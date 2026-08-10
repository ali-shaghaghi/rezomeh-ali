<?php

namespace Modules\Admin\Livewire\Auth;

use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Facades\Hash;

class ChangePassword extends Component
{
    public string $currentPassword = '';
    public string $newPassword = '';
    public string $newPasswordConfirmation = '';
    public bool $isLoading = false;
    public string $successMessage = '';
    public string $errorMessage = '';

    protected $rules = [
        'currentPassword' => 'required|string',
        'newPassword' => 'required|string|min:8|confirmed',
    ];

    public function changePassword(): void
    {
        $this->errorMessage = '';
        $this->successMessage = '';

        $this->validate([
            'currentPassword' => 'required|string',
            'newPassword' => 'required|string|min:8',
            'newPasswordConfirmation' => 'required|same:newPassword',
        ], [
            'currentPassword.required' => 'رمز عبور فعلی الزامی است.',
            'newPassword.required' => 'رمز عبور جدید الزامی است.',
            'newPassword.min' => 'رمز عبور باید حداقل ۸ کاراکتر باشد.',
            'newPasswordConfirmation.required' => 'تکرار رمز عبور الزامی است.',
            'newPasswordConfirmation.same' => 'تکرار رمز عبور مطابقت ندارد.',
        ]);

        $this->isLoading = true;

        try {
            $user = auth()->user();

            // Verify current password
            if (!Hash::check($this->currentPassword, $user->password)) {
                $this->errorMessage = 'رمز عبور فعلی صحیح نیست.';
                $this->isLoading = false;
                return;
            }

            // Update password
            $user->update([
                'password' => Hash::make($this->newPassword),
            ]);

            $this->successMessage = 'رمز عبور با موفقیت تغییر کرد.';
            $this->currentPassword = '';
            $this->newPassword = '';
            $this->newPasswordConfirmation = '';

        } catch (\Exception $e) {
            $this->errorMessage = 'خطایی رخ داد. لطفاً دوباره تلاش کنید.';
        } finally {
            $this->isLoading = false;
        }
    }

    public function render()
    {
        return view('admin::livewire.auth.change-password');
    }
}