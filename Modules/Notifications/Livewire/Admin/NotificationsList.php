<?php

namespace Modules\Notifications\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\Notifications\Services\NotificationService;

/**
 * صفحه کامل اعلان‌های ادمین — لیست پیجینیت‌شده + علامت‌گذاری + حذف.
 */
class NotificationsList extends Component
{
    use WithPagination;

    public string $filter = 'all';

    public function updatingFilter(): void
    {
        $this->resetPage();
    }

    public function markAsRead(string $id): void
    {
        $user = auth()->user();

        if (! $user || ! $user->hasPermission('view_notifications')) {
            return;
        }

        app(NotificationService::class)->markAsRead($user, $id);
    }

    public function markAllRead(): void
    {
        $user = auth()->user();

        if (! $user || ! $user->hasPermission('view_notifications')) {
            return;
        }

        app(NotificationService::class)->markAllAsRead($user);
    }

    public function render(NotificationService $service)
    {
        $user = auth()->user();

        if (! $user) {
            return view('notifications::livewire.admin.list', [
                'notifications' => collect(),
                'unreadCount' => 0,
            ]);
        }

        $query = $user->notifications()->latest();

        if ($this->filter === 'unread') {
            $query->whereNull('read_at');
        }

        return view('notifications::livewire.admin.list', [
            'notifications' => $query->paginate(15)->withQueryString(),
            'unreadCount' => $service->unreadCount($user),
        ]);
    }
}
