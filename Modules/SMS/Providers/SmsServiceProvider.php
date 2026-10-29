<?php

namespace Modules\SMS\Providers;

use Illuminate\Support\Facades\View;
use Livewire\Livewire;
use Modules\Core\Providers\BaseServiceProvider;

class SmsServiceProvider extends BaseServiceProvider
{
    protected function moduleName(): string
    {
        return 'SMS';
    }

    public function boot(): void
    {
        parent::boot();

        // دیتابیس ماژول پیامک — خودکفا (providers/logs/templates داخل ماژول)
        $this->loadMigrationsFrom(module_path('SMS', 'Database'.DIRECTORY_SEPARATOR.'Migrations'));

        // روتینگ ماژول پیامک — خودکفا (ادمین با پیشوند admin/sms)
        $this->loadRoutesFrom(module_route_path('SMS', 'admin.php'));

        // ثبت کامپوننت‌های Livewire ادمین
        Livewire::component('sms.settings', \Modules\SMS\Livewire\Admin\SmsSettings::class);
        Livewire::component('sms.logs', \Modules\SMS\Livewire\Admin\SmsLogs::class);
        Livewire::component('sms.templates', \Modules\SMS\Livewire\Admin\SmsTemplates::class);
    }

    public function register(): void
    {
        parent::register();

        // helperهای عمومی ماژول (send_sms) — بارگذاری یک‌باره
        if (file_exists($helpers = module_path('SMS', 'helpers.php'))) {
            require_once $helpers;
        }

        // اتصال به Services Hub — سرویس پیامک در دسترس سایر ماژول‌ها قرار می‌گیرد
        $this->app->singleton(\Modules\SMS\Services\SmsService::class, function ($app) {
            $service = new \Modules\SMS\Services\SmsService;

            $app->make(\Modules\Core\Services\ServicesHubService::class)
                ->register('sms', fn () => $service);

            return $service;
        });
    }
}