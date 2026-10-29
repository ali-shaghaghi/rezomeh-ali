<?php

namespace Modules\Notifications\Services;

use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Collection;

/**
 * سرویس مرکزی اعلان‌های ماژول Notifications.
 *
 * اعلان‌ها در جدول notifications لاراول ذخیره می‌شوند (polymorphic).
 * این سرویس لایه نازکی روی آن است که با مجوزهای پروژه (قانون ۴۰۴) می‌چسبد.
 */
class NotificationService
{
    /**
     * تعداد اعلان‌های خوانده‌نشده یک کاربر.
     */
    public function unreadCount(User $user): int
    {
        return $user->unreadNotifications()->count();
    }

    /**
     * اعلان‌های اخیر یک کاربر (به‌صورت paginateٔ سبک روی Collection).
     *
     * @return Collection<int, DatabaseNotification>
     */
    public function forUser(User $user, int $limit = 10): Collection
    {
        return $user->notifications()->latest()->limit($limit)->get();
    }

    /**
     * علامت‌گذاری یک اعلان به‌عنوان خوانده‌شده (در صورت دسترسی کاربر).
     */
    public function markAsRead(User $user, string $notificationId): bool
    {
        $notification = $user->notifications()->where('id', $notificationId)->first();

        if (! $notification) {
            return false;
        }

        $notification->markAsRead();

        return true;
    }

    /**
     * علامت‌گذاری همه اعلان‌های کاربر به‌عنوان خوانده‌شده.
     */
    public function markAllAsRead(User $user): void
    {
        $user->unreadNotifications->markAsRead();
    }

    /**
     * حذف همه اعلان‌های کاربر (دکمه «پاک کردن همه»).
     */
    public function clearAll(User $user): void
    {
        $user->notifications()->delete();
    }

    /**
     * حذف اعلان‌های خوانده‌شده قدیمی (bigger than N days).
     */
    public function pruneRead(int $days = 30): int
    {
        return DatabaseNotification::whereNotNull('read_at')
            ->where('read_at', '<', now()->subDays($days))
            ->delete();
    }

    /**
     * ارسال یک اعلان به کاربران دارای یک مجوز (مثلاً همه ادمین‌ها).
     *
     * @param  array<string, mixed>  $data
     */
    public function notifyUsersWithPermission(string $permission, string $type, array $data): void
    {
        // کاربران عادیِ دارای مجوز (نقش‌های جدول user_role)
        $users = User::whereHas('roles.permissions', function ($query) use ($permission) {
            $query->where('slug', $permission);
        })->get();

        foreach ($users as $user) {
            $user->notify(new $type($data));
        }

        // کادر فنی (ادمین‌ها — جدول admins) که این مجوز را دارند
        $admins = \App\Models\Admin::whereHas('roles.permissions', function ($query) use ($permission) {
            $query->where('slug', $permission);
        })->get();

        foreach ($admins as $admin) {
            $admin->notify(new $type($data));
        }
    }

    /**
     * برچسب و رنگ نمایشی بر اساس نوع اعلان.
     *
     * @return array{icon: string, color: string, label: string}
     */
    public function styleFor(string $type): array
    {
        return match (true) {
            str_contains($type, 'Order')      => ['icon' => 'orders', 'color' => 'primary', 'label' => 'سفارش'],
            str_contains($type, 'Ticket')     => ['icon' => 'bell', 'color' => 'amber', 'label' => 'تیکت'],
            str_contains($type, 'Payment')    => ['icon' => 'wallet', 'color' => 'emerald', 'label' => 'پرداخت'],
            str_contains($type, 'Product')    => ['icon' => 'products', 'color' => 'violet', 'label' => 'محصول'],
            str_contains($type, 'Consultation') => ['icon' => 'users', 'color' => 'cyan', 'label' => 'مشاوره'],
            str_contains($type, 'NewUser')    => ['icon' => 'user', 'color' => 'sky', 'label' => 'ثبت‌نام'],
            default                           => ['icon' => 'bell', 'color' => 'slate', 'label' => 'اعلان'],
        };
    }
}
