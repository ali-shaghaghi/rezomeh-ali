<?php

namespace Modules\Admin\Livewire\Auth;

use Livewire\Component;
use App\Models\User;
use Modules\Core\Services\OtpService;
use Modules\Core\Notifications\OtpNotification;

class PasswordResetForm extends Component
{
    public int $step = 1;
    public string $email = '';
    public string $otp = '';
    public string $newPassword = '';
    public string $newPasswordConfirmation = '';
    public bool $isLoading = false;
    public string $errorMessage = '';
    public string $successMessage = '';

    protected OtpService $otpService;

    public function boot(OtpService $otpService): void
    {
        $this->otpService = $otpService;
    }

    public function sendOtp(): void
    {
        $this->errorMessage = '';
        $this->validate([
            'email' => 'required|email|exists:users,email',
        ], [
            'email.required' => 'ایمیل الزامی است.',
            'email.email' => 'فرمت ایمیل صحیح نیست.',
            'email.exists' => 'کاربری با این ایمیل یافت نشد.',
        ]);

        $this->isLoading = true;

        try {
            $user = User::where('email', $this->email)->first();

            if (!$user) {
                $this->errorMessage = 'کاربری با این ایمیل یافت نشد.';
                $this->isLoading = false;
                return;
            }

            if (!$this->otpService->canRequest($user, 'password_reset')) {
                $this->errorMessage = 'لطفاً ۶۰ ثانیه صبر کنید.';
                $this->isLoading = false;
                return;
            }

            $code = $this->otpService->generate($user, 'password_reset', 6, 15);
            OtpNotification::sendViaMailtrap($user, $code, 'password_reset');
            $this->otpService->setCooldown($user, 'password_reset', 60);

            $this->step = 2;
            $this->successMessage = 'کد بازیابی به ایمیل شما ارسال شد.';

        } catch (\Exception $e) {
            $this->errorMessage = 'خطایی رخ داد. لطفاً دوباره تلاش کنید.';
        } finally {
            $this->isLoading = false;
        }
    }

    public function verifyOtp(): void
    {
        $this->errorMessage = '';
        $this->validate([
            'otp' => 'required|string|size:6',
        ], [
            'otp.required' => 'کد تایید الزامی است.',
            'otp.size' => 'کد تایید باید ۶ رقم باشد.',
        ]);

        $this->isLoading = true;

        try {
            $user = User::where('email', $this->email)->first();

            if (!$user) {
                $this->errorMessage = 'کاربر یافت نشد.';
                $this->isLoading = false;
                return;
            }

            if (!$this->otpService->verify($user, $this->otp, 'password_reset')) {
                $this->errorMessage = 'کد وارد شده صحیح نیست یا منقضی شده است.';
                $this->otp = '';
                $this->isLoading = false;
                return;
            }

            $this->step = 3;
            $this->successMessage = 'هویت شما تایید شد. رمز عبور جدید را وارد کنید.';

        } catch (\Exception $e) {
            $this->errorMessage = 'خطایی رخ داد. لطفاً دوباره تلاش کنید.';
        } finally {
            $this->isLoading = false;
        }
    }

    public function resetPassword(): void
    {
        $this->errorMessage = '';

        $this->validate([
            'newPassword' => 'required|string|min:8',
            'newPasswordConfirmation' => 'required|same:newPassword',
        ], [
            'newPassword.required' => 'رمز عبور جدید الزامی است.',
            'newPassword.min' => 'رمز عبور باید حداقل ۸ کاراکتر باشد.',
            'newPasswordConfirmation.required' => 'تکرار رمز عبور الزامی است.',
            'newPasswordConfirmation.same' => 'تکرار رمز عبور مطابقت ندارد.',
        ]);

        $this->isLoading = true;

        try {
            $user = User::where('email', $this->email)->first();

            if (!$user) {
                $this->errorMessage = 'کاربر یافت نشد.';
                $this->isLoading = false;
                return;
            }

            $user->update([
                'password' => bcrypt($this->newPassword),
            ]);

            $this->step = 4;
            $this->successMessage = 'رمز عبور با موفقیت تغییر کرد.';

        } catch (\Exception $e) {
            $this->errorMessage = 'خطایی رخ داد. لطفاً دوباره تلاش کنید.';
        } finally {
            $this->isLoading = false;
        }
    }

    public function render()
    {
        return view('admin::livewire.auth.password-reset-form');
    }
}