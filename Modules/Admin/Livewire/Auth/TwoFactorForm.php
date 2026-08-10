<?php

namespace Modules\Admin\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Modules\Core\Services\Contracts\AuthServiceInterface;
use Modules\Core\Services\OtpService;

class TwoFactorForm extends Component
{
    public array $otpDigits = ['', '', '', '', '', ''];
    public bool $isLoading = false;
    public bool $canResend = false;
    public int $cooldown = 0;
    public string $errorMessage = '';
    public string $maskedPhone = '';

    protected AuthServiceInterface $authService;
    protected OtpService $otpService;

    public function boot(AuthServiceInterface $authService, OtpService $otpService): void
    {
        $this->authService = $authService;
        $this->otpService = $otpService;
    }

    public function mount(): void
    {
        $userId = session('two_factor_user_id');

        if (!$userId) {
            $this->redirect(route('admin.login'), navigate: true);
            return;
        }

        $user = User::find($userId);

        if (!$user) {
            $this->redirect(route('admin.login'), navigate: true);
            return;
        }

        $this->maskedPhone = $this->otpService->maskPhone($user->phone);

        // Set initial cooldown
        $this->cooldown = $this->otpService->getCooldownRemaining($user->phone, 'two_factor');
        $this->canResend = $this->cooldown === 0;
    }

    public function handleKeydown($event, int $index): void
    {
        $key = $event['key'];

        // Handle backspace
        if ($key === 'Backspace') {
            $this->otpDigits[$index] = '';
            if ($index > 0) {
                $this->dispatch('focus-input', index: $index - 1);
            }
            return;
        }

        // Handle paste
        if ($key === 'Tab' || $key === ' ') {
            return;
        }

        // Only allow numbers
        if (!preg_match('/^[0-9]$/', $key)) {
            return;
        }

        // Set the digit
        $this->otpDigits[$index] = $key;

        // Auto-focus next input
        if ($index < 5) {
            $this->dispatch('focus-input', index: $index + 1);
        }

        // Auto-submit when all digits are filled
        $otp = implode('', $this->otpDigits);
        if (strlen($otp) === 6) {
            $this->verify();
        }
    }

    public function verify(): void
    {
        $this->errorMessage = '';

        $otp = implode('', $this->otpDigits);

        if (strlen($otp) !== 6) {
            $this->errorMessage = 'لطفاً کد ۶ رقمی را کامل وارد کنید.';
            return;
        }

        $this->isLoading = true;

        try {
            $userId = session('two_factor_user_id');
            $user = User::find($userId);

            if (!$user) {
                $this->redirect(route('admin.login'), navigate: true);
                return;
            }

            // Verify the OTP
            $verified = $this->authService->verifyTwoFactorCode($user, $otp);

            if (!$verified) {
                $this->errorMessage = 'کد وارد شده صحیح نیست یا منقضی شده است.';
                $this->otpDigits = ['', '', '', '', '', ''];
                $this->isLoading = false;
                return;
            }

            // Complete the login
            $remember = session('two_factor_remember', false);
            $this->authService->completeLogin($user, request()->merge(['remember' => $remember]));

            // Clear session data
            session()->forget(['two_factor_user_id', 'two_factor_remember']);

            // Show success animation
            $this->dispatch('login-success');

        } catch (\Exception $e) {
            $this->errorMessage = 'خطایی رخ داد. لطفاً دوباره تلاش کنید.';
        } finally {
            $this->isLoading = false;
        }
    }

    public function resendOtp(): void
    {
        $userId = session('two_factor_user_id');
        $user = User::find($userId);

        if (!$user) {
            $this->redirect(route('admin.login'), navigate: true);
            return;
        }

        // Check if can resend
        if (!$this->otpService->canRequest($user->phone, 'two_factor')) {
            $this->canResend = false;
            $this->cooldown = $this->otpService->getCooldownRemaining($user->phone, 'two_factor');
            return;
        }

        // Generate new OTP
        $otp = $this->otpService->generate($user->phone, 'two_factor', 6, 2);

        if ($otp) {
            $this->canResend = false;
            $this->cooldown = 60;
            $this->errorMessage = '';

            // Start countdown
            $this->dispatch('start-cooldown');
        } else {
            $this->errorMessage = 'خطا در ارسال کد. لطفاً دوباره تلاش کنید.';
        }
    }

    public function updatedOtpDigits(): void
    {
        $otp = implode('', $this->otpDigits);
        if (strlen($otp) === 6) {
            $this->verify();
        }
    }

    public function render()
    {
        return view('admin::livewire.auth.two-factor-form');
    }
}