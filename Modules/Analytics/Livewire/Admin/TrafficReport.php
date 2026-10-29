<?php

namespace Modules\Analytics\Livewire\Admin;

use Livewire\Component;
use Modules\Analytics\Services\AnalyticsService;

/**
 * گزارش ترافیک کاربران — کاربران آنلاین، آخرین فعالیت‌ها،
 * صفحات پر بازدید و روند ثبت‌نام کاربران.
 */
class TrafficReport extends Component
{
    public function render(AnalyticsService $analytics)
    {
        return view('analytics::livewire.admin.traffic', [
            'onlineUsers' => $analytics->onlineUsers(5),
            'recentActivity' => $analytics->recentActivity(15),
            'topPages' => $analytics->topPages(10),
            'usersChart' => $analytics->monthlyUsers(6),
            'stats' => $analytics->keyStats(),
        ]);
    }
}
