<?php

namespace Modules\Admin\Livewire\Settings;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class LogoForm extends Component
{
    use WithFileUploads;

    public $logo;
    public bool $isLoading = false;
    public string $successMessage = '';
    public string $errorMessage = '';

    public function getLogoUrlProperty(): string
    {
        $logoPath = config('app.logo_path');
        if ($logoPath && Storage::disk('public')->exists($logoPath)) {
            $url = asset('storage/' . $logoPath);
            $fullPath = storage_path('app/public/' . $logoPath);
            if (file_exists($fullPath)) {
                $url .= '?v=' . filemtime($fullPath);
            }
            return $url;
        }
        return asset('img/logo.png');
    }

    public function updatedLogo(): void
    {
        $this->validate([
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:4096',
        ]);
    }

    public function uploadLogo(): void
    {
        $this->errorMessage = '';
        $this->successMessage = '';

        if (!$this->logo) {
            $this->errorMessage = 'لطفاً تصویری انتخاب کنید.';
            return;
        }

        $this->validate([
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:4096',
        ]);

        $this->isLoading = true;

        try {
            // Delete old logo if exists
            $oldPath = config('app.logo_path');
            if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }

            // Store new logo
            $filename = 'logo.' . $this->logo->getClientOriginalExtension();
            $this->logo->storeAs('logos', $filename, 'public');

            // Save path to config
            config(['app.logo_path' => 'logos/' . $filename]);

            // Also save to .env or config file for persistence
            $this->saveLogoPath('logos/' . $filename);

            $this->logo = null;
            $this->successMessage = 'لوگو با موفقیت به‌روزرسانی شد.';
            $this->dispatch('logo-updated');

        } catch (\Exception $e) {
            $this->errorMessage = 'خطایی رخ داد. لطفاً دوباره تلاش کنید.';
        } finally {
            $this->isLoading = false;
        }
    }

    public function resetLogo(): void
    {
        try {
            $oldPath = config('app.logo_path');
            if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }

            config(['app.logo_path' => null]);
            $this->saveLogoPath(null);

            $this->successMessage = 'لوگو به حالت پیش‌فرض بازگشت.';
            $this->dispatch('logo-updated');

        } catch (\Exception $e) {
            $this->errorMessage = 'خطایی رخ داد.';
        }
    }

    protected function saveLogoPath(?string $path): void
    {
        // Save to a simple settings file
        $settingsPath = storage_path('app/settings.json');
        $settings = file_exists($settingsPath) ? json_decode(file_get_contents($settingsPath), true) : [];
        $settings['logo_path'] = $path;
        file_put_contents($settingsPath, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    public function render()
    {
        return view('admin::livewire.settings.logo-form');
    }
}