<?php

namespace Modules\Admin\Livewire\Auth;

use App\Models\User;
use Livewire\Component;
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

    protected OtpService $otpService;

    public function boot(OtpService $otpService): void
    {
        $this->otpService = $otpService;
    }

    public function mount(): void
    {
        $userId = session('otp_user_id');
        if (!$userId) {
            $this->redirect(route('admin.login'), navigate: true);
            return;
        }

        $user = User::find($userId);
        if (!$user) {
            $this->redirect(route('admin.login'), navigate: true);
            return;
        }

        $this->method = session('otp_method', 'email');
        $this->maskedContact = $this->method === 'email'
            ? $this->otpService->maskEmail($user->email)
            : $this->otpService->maskPhone($user->phone ?? '');

        $this->cooldown = $this->otpService->getCooldownRemaining($user, 'login');
        $this->canResend = $this->cooldown === 0;
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

            if (!$user) {
                $this->redirect(route('admin.login'), navigate: true);
                return;
            }

            if (!$this->otpService->verify($user, $this->otp, 'login')) {
                $this->errorMessage = 'کد تایید نادرست یا منقضی شده است.';
                $this->otp = '';
                $this->isLoading = false;
                return;
            }

            auth()->login($user, session('otp_remember', false));
            request()->session()->regenerate();
            session()->forget(['otp_user_id', 'otp_method', 'otp_remember']);

            $this->redirect(route('admin.dashboard'));

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('OTP Verification Error: ' . $e->getMessage());
            $this->errorMessage = 'خطایی رخ داد. لطفاً دوباره تلاش کنید.';
        } finally {
            $this->isLoading = false;
        }
    }

    public function resendOtp(): void
    {
        $userId = session('otp_user_id');
        $user = User::find($userId);

        if (!$user) {
            $this->redirect(route('admin.login'), navigate: true);
            return;
        }

        if (!$this->otpService->canRequest($user, 'login')) {
            $this->canResend = false;
            $this->cooldown = $this->otpService->getCooldownRemaining($user, 'login');
            return;
        }

        $code = $this->otpService->generate($user, 'login');
        OtpNotification::sendViaMailtrap($user, $code, 'login');
        $this->otpService->setCooldown($user, 'login', 60);

        $this->canResend = false;
        $this->cooldown = 60;
        $this->errorMessage = '';
    }

    public function render()
    {
        return view('admin::livewire.auth.otp-verification');
    }
}