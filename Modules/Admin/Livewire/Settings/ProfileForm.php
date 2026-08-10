<?php

namespace Modules\Admin\Livewire\Settings;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class ProfileForm extends Component
{
    public string $name = '';
    public string $email = '';
    public bool $isLoading = false;
    public string $successMessage = '';
    public string $errorMessage = '';

    public function mount(): void
    {
        $user = Auth::user();
        $this->name = $user->name;
        $this->email = $user->email;
    }

    public function updateProfile(): void
    {
        $this->errorMessage = '';
        $this->successMessage = '';

        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . Auth::id(),
        ]);

        $this->isLoading = true;

        try {
            Auth::user()->update([
                'name' => $this->name,
                'email' => $this->email,
            ]);

            $this->successMessage = 'پروفایل با موفقیت به‌روزرسانی شد.';
        } catch (\Exception $e) {
            $this->errorMessage = 'خطایی رخ داد. لطفاً دوباره تلاش کنید.';
        } finally {
            $this->isLoading = false;
        }
    }

    public function render()
    {
        return view('admin::livewire.settings.profile-form');
    }
}