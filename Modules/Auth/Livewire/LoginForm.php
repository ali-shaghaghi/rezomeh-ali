<?php

namespace Modules\Auth\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Modules\Auth\Services\LoginFlowService;
use Modules\Auth\Services\RedirectService;
use Illuminate\Support\Facades\Cache;
use Modules\Core\Models\LoginAttempt;
use Modules\Core\Notifications\OtpNotification;
use Modules\Core\Services\OtpService;

class LoginForm extends Component
{
    public string $credential = '';

    public string $password = '';

    public bool $remember = false;

    public bool $showPassword = false;

    public bool $isLoading = false;

    public string $loginType = 'email'; // email or phone

    public string $errorMessage = '';

    protected $rules = [
        'credential' => 'required|string',
        'password' => 'required|string|min:6',
    ];

    protected $messages = [
        'credential.required' => 'ایمیل یا شماره تلفن الزامی است.',
        'password.required' => 'رمز عبور الزامی است.',
        'password.min' => 'رمز عبور باید حداقل ۶ کاراکتر باشد.',
    ];

    protected OtpService $otpService;

    public function boot(OtpService $otpService): void
    {
        $this->otpService = $otpService;
    }

    public function mount(): void
    {
        if (Auth::check()) {
            // ریدایرکت نقش‌محور: ادمین → داشبورد ادمین، کاربر عادی → داشبورد کاربر
            $this->redirect(RedirectService::target(Auth::user()), navigate: true);
        }
    }

    public function setLoginType(string $type): void
    {
        $this->loginType = $type;
        $this->credential = '';
        $this->errorMessage = '';
        $this->resetValidation();
    }

    public function togglePasswordVisibility(): void
    {
        $this->showPassword = ! $this->showPassword;
    }

    public function login(): void
    {
        $this->errorMessage = '';

        $this->validate();

        // Rate limiting by IP
        if (LoginAttempt::hasExceededMaxAttempts(request()->ip(), 5, 15)) {
            $this->errorMessage = 'تعداد تلاش‌های ناموفق بیش از حد مجاز است. لطفاً ۱۵ دقیقه صبر کنید.';

            return;
        }

        $this->isLoading = true;

        try {
            // Find user by email or phone
            $user = $this->loginType === 'email'
                ? User::where('email', $this->credential)->first()
                : User::where('phone', $this->credential)->first();

            if (! $user) {
                LoginAttempt::log([
                    'email' => $this->loginType === 'email' ? $this->credential : null,
                    'phone' => $this->loginType === 'phone' ? $this->credential : null,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'is_successful' => false,
                    'failure_reason' => 'user_not_found',
                ]);
                $this->errorMessage = 'کاربری با این مشخصات یافت نشد.';
                $this->isLoading = false;

                return;
            }

            // تشخیص ادمین لازم نیست — ورود یکپارچه است؛ مقصد نهایی پس از تایید OTP
            // بر اساس نقش (RedirectService) مشخص می‌شود.

            // Check if user is active
            if (! $user->is_active) {
                $this->errorMessage = 'حساب کاربری شما غیرفعال است.';
                $this->isLoading = false;

                return;
            }

            // Verify password
            if (! Hash::check($this->password, $user->password)) {
                LoginAttempt::log([
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'is_successful' => false,
                    'failure_reason' => 'invalid_password',
                ]);
                $this->errorMessage = 'رمز عبور صحیح نیست.';
                $this->isLoading = false;

                return;
            }

            // Log successful password verification
            LoginAttempt::log([
                'user_id' => $user->id,
                'email' => $user->email,
                'phone' => $user->phone,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'is_successful' => true,
            ]);

            // ==================================================================
            // ورود با شماره موبایل → کد یک‌بارمصرف پیامکی (اجباری)
            // ==================================================================
            if ($this->loginType === 'phone') {
                // کلید اضطراری هاست: وجود فایل storage/app/otp_off → ورود مستقیم بدون پیامک
                if (file_exists(storage_path('app/otp_off'))) {
                    Auth::login($user, $this->remember);
                    session()->regenerate();
                    $this->redirect(RedirectService::target($user), navigate: true);

                    return;
                }

                if (! $user->phone) {
                    $this->errorMessage = 'برای این حساب شماره موبایلی ثبت نشده است. با ایمیل وارد شوید.';
                    $this->isLoading = false;

                    return;
                }

                if (! $this->otpService->canRequest($user, 'login')) {
                    $cooldown = $this->otpService->getCooldownRemaining($user, 'login');
                    $this->errorMessage = "لطفاً {$cooldown} ثانیه صبر کنید و مجدداً تلاش کنید.";
                    $this->isLoading = false;

                    return;
                }

                $code = $this->otpService->generate($user, 'login');

                try {
                    app(\Modules\SMS\Services\SmsService::class)
                        ->send($user->phone, 'otp_code', ['code' => $code, 'minutes' => '5'], $user);
                } catch (\Throwable $e) {
                    report($e);
                    $this->errorMessage = 'ارسال پیامک کد تایید ممکن نشد. لطفاً دوباره تلاش کنید.';
                    $this->isLoading = false;

                    return;
                }

                $this->otpService->setCooldown($user, 'login', 60);

                session([
                    'otp_user_id' => $user->id,
                    'otp_method' => 'phone',
                    'otp_remember' => $this->remember,
                    'otp_dev_code' => dev_mode_enabled() ? $code : null,
                ]);

                $this->redirect(route('auth.otp.verify'), navigate: true);

                return;
            }

            // ==================================================================
            // ورود با ایمیل → پیش‌فرض ورود مستقیم؛ OTP/۲FA فقط اگر خود کاربر فعال کرده باشد
            // ==================================================================
            $step = LoginFlowService::next($user);

            // ---------- مرحله ۱ (اختیاری): کد ورود یک‌بارمصرف (OTP ایمیل) ----------
            if ($step === LoginFlowService::STEP_OTP) {
                // Check OTP cooldown
                if (! $this->otpService->canRequest($user, 'login')) {
                    $cooldown = $this->otpService->getCooldownRemaining($user, 'login');
                    $this->errorMessage = "لطفاً {$cooldown} ثانیه صبر کنید و مجدداً تلاش کنید.";
                    $this->isLoading = false;

                    return;
                }

                // Generate and send OTP via Mailtrap API
                $code = $this->otpService->generate($user, 'login');

                // Send via Mailtrap API (with fallback to Laravel mail)
                $sent = OtpNotification::sendViaMailtrap($user, $code, 'login');
                if (! $sent) {
                    $this->errorMessage = 'خطا در ارسال ایمیل. لطفاً دوباره تلاش کنید.';
                    $this->isLoading = false;

                    return;
                }

                $this->otpService->setCooldown($user, 'login', 60);

                // Store user info in session for OTP verification
                session([
                    'otp_user_id' => $user->id,
                    'otp_method' => 'email',
                    'otp_remember' => $this->remember,
                    'otp_dev_code' => dev_mode_enabled() ? $code : null,
                ]);

                // Redirect to OTP verification
                $this->redirect(route('auth.otp.verify'), navigate: true);

                return;
            }

            // ---------- مرحله ۲ (اختیاری): ورود دو عاملی (2FA) ----------
            if ($step === LoginFlowService::STEP_TWO_FACTOR) {
                session([
                    'two_factor_user_id' => $user->id,
                    'two_factor_remember' => $this->remember,
                ]);

                // تولید و ارسال کد دومرحله‌ای (ایمیل)
                if ($this->otpService->canRequest($user, 'two_factor')) {
                    $code = $this->otpService->generate($user, 'two_factor');
                    OtpNotification::sendViaMailtrap($user, $code, 'two_factor');
                    $this->otpService->setCooldown($user, 'two_factor', 60);
                }

                $this->redirect(route('auth.two-factor.verify'), navigate: true);

                return;
            }

            // ---------- ورود مستقیم (پیش‌فرض — بدون OTP و بدون 2FA) ----------
            Auth::login($user, $this->remember);
            session()->regenerate();

            // مرحله تایید ایمیل (اختیاری): فقط اگر کاربر فعالش کرده و ایمیل تایید نشده
            if ($user->email_verification_enabled && ! $user->hasVerifiedEmail()) {
                $throttleKey = 'email-verification:cooldown:'.$user->id;
                if (! Cache::has($throttleKey)) {
                    $user->sendEmailVerificationNotification();
                    Cache::put($throttleKey, true, now()->addMinutes(10));
                }

                $this->redirect(route('verification.notice'), navigate: true);

                return;
            }

            // ریدایرکت: کاربر همیشه به پنل کاربر (ادمین‌ها از /admin/login وارد می‌شوند)
            $this->redirect(RedirectService::target($user), navigate: true);

        } catch (\Exception $e) {
            $this->errorMessage = 'خطایی رخ داد. لطفاً دوباره تلاش کنید.';
        } finally {
            $this->isLoading = false;
        }
    }

    public function render()
    {
        return view('auth::livewire.login-form');
    }
}
