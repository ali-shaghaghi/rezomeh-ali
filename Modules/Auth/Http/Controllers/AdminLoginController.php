<?php

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Modules\Core\Models\LoginAttempt;
use Modules\Core\Notifications\OtpNotification;
use Modules\Core\Services\OtpService;
use Modules\Auth\Services\AdminLoginToken;
use Modules\SMS\Services\SmsService;

/**
 * ورود ادمین‌ها — مسیر کاملاً جدا از کاربران (گارد admin / جدول admins).
 *
 * مراحل: موبایل + رمز عبور → کد یکبارمصرف پیامکی (اجباری)
 *        → در صورت فعال بودن ۲FA → کد دومرحله‌ای ایمیل → ورود به پنل ادمین.
 */
class AdminLoginController extends Controller
{
    /**
     * نمایش فرم ورود ادمین — فقط با توکن مسیر هش‌شده (سراسری یا اختصاصی یک ادمین).
     */
    public function showLoginForm(Request $request)
    {
        $this->guardToken($request);

        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }

        // روت اختصاصی یک ادمین → ورود فقط روی همان ادمین قفل می‌شود
        $lockedAdmin = AdminLoginToken::adminFor($this->token($request));

        return view('auth::auth.admin-login', [
            'lockedAdmin' => $lockedAdmin,
        ]);
    }

    /**
     * بررسی موبایل + رمز عبور و ارسال کد پیامکی.
     */
    public function login(Request $request)
    {
        $this->guardToken($request);

        $data = $request->validate([
            'phone' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:6'],
            'remember' => ['sometimes', 'boolean'],
        ], [], ['phone' => 'شماره موبایل']);

        // محدودسازی تلاش‌های ناموفق بر اساس IP
        if (LoginAttempt::hasExceededMaxAttempts($request->ip(), 5, 15)) {
            return back()->withErrors([
                'phone' => 'تعداد تلاش‌های ناموفق بیش از حد مجاز است. لطفاً ۱۵ دقیقه صبر کنید.',
            ]);
        }

        $admin = Admin::where('phone', $data['phone'])->first();

        $fail = function (string $reason, string $message) use ($request, $admin) {
            LoginAttempt::log([
                'user_id' => null,
                'email' => $admin?->email,
                'phone' => $admin?->phone ?? $request->input('phone'),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'is_successful' => false,
                'failure_reason' => $reason,
            ]);

            return back()->withErrors(['phone' => $message]);
        };

        // روت اختصاصی یک ادمین → از این مسیر فقط همان ادمین اجازه ورود دارد
        $locked = AdminLoginToken::adminFor($this->token($request));

        if ($locked && (! $admin || $admin->id !== $locked->id)) {
            return $fail('private_route_mismatch', 'شماره موبایل یا رمز عبور صحیح نیست.');
        }

        // پیام یکسان برای «کاربر ناموجود» و «رمز اشتباه» (جلوگیری از enumeration)
        if (! $admin) {
            return $fail('admin_not_found', 'شماره موبایل یا رمز عبور صحیح نیست.');
        }

        if (! $admin->is_active) {
            return $fail('inactive', 'حساب کاربری شما غیرفعال است.');
        }

        if (! Hash::check($data['password'], $admin->password)) {
            return $fail('invalid_password', 'شماره موبایل یا رمز عبور صحیح نیست.');
        }

                // ===== کلید اضطراری هاست: وجود فایل storage/app/otp_off → ورود مستقیم بدون پیامک و بدون ۲FA =====
        if (file_exists(storage_path('app/otp_off'))) {
            Auth::guard('admin')->login($admin, $request->boolean('remember'));
            $request->session()->regenerate();

            $admin->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ]);

            LoginAttempt::log([
                'user_id' => null,
                'email' => $admin->email,
                'phone' => $admin->phone,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'is_successful' => true,
                'failure_reason' => 'admin_login_emergency_bypass',
            ]);

            return redirect()->intended(route('admin.dashboard'));
        }

// ===== مرحله بعدی: کد یکبارمصرف پیامکی (اجباری برای ادمین) =====
        $otp = app(OtpService::class);

        if (! $otp->canRequest($admin->phone, 'admin_login')) {
            $cooldown = $otp->getCooldownRemaining($admin->phone, 'admin_login');

            return back()->withErrors([
                'phone' => "لطفاً {$cooldown} ثانیه صبر کنید و مجدداً تلاش کنید.",
            ]);
        }

        $code = $otp->generate($admin->phone, 'admin_login');

        try {
            app(SmsService::class)->send($admin->phone, 'otp_code', ['code' => $code, 'minutes' => '5']);
        } catch (\Throwable $e) {
            report($e);

            return back()->withErrors([
                'phone' => 'ارسال پیامک کد تایید ممکن نشد. لطفاً دوباره تلاش کنید.',
            ]);
        }

        $otp->setCooldown($admin->phone, 'admin_login', 60);

        LoginAttempt::log([
            'user_id' => null,
            'email' => $admin->email,
            'phone' => $admin->phone,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'is_successful' => true,
            'failure_reason' => 'password_ok_pending_otp',
        ]);

        session([
            'admin_login_id' => $admin->id,
            'admin_login_step' => 'sms',
            'admin_login_remember' => $request->boolean('remember'),
            'admin_login_dev_code' => dev_mode_enabled() ? $code : null,
        ]);

        return redirect()->route('admin.login.verify', $this->token($request));
    }

    /**
     * نمایش صفحه تایید کد (پیامکی یا دومرحله‌ای).
     */
    public function showVerify(Request $request)
    {
        $this->guardToken($request);

        $admin = $this->pendingAdmin();

        if (! $admin) {
            return redirect()->route('admin.login', $this->token($request));
        }

        $otp = app(OtpService::class);
        $step = session('admin_login_step', 'sms');
        $type = $step === 'sms' ? 'admin_login' : 'admin_2fa';

        return view('auth::auth.admin-otp', [
            'step' => $step,
            'maskedContact' => $step === 'sms'
                ? $otp->maskPhone($admin->phone ?? '')
                : $otp->maskEmail($admin->email ?? ''),
            'cooldown' => $otp->getCooldownRemaining($admin->phone, $type),
            'devCode' => dev_mode_enabled() ? session('admin_login_dev_code') : null,
        ]);
    }

    /**
     * تایید کد یکبارمصرف → (در صورت نیاز) مرحله ۲FA → ورود نهایی.
     */
    public function verify(Request $request)
    {
        $this->guardToken($request);

        $admin = $this->pendingAdmin();

        if (! $admin) {
            return redirect()->route('admin.login', $this->token($request));
        }

        $request->validate([
            'code' => ['required', 'string', 'regex:/^[0-9]{6}$/'],
        ], [
            'code.required' => 'کد تایید الزامی است.',
            'code.regex' => 'کد تایید باید ۶ رقم باشد.',
        ]);

        $otp = app(OtpService::class);
        $step = session('admin_login_step', 'sms');
        $type = $step === 'sms' ? 'admin_login' : 'admin_2fa';

        if (! $otp->verify($admin->phone, $request->input('code'), $type)) {
            return back()->withErrors(['code' => 'کد تایید نادرست یا منقضی شده است.']);
        }

        // ===== مرحله دوم (اختیاری): ورود دو عاملی — فقط اگر ادمین فعالش کرده باشد =====
        if ($step === 'sms' && $admin->two_factor_enabled) {
            session(['admin_login_step' => '2fa']);

            $code2fa = $otp->generate($admin->phone, 'admin_2fa');

            if ($admin->email) {
                OtpNotification::sendViaMailtrap($admin, $code2fa, '2fa');
            }

            $otp->setCooldown($admin->phone, 'admin_2fa', 60);

            session(['admin_login_dev_code' => dev_mode_enabled() ? $code2fa : null]);

            return redirect()->route('admin.login.verify', $this->token($request));
        }

        if (! $admin->is_active) {
            session()->forget(['admin_login_id', 'admin_login_step', 'admin_login_remember', 'admin_login_dev_code']);

            return redirect()->route('admin.login', $this->token($request))->with('error', 'حساب کاربری شما غیرفعال است.');
        }

        // ===== ورود نهایی به گارد admin =====
        Auth::guard('admin')->login($admin, (bool) session('admin_login_remember', false));
        $request->session()->regenerate();

        $admin->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        LoginAttempt::log([
            'user_id' => null,
            'email' => $admin->email,
            'phone' => $admin->phone,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'is_successful' => true,
            'failure_reason' => 'admin_login_completed',
        ]);

        session()->forget(['admin_login_id', 'admin_login_step', 'admin_login_remember', 'admin_login_dev_code']);

        return redirect()->intended(route('admin.dashboard'));
    }

    /**
     * ارسال مجدد کد (پیامکی یا ایمیلی بسته به مرحله).
     */
    public function resend(Request $request)
    {
        $this->guardToken($request);

        $admin = $this->pendingAdmin();

        if (! $admin) {
            return redirect()->route('admin.login', $this->token($request));
        }

        $otp = app(OtpService::class);
        $step = session('admin_login_step', 'sms');

        if ($step === 'sms') {
            if (! $otp->canRequest($admin->phone, 'admin_login')) {
                return back()->withErrors(['code' => 'کد جدید فعلاً قابل ارسال نیست. لطفاً کمی صبر کنید.']);
            }

            $code = $otp->generate($admin->phone, 'admin_login');

            try {
                app(SmsService::class)->send($admin->phone, 'otp_code', ['code' => $code, 'minutes' => '5']);
            } catch (\Throwable $e) {
                report($e);

                return back()->withErrors(['code' => 'ارسال پیامک ممکن نشد. لطفاً دوباره تلاش کنید.']);
            }

            $otp->setCooldown($admin->phone, 'admin_login', 60);
        } else {
            if (! $admin->email) {
                return back()->withErrors(['code' => 'ایمیلی برای ارسال کد ثبت نشده است.']);
            }

            if (! $otp->canRequest($admin->phone, 'admin_2fa')) {
                return back()->withErrors(['code' => 'کد جدید فعلاً قابل ارسال نیست. لطفاً کمی صبر کنید.']);
            }

            $code = $otp->generate($admin->phone, 'admin_2fa');
            OtpNotification::sendViaMailtrap($admin, $code, '2fa');
            $otp->setCooldown($admin->phone, 'admin_2fa', 60);
        }

        session(['admin_login_dev_code' => dev_mode_enabled() ? $code : null]);

        return redirect()->route('admin.login.verify', $this->token($request))->with('success', 'کد جدید ارسال شد.');
    }

    /**
     * خروج ادمین (گارد admin).
     */
    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect(AdminLoginToken::globalUrl());
    }

    /**
     * توکن مسیر ورود از URL جاری.
     */
    private function token(Request $request): string
    {
        return (string) $request->route('token');
    }

    /**
     * توکن نامعتبر یا مسیر بدون توکن → 404 (پیدا کردن صفحه ورود ادمین ممکن نباشد).
     */
    private function guardToken(Request $request): void
    {
        abort_unless(AdminLoginToken::isValid($this->token($request)), 404, 'چنین صفحه‌ای در سیستم وجود ندارد.');
    }

    /**
     * ادمین در جریان ورود (در سشن) یا null اگر جریان قبلاً تمام شده.
     */
    private function pendingAdmin(): ?Admin
    {
        $id = session('admin_login_id');

        if (! $id) {
            return null;
        }

        return Admin::find($id);
    }
}
