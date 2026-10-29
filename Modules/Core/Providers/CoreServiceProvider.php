<?php

namespace Modules\Core\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Core\Services\AuthenticationService;
use Modules\Core\Services\Contracts\AuthServiceInterface;
use Modules\Core\Services\OtpService;
use Modules\Core\Services\ServicesHubService;
use Modules\Core\Services\TwoFactorService;

class CoreServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(OtpService::class, fn () => new OtpService());
        $this->app->singleton(TwoFactorService::class, fn ($app) => new TwoFactorService($app->make(OtpService::class)));

        // اتصال قرارداد سرویس احراز هویت (استفاده در TwoFactorForm و ...)
        $this->app->singleton(AuthServiceInterface::class, fn ($app) => new AuthenticationService(
            $app->make(OtpService::class),
            $app->make(TwoFactorService::class),
        ));

        // Services Hub — رجیستر مرکزی برای اتصال بین‌ماژولی سرویس‌ها
        $this->app->singleton(ServicesHubService::class, fn () => new ServicesHubService());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}