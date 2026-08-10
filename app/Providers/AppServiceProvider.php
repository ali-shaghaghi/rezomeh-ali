<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Modules\Core\Services\OtpService;
use Modules\Core\Services\TwoFactorService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->registerServices();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerModuleViews();
        $this->registerLivewireComponents();
    }

    /**
     * Register service bindings.
     */
    protected function registerServices(): void
    {
        $this->app->singleton(OtpService::class, fn () => new OtpService());
        $this->app->singleton(TwoFactorService::class, fn ($app) => new TwoFactorService($app->make(OtpService::class)));
    }

    /**
     * Register module view namespaces.
     */
    protected function registerModuleViews(): void
    {
        $modules = ['Admin', 'Users', 'Portfolio', 'Orders', 'Blog', 'Tickets', 'Settings', 'Analytics', 'AI', 'Notifications', 'Payments'];

        foreach ($modules as $module) {
            $viewPath = base_path("Modules/{$module}/Resources/views");
            if (is_dir($viewPath)) {
                View::addNamespace(strtolower($module), $viewPath);
            }
        }
    }

    /**
     * Register Livewire components from modules.
     */
    protected function registerLivewireComponents(): void
    {
        // Auth components
        Livewire::component('admin.auth.login-form', \Modules\Admin\Livewire\Auth\LoginForm::class);
        Livewire::component('admin.auth.two-factor-form', \Modules\Admin\Livewire\Auth\TwoFactorForm::class);
        Livewire::component('admin.auth.password-reset-form', \Modules\Admin\Livewire\Auth\PasswordResetForm::class);
        Livewire::component('admin.auth.otp-verification', \Modules\Admin\Livewire\Auth\OtpVerification::class);
        Livewire::component('admin.auth.change-password', \Modules\Admin\Livewire\Auth\ChangePassword::class);

        // Settings components
        Livewire::component('admin.settings.profile-form', \Modules\Admin\Livewire\Settings\ProfileForm::class);
        Livewire::component('admin.settings.avatar-form', \Modules\Admin\Livewire\Settings\AvatarForm::class);
        Livewire::component('admin.settings.logo-form', \Modules\Admin\Livewire\Settings\LogoForm::class);
    }
}