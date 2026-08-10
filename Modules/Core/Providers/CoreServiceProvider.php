<?php

namespace Modules\Core\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Core\Services\OtpService;
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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}