<?php

namespace Modules\Analytics\Livewire\Admin;

use Livewire\Component;
use Modules\Analytics\Services\AnalyticsService;

/**
 * داشبورد گزارش‌ها — کارت‌های آمار + نمودار روند درآمد/سفارشات/کاربران
 * + نمودار دونات وضعیت سفارشات و روش‌های پرداخت.
 */
class AnalyticsOverview extends Component
{
    public function render(AnalyticsService $analytics)
    {
        return view('analytics::livewire.admin.overview', [
            'stats' => $analytics->keyStats(),
            'revenueChart' => $analytics->monthlyRevenue(6),
            'ordersChart' => $analytics->monthlyOrders(6),
            'usersChart' => $analytics->monthlyUsers(6),
            'orderStatusChart' => $analytics->ordersByStatus(),
            'paymentMethodChart' => $analytics->paymentsByMethod(),
        ]);
    }
}
