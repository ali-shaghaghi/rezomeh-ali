<?php

namespace Modules\Settings\Providers;

use Livewire\Livewire;
use Modules\Core\Providers\BaseServiceProvider;
use Modules\Settings\Livewire\Admin\AvatarForm;
use Modules\Settings\Livewire\Admin\ChangePasswordForm;
use Modules\Settings\Livewire\Admin\LogoForm;
use Modules\Settings\Livewire\Admin\ProfileForm;
use Modules\Settings\Livewire\Admin\SecurityToggles;
use Modules\Settings\Livewire\Admin\SettingsManager;

class SettingsServiceProvider extends BaseServiceProvider
{
    protected function moduleName(): string
    {
        return 'Settings';
    }

    public function boot(): void
    {
        parent::boot();

        // دیتابیس ماژول تنظیمات — خودکفا (جدول settings داخل ماژول)
        $this->loadMigrationsFrom(module_path('Settings', 'Database'.DIRECTORY_SEPARATOR.'Migrations'));

        // روتینگ ماژول تنظیمات — خودکفا (ادمین با پیشوند admin/settings)
        $this->loadRoutesFrom(module_route_path('Settings', 'admin.php'));

        // ثبت کامپوننت‌های Livewire (پروفایل/امنیت از ماژول Admin به اینجا منتقل شد)
        Livewire::component('admin.settings.manager', SettingsManager::class);
        Livewire::component('admin.settings.profile-form', ProfileForm::class);
        Livewire::component('admin.settings.avatar-form', AvatarForm::class);
        Livewire::component('admin.settings.logo-form', LogoForm::class);
        Livewire::component('admin.settings.change-password-form', ChangePasswordForm::class);
        Livewire::component('admin.settings.security-toggles', SecurityToggles::class);
    }

    public function register(): void
    {
        parent::register();
    }
}
