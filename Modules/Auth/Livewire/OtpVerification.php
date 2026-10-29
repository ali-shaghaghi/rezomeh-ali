<?php

namespace Modules\Auth\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Modules\Auth\Services\RedirectService;
use Modules\Core\Notifications\OtpNotification;
use Modules\Core\Services\OtpService;

class OtpVerification extends Component
{
    public string $otp = '';

    public string $method = 'email';

    public string $maskedContact = '';

    public bool $isLoading = false;

    public string $errorMessage = '';

    public int $cooldown = 0;

    public bool $canResend = false;

    public ?string $devCode = null;

    protected OtpService $otpService;

    public function boot(OtpService $otpService): void
    {
        $this->otpService = $otpService;
    }

    public function mount(): void
    {
        $userId = session('otp_user_id');
        if (! $userId) {
            $this->redirect(route('auth.login'), navigate: true);

            return;
        }

        $user = User::find($userId);
        if (! $user) {
            $this->redirect(route('auth.login'), navigate: true);

            return;
        }

        $this->method = session('otp_method', 'email');
        $this->maskedContact = $this->method === 'email'
            ? $this->otpService->maskEmail($user->email)
            : $this->otpService->maskPhone($user->phone ?? '');

        $this->cooldown = $this->otpService->getCooldownRemaining($user, 'login');
        $this->canResend = $this->cooldown === 0;

        // Show OTP code in development environment
        if (dev_mode_enabled()) {
            $this->devCode = session('otp_dev_code');
        }
    }

    public function verify(): void
    {
        $this->errorMessage = '';

        if (strlen($this->otp) !== 6) {
            $this->errorMessage = 'کد تایید باید ۶ رقم باشد.';

            return;
        }

        $this->isLoading = true;

        try {
            $userId = session('otp_user_id');
            $user = User::find($userId);

            if (! $user) {
                $this->redirect(route('auth.login'), navigate: true);

                return;
            }

            if (! $this->otpService->verify($user, $this->otp, 'login')) {
                $this->errorMessage = 'کد تایید نادرست یا منقضی شده است.';
                $this->otp = '';
                $this->isLoading = false;

                return;
            }

            session()->forget(['otp_user_id', 'otp_method', 'otp_dev_code']);

            // ==================================================================
            // مرحله دوم (اختیاری): ورود دو عاملی — فقط اگر کاربر آن را فعال کرده باشد
            // ==================================================================
            if ($user->two_factor_enabled) {
                session([
                    'two_factor_user_id' => $user->id,
                    'two_factor_remember' => session('otp_remember', false),
                ]);
                session()->forget('otp_remember');

                // تولید و ارسال کد دومرحله‌ای (ایمیل)
                if ($this->otpService->canRequest($user, 'two_factor')) {
                    $code = $this->otpService->generate($user, 'two_factor');
                    OtpNotification::sendViaMailtrap($user, $code, 'two_factor');
                    $this->otpService->setCooldown($user, 'two_factor', 60);
                }

                $this->redirect(route('auth.two-factor.verify'), navigate: true);

                return;
            }

            auth()->login($user, session('otp_remember', false));
            session()->regenerate();
            session()->forget('otp_remember');

            // ==================================================================
            // مرحله ایمیل تایید (اختیاری): فقط اگر کاربر فعالش کرده و ایمیل تایید نشده
            // ==================================================================
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
            $this->redirect(RedirectService::target($user));

        } catch (\Exception $e) {
            Log::error('OTP Verification Error: '.$e->getMessage());
            $this->errorMessage = 'خطایی رخ داد. لطفاً دوباره تلاش کنید.';
        } finally {
            $this->isLoading = false;
        }
    }

    public function resendOtp(): void
    {
        $userId = session('otp_user_id');
        $user = User::find($userId);

        if (! $user) {
            $this->redirect(route('auth.login'), navigate: true);

            return;
        }

        if (! $this->otpService->canRequest($user, 'login')) {
            $this->canResend = false;
            $this->cooldown = $this->otpService->getCooldownRemaining($user, 'login');

            return;
        }

        $code = $this->otpService->generate($user, 'login');

        // ارسال کد: ورود با موبایل → پیامک، ورود با ایمیل → ایمیل
        if (session('otp_method', 'email') === 'phone') {
            if (! $user->phone) {
                $this->errorMessage = 'شماره موبایلی برای این حساب ثبت نشده است.';

                return;
            }

            try {
                app(\Modules\SMS\Services\SmsService::class)
                    ->send($user->phone, 'otp_code', ['code' => $code, 'minutes' => '5'], $user);
            } catch (\Throwable $e) {
                report($e);
                $this->errorMessage = 'ارسال پیامک ممکن نشد. لطفاً دوباره تلاش کنید.';

                return;
            }
        } else {
            OtpNotification::sendViaMailtrap($user, $code, 'login');
        }

        $this->otpService->setCooldown($user, 'login', 60);

        $this->canResend = false;
        $this->cooldown = 60;
        $this->errorMessage = '';
    }

    public function render()
    {
        return view('auth::livewire.otp-verification');
    }
}
