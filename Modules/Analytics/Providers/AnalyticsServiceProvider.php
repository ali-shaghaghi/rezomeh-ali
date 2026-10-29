<?php

namespace Modules\Analytics\Providers;

use Livewire\Livewire;
use Modules\Analytics\Http\Controllers\AnalyticsController;
use Modules\Core\Providers\BaseServiceProvider;

class AnalyticsServiceProvider extends BaseServiceProvider
{
    protected function moduleName(): string
    {
        return 'Analytics';
    }

    public function boot(): void
    {
        parent::boot();

        // روتینگ ماژول گزارش‌ها — خودکفا (ادمین با پیشوند admin/analytics)
        $this->loadRoutesFrom(module_route_path('Analytics', 'admin.php'));

        // ثبت کامپوننت‌های Livewire ادمین
        Livewire::component('analytics.overview', \Modules\Analytics\Livewire\Admin\AnalyticsOverview::class);
        Livewire::component('analytics.sales', \Modules\Analytics\Livewire\Admin\SalesReport::class);
        Livewire::component('analytics.traffic', \Modules\Analytics\Livewire\Admin\TrafficReport::class);
        Livewire::component('analytics.content', \Modules\Analytics\Livewire\Admin\ContentReport::class);
    }

    public function register(): void
    {
        parent::register();

        // سرویس گزارش‌گیری به‌صورت Singleton (همان نمونه در کل درخواست)
        $this->app->singleton(\Modules\Analytics\Services\AnalyticsService::class);
    }
}
