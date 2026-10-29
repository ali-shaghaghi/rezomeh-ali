<?php

namespace Modules\UserPanel\Livewire;

use Livewire\Component;
use Modules\ProjectDev\Services\ProjectService;
use Modules\UserPanel\Services\UserPanelService;

/**
 * داشبورد کاربر — آمار و آخرین‌ها (به‌روزرسانی زنده با wire:poll)
 */
class Dashboard extends Component
{
    public function render()
    {
        $user = auth()->user();
        $service = app(UserPanelService::class);

        return view('userpanel::livewire.dashboard', [
            'stats' => $service->stats($user),
            'latestOrders' => $service->latestOrders($user),
            'latestTickets' => $service->latestTickets($user),
            'projectStats' => app(ProjectService::class)->userStats($user),
        ]);
    }
}
