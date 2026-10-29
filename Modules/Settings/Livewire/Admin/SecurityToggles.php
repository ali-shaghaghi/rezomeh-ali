<?php

namespace Modules\Settings\Livewire\Admin;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Modules\Core\Services\TwoFactorService;

/**
 * تنظیمات امنیتی اختیاری کاربر — ورود دو عاملی (2FA) + تایید ایمیل.
 *
 * هر دو قابلیت به‌صورت پیش‌فرض غیرفعال‌اند و فقط با انتخاب خود کاربر
 * (ادمین یا کاربر عادی) فعال می‌شوند. در تب «امنیت حساب» تنظیمات ادمین
 * و تب «امنیت حساب» پنل کاربر، کنار بخش تغییر رمز عبور قرار دارد.
 */
class SecurityToggles extends Component
{
    /** تم نمایش: dark (پنل ادمین) | light (پنل کاربر) */
    public string $theme = 'dark';

    public bool $twoFactorEnabled = false;

    public bool $emailVerificationEnabled = false;

    public bool $loginOtpEnabled = false;

    public bool $emailVerified = false;

    /** کدهای بازیابی — فقط یک‌بار پس از فعال‌سازی 2FA نمایش داده می‌شوند */
    public array $recoveryCodes = [];

    public bool $showRecoveryCodes = false;

    public bool $showDisableForm = false;

    public string $disablePassword = '';

    public bool $isLoading = false;

    public string $successMessage = '';

    public string $errorMessage = '';

    public function mount(string $theme = 'dark'): void
    {
        $this->theme = $theme;
        $this->refreshState();
    }

    protected function refreshState(): void
    {
        $user = auth()->user();

        $this->twoFactorEnabled = (bool) $user->two_factor_enabled;
        $this->emailVerificationEnabled = (bool) $user->email_verification_enabled;
        $this->loginOtpEnabled = (bool) $user->login_otp_enabled;
        $this->emailVerified = (bool) $user->email_verified_at;
    }

    // ==================================================================
    // ورود دو عاملی (2FA)
    // ==================================================================

    public function enableTwoFactor(): void
    {
        $this->resetMessages();
        $user = auth()->user();

        if ($user->two_factor_enabled) {
            $this->errorMessage = 'ورود دو عاملی از قبل فعال است.';

            return;
        }

        // فعال‌سازی + تولید کدهای بازیابی (فقط این‌جا نمایش داده می‌شوند)
        $this->recoveryCodes = app(TwoFactorService::class)->generateRecoveryCodes($user);
        $user->update(['two_factor_enabled' => true]);

        $this->refreshState();
        $this->showRecoveryCodes = true;
        $this->successMessage = 'ورود دو عاملی فعال شد. از این پس پس از تایید OTP ورود، یک کد دوم مرحله‌ای نیز لازم است.';
    }

    public function disableTwoFactor(): void
    {
        $this->resetMessages();

        $this->validate([
            'disablePassword' => ['required', 'string'],
        ], [
            'disablePassword.required' => 'برای غیرفعال‌سازی، رمز عبور فعلی الزامی است.',
        ]);

        $this->isLoading = true;

        try {
            if (! app(TwoFactorService::class)->disable(auth()->user(), $this->disablePassword)) {
                $this->errorMessage = 'رمز عبور فعلی صحیح نیست.';

                return;
            }

            $this->disablePassword = '';
            $this->showDisableForm = false;
            $this->refreshState();
            $this->successMessage = 'ورود دو عاملی غیرفعال شد.';
        } finally {
            $this->isLoading = false;
        }
    }

    public function cancelDisable(): void
    {
        $this->showDisableForm = false;
        $this->disablePassword = '';
        $this->resetMessages();
    }

    // ==================================================================
    // تایید ایمیل
    // ==================================================================

    public function setEmailVerification(bool $enabled): void
    {
        $this->resetMessages();
        $user = auth()->user();

        $user->update(['email_verification_enabled' => $enabled]);
        $this->refreshState();

        if ($enabled) {
            if ($user->hasVerifiedEmail()) {
                $this->successMessage = 'تایید ایمیل فعال شد؛ ایمیل شما قبلاً تایید شده است.';
            } else {
                $this->successMessage = 'تایید ایمیل فعال شد. لینک تایید به ایمیل شما ارسال شد.';
                $this->sendVerificationEmail(silent: true);
            }
        } else {
            $this->successMessage = 'تایید ایمیل غیرفعال شد.';
        }
    }

    public function sendVerificationEmail(bool $silent = false): void
    {
        if (! $silent) {
            $this->resetMessages();
        }

        /** @var User $user */
        $user = auth()->user();

        if ($user->hasVerifiedEmail()) {
            $this->successMessage = 'ایمیل شما قبلاً تایید شده است.';

            return;
        }

        // جلوگیری از ارسال مکرر (۱۰ دقیقه)
        $throttleKey = 'email-verification:cooldown:'.$user->id;
        if (Cache::has($throttleKey)) {
            $this->errorMessage = 'ایمیل تایید اخیراً ارسال شده است. لطفاً ۱۰ دقیقه دیگر تلاش کنید.';

            return;
        }

        $user->sendEmailVerificationNotification();
        Cache::put($throttleKey, true, now()->addMinutes(10));

        $this->successMessage = 'ایمیل تایید به '.$user->email.' ارسال شد.';
    }

    // ==================================================================
    // کد ورود یک‌بارمصرف (OTP ایمیل) — اختیاری
    // ==================================================================

    public function setLoginOtp(bool $enabled): void
    {
        $this->resetMessages();

        auth()->user()->update(['login_otp_enabled' => $enabled]);
        $this->refreshState();

        $this->successMessage = $enabled
            ? 'کد ورود یک‌بارمصرف فعال شد. از این پس پس از ورود با رمز عبور، یک کد به ایمیل شما ارسال می‌شود.'
            : 'کد ورود یک‌بارمصرف غیرفعال شد. ورود فقط با رمز عبور انجام می‌شود.';
    }

    protected function resetMessages(): void
    {
        $this->successMessage = '';
        $this->errorMessage = '';
    }

    public function render()
    {
        return view('settings::livewire.admin.security-toggles');
    }
}
