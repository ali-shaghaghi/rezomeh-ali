<?php

namespace Modules\Notifications\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * کلاس پایه برای همه اعلان‌های درون‌برنامه‌ای پروژه.
 *
 * داده‌ها به‌صورت آرایه به constructor پاس داده می‌شوند و در جدول
 * notifications (polymorphic لاراول) ذخیره می‌شوند. کانال تحویل
 * فقط database است — ایمیل/SMS از طریق ماژول‌های خودشان مدیریت می‌شوند.
 */
abstract class BaseNotification extends Notification
{
    use Queueable;

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(
        protected array $data = []
    ) {}

    /**
     * کانال‌های تحویل — فقط دیتابیس (اعلان‌های درون‌برنامه‌ای).
     *
     * @param  mixed  $notifiable
     * @return array<int, string>
     */
    public function via($notifiable): array
    {
        return ['database'];
    }

    /**
     * داده‌های ذخیره‌شده در جدول notifications.
     *
     * @return array<string, mixed>
     */
    public function toArray($notifiable): array
    {
        return $this->data;
    }

    /**
     * عنوان نمایشی اعلان (برای UI).
     */
    abstract public function title(): string;

    /**
     * توضیح نمایشی اعلان (برای UI).
     */
    abstract public function description(): string;

    /**
     * لینک ارجاع هنگام کلیک روی اعلان.
     */
    abstract public function url(): string;
}
