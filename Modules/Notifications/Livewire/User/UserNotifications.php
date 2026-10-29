<?php

namespace Modules\Notifications\Livewire\User;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\Notifications\Services\NotificationService;

/**
 * اعلان‌های پنل کاربر — کاربر اعلان‌های خودش را می‌بیند
 * (پرداخت موفق، پاسخ تیکت، وضعیت سفارش و...).
 */
class UserNotifications extends Component
{
    use WithPagination;

    public string $filter = 'all';

    protected $listeners = ['notification-sent' => '$refresh'];

    public function updatingFilter(): void
    {
        $this->resetPage();
    }

    public function markAsRead(string $id): void
    {
        $user = auth()->user();

        if (! $user) {
            return;
        }

        app(NotificationService::class)->markAsRead($user, $id);
    }

    public function markAllRead(): void
    {
        $user = auth()->user();

        if (! $user) {
            return;
        }

        app(NotificationService::class)->markAllAsRead($user);
    }

    public function render(NotificationService $service)
    {
        $user = auth()->user();

        if (! $user) {
            return view('notifications::livewire.user.notifications', [
                'notifications' => collect(),
                'unreadCount' => 0,
            ]);
        }

        $query = $user->notifications()->latest();

        if ($this->filter === 'unread') {
            $query->whereNull('read_at');
        }

        return view('notifications::livewire.user.notifications', [
            'notifications' => $query->paginate(10)->withQueryString(),
            'unreadCount' => $service->unreadCount($user),
        ]);
    }
}
