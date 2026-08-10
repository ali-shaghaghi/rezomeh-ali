<?php

namespace Modules\Admin\Livewire\Auth;

use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
        if (Auth::check() && Auth::user()->is_admin) {
            $this->redirect(route('admin.dashboard'), navigate: true);
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
        $this->showPassword = !$this->showPassword;
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

            if (!$user) {
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

            // Check if user is admin
            if (!$user->is_admin) {
                $this->errorMessage = 'شما دسترسی به پنل ادمین را ندارید.';
                $this->isLoading = false;
                return;
            }

            // Check if user is active
            if (!$user->is_active) {
                $this->errorMessage = 'حساب کاربری شما غیرفعال است.';
                $this->isLoading = false;
                return;
            }

            // Verify password
            if (!Hash::check($this->password, $user->password)) {
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

            // Check OTP cooldown
            if (!$this->otpService->canRequest($user, 'login')) {
                $cooldown = $this->otpService->getCooldownRemaining($user, 'login');
                $this->errorMessage = "لطفاً {$cooldown} ثانیه صبر کنید و مجدداً تلاش کنید.";
                $this->isLoading = false;
                return;
            }

            // Generate and send OTP via Mailtrap API
            $code = $this->otpService->generate($user, 'login');
            \Illuminate\Support\Facades\Log::info('OTP Generated for user: ' . $user->email . ' Code: ' . $code);

            // Send via Mailtrap API
            $sent = OtpNotification::sendViaMailtrap($user, $code, 'login');
            if (!$sent) {
                $this->errorMessage = 'خطا در ارسال ایمیل. لطفاً دوباره تلاش کنید.';
                $this->isLoading = false;
                return;
            }

            $this->otpService->setCooldown($user, 'login', 60);

            // Store user info in session for OTP verification
            session([
                'otp_user_id' => $user->id,
                'otp_method' => $this->loginType,
                'otp_remember' => $this->remember,
            ]);

            // Redirect to OTP verification
            $this->redirect(route('admin.otp.verify'), navigate: true);

        } catch (\Exception $e) {
            $this->errorMessage = 'خطایی رخ داد. لطفاً دوباره تلاش کنید.';
        } finally {
            $this->isLoading = false;
        }
    }

    public function render()
    {
        return view('admin::livewire.auth.login-form');
    }
}