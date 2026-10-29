<?php

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Modules\Auth\Http\Requests\LoginRequest;
use Modules\Auth\Services\LoginFlowService;
use Modules\Auth\Services\RedirectService;
use Modules\Core\Models\LoginAttempt;
use Modules\Core\Notifications\OtpNotification;
use Modules\Core\Services\OtpService;

class LoginController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        return view('auth::auth.login');
    }

    /**
     * Handle a login request (fallback سروری برای فرم Livewire).
     *
     * امنیت دقیقاً همان جریان Livewire است:
     * Rate Limiting → یافتن کاربر → بررسی وضعیت → رمز عبور → ثبت LoginAttempt → ارسال OTP → ریدایرکت به تایید OTP.
     * ریدایرکت نهایی (نقش‌محور) بعد از تایید OTP در OtpVerification انجام می‌شود.
     */
    public function login(LoginRequest $request)
    {
        // Rate Limiting بر اساس IP (جدول login_attempts در Core)
        if (LoginAttempt::hasExceededMaxAttempts($request->ip(), 5, 15)) {
            return back()->withErrors([
                'credential' => 'تعداد تلاش‌های ناموفق بیش از حد مجاز است. لطفاً ۱۵ دقیقه صبر کنید.',
            ]);
        }

        $user = $request->findUser();

        $fail = function (string $reason, string $message) use ($request, $user) {
            LoginAttempt::log([
                'user_id' => $user?->id,
                'email' => $user?->email ?? ($request->loginType() === 'email' ? $request->input('credential') : null),
                'phone' => $user?->phone ?? ($request->loginType() === 'phone' ? $request->input('credential') : null),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'is_successful' => false,
                'failure_reason' => $reason,
            ]);

            return back()->withErrors(['credential' => $message]);
        };

        if (! $user) {
            return $fail('user_not_found', 'کاربری با این مشخصات یافت نشد.');
        }

        if (! $user->is_active) {
            return $fail('inactive', 'حساب کاربری شما غیرفعال است.');
        }

        if (! Hash::check($request->input('password'), $user->password)) {
            return $fail('invalid_password', 'رمز عبور صحیح نیست.');
        }

        // ثبت تلاش موفق تایید رمز عبور
        LoginAttempt::log([
            'user_id' => $user->id,
            'email' => $user->email,
            'phone' => $user->phone,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'is_successful' => true,
        ]);

        // ==================================================================
        // ورود با شماره موبایل → کد یک‌بارمصرف پیامکی (اجباری)
        // ==================================================================
        if ($request->loginType() === 'phone') {
            if (! $user->phone) {
                return $fail('missing_phone', 'برای این حساب شماره موبایلی ثبت نشده است. با ایمیل وارد شوید.');
            }

            $otpService = app(OtpService::class);

            if (! $otpService->canRequest($user, 'login')) {
                $cooldown = $otpService->getCooldownRemaining($user, 'login');

                return back()->withErrors([
                    'credential' => "لطفاً {$cooldown} ثانیه صبر کنید و مجدداً تلاش کنید.",
                ]);
            }

            $code = $otpService->generate($user, 'login');

            try {
                app(\Modules\SMS\Services\SmsService::class)
                    ->send($user->phone, 'otp_code', ['code' => $code, 'minutes' => '5'], $user);
            } catch (\Throwable $e) {
                report($e);

                return back()->withErrors([
                    'credential' => 'ارسال پیامک کد تایید ممکن نشد. لطفاً دوباره تلاش کنید.',
                ]);
            }

            $otpService->setCooldown($user, 'login', 60);

            session([
                'otp_user_id' => $user->id,
                'otp_method' => 'phone',
                'otp_remember' => $request->boolean('remember'),
                'otp_dev_code' => dev_mode_enabled() ? $code : null,
            ]);

            return redirect()->route('auth.otp.verify');
        }

        // ==================================================================
        // ورود با ایمیل → پیش‌فرض ورود مستقیم؛ OTP/۲FA فقط اگر خود کاربر فعال کرده باشد
        // ==================================================================
        $step = LoginFlowService::next($user);

        // ---------- مرحله ۱ (اختیاری): کد ورود یک‌بارمصرف (OTP ایمیل) ----------
        if ($step === LoginFlowService::STEP_OTP) {
            $otpService = app(OtpService::class);

            // بررسی فاصله زمانی ارسال مجدد OTP
            if (! $otpService->canRequest($user, 'login')) {
                $cooldown = $otpService->getCooldownRemaining($user, 'login');

                return back()->withErrors([
                    'credential' => "لطفاً {$cooldown} ثانیه صبر کنید و مجدداً تلاش کنید.",
                ]);
            }

            // تولید و ارسال کد یکبارمصرف (Mailtrap با fallback به میل سرور)
            $code = $otpService->generate($user, 'login');

            if (! OtpNotification::sendViaMailtrap($user, $code, 'login')) {
                return back()->withErrors([
                    'credential' => 'خطا در ارسال ایمیل. لطفاً دوباره تلاش کنید.',
                ]);
            }

            $otpService->setCooldown($user, 'login', 60);

            // ذخیره اطلاعات کاربر در سشن برای مرحله تایید OTP
            session([
                'otp_user_id' => $user->id,
                'otp_method' => 'email',
                'otp_remember' => $request->boolean('remember'),
                'otp_dev_code' => dev_mode_enabled() ? $code : null,
            ]);

            return redirect()->route('auth.otp.verify');
        }

        // ---------- مرحله ۲ (اختیاری): ورود دو عاملی (2FA) ----------
        if ($step === LoginFlowService::STEP_TWO_FACTOR) {
            $otpService = app(OtpService::class);

            session([
                'two_factor_user_id' => $user->id,
                'two_factor_remember' => $request->boolean('remember'),
            ]);

            if ($otpService->canRequest($user, 'two_factor')) {
                $code = $otpService->generate($user, 'two_factor');
                OtpNotification::sendViaMailtrap($user, $code, 'two_factor');
                $otpService->setCooldown($user, 'two_factor', 60);
            }

            return redirect()->route('auth.two-factor.verify');
        }

        // ---------- ورود مستقیم (پیش‌فرض — بدون OTP و بدون 2FA) ----------
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        // مرحله تایید ایمیل (اختیاری): فقط اگر کاربر فعالش کرده و ایمیل تایید نشده
        if ($user->email_verification_enabled && ! $user->hasVerifiedEmail()) {
            $throttleKey = 'email-verification:cooldown:'.$user->id;
            if (! Cache::has($throttleKey)) {
                $user->sendEmailVerificationNotification();
                Cache::put($throttleKey, true, now()->addMinutes(10));
            }

            return redirect()->route('verification.notice');
        }

        // ریدایرکت: کاربر همیشه به پنل کاربر (ادمین‌ها از /admin/login وارد می‌شوند)
        return redirect()->intended(RedirectService::target($user));
    }
}
