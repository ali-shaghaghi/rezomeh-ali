<?php

namespace Modules\Notifications\Livewire\Admin;

use Livewire\Component;
use Modules\Notifications\Services\NotificationService;

/**
 * زنگوله اعلان‌های هدر ادمین — تعداد خوانده‌نشده + لیست سریع.
 *
 * جایگزین محتوای hardcoded قبلی در partials/header.blade.php.
 * Polling هر ۳۰ ثانیه شمارنده را تازه می‌کند.
 */
class NotificationsBell extends Component
{
    protected $listeners = ['notification-sent' => '$refresh'];

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

    /**
     * پاک کردن همه اعلان‌ها (حذف کامل از لیست)
     */
    public function clearAll(): void
    {
        $user = auth()->user();

        if (! $user || ! $user->hasPermission('view_notifications')) {
            return;
        }

        app(NotificationService::class)->clearAll($user);
    }

    public function render(NotificationService $service)
    {
        $user = auth()->user();

        if (! $user) {
            return view('notifications::livewire.admin.bell', [
                'notifications' => collect(),
                'unreadCount' => 0,
            ]);
        }

        return view('notifications::livewire.admin.bell', [
            'notifications' => $service->forUser($user, 6),
            'unreadCount' => $service->unreadCount($user),
        ]);
    }
}
