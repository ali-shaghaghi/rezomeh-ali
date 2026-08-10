<?php

namespace Modules\Admin\Livewire\Settings;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class AvatarForm extends Component
{
    use WithFileUploads;

    public $avatar;
    public bool $isLoading = false;
    public string $successMessage = '';
    public string $errorMessage = '';

    public function getAvatarUrlProperty(): string
    {
        $user = Auth::user();
        if ($user && $user->avatar && Storage::disk('public')->exists('avatars/' . $user->avatar)) {
            $url = asset('storage/avatars/' . $user->avatar);
            $path = storage_path('app/public/avatars/' . $user->avatar);
            if (file_exists($path)) {
                $url .= '?v=' . filemtime($path);
            }
            return $url;
        }
        return asset('img/avatar.svg');
    }

    public function uploadAvatar(): void
    {
        $this->errorMessage = '';
        $this->successMessage = '';

        if (!$this->avatar) {
            $this->errorMessage = 'لطفاً تصویری انتخاب کنید.';
            return;
        }

        try {
            $this->isLoading = true;

            $user = Auth::user();

            // Delete old avatar if exists
            if ($user->avatar && Storage::disk('public')->exists('avatars/' . $user->avatar)) {
                Storage::disk('public')->delete('avatars/' . $user->avatar);
            }

            // Store new avatar
            $filename = time() . '_' . Auth::id() . '.' . $this->avatar->getClientOriginalExtension();
            $this->avatar->storeAs('avatars', $filename, 'public');

            // Update user
            $user->update(['avatar' => $filename]);

            $this->avatar = null;
            $this->successMessage = 'آواتار با موفقیت به‌روزرسانی شد.';
            $this->dispatch('avatar-updated');

            // Refresh the page to update header avatar
            $this->refresh();

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Avatar upload error: ' . $e->getMessage());
            $this->errorMessage = 'خطایی رخ داد. لطفاً دوباره تلاش کنید.';
        } finally {
            $this->isLoading = false;
        }
    }

    public function deleteAvatar(): void
    {
        try {
            $user = Auth::user();

            if ($user->avatar && Storage::disk('public')->exists('avatars/' . $user->avatar)) {
                Storage::disk('public')->delete('avatars/' . $user->avatar);
            }

            $user->update(['avatar' => null]);
            $this->successMessage = 'آواتار حذف شد.';
            $this->dispatch('avatar-updated');

        } catch (\Exception $e) {
            $this->errorMessage = 'خطایی رخ داد.';
        }
    }

    public function render()
    {
        return view('admin::livewire.settings.avatar-form');
    }
}