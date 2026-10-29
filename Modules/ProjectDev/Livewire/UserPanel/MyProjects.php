<?php

namespace Modules\ProjectDev\Livewire\UserPanel;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\ProjectDev\Services\ProjectService;

/**
 * درخواست‌های پروژه من — لیست + آمار (به‌روزرسانی زنده، پنل کاربر، ماژول ProjectDev)
 */
class MyProjects extends Component
{
    use WithPagination;

    public function render()
    {
        $user = auth()->user();
        $projectService = app(ProjectService::class);

        return view('projectdev::livewire.user-panel.my-projects', [
            'projectStats' => $projectService->userStats($user),
            'projectRequests' => $projectService->userRequests(
                $user,
                config('userpanel.orders_per_page', 9),
            ),
        ]);
    }
}
