<?php

namespace Modules\Notifications\Notifications;

/**
 * ثبت‌نام کاربر جدید — برای زنگوله ادمین.
 */
class NewUserNotification extends BaseNotification
{
    public function title(): string
    {
        return 'کاربر جدید ثبت‌نام کرد';
    }

    public function description(): string
    {
        $name = $this->data['user_name'] ?? 'کاربر';
        $email = $this->data['user_email'] ?? '';

        return $email !== '' ? "{$name} ({$email}) عضو سایت شد." : "{$name} عضو سایت شد.";
    }

    public function url(): string
    {
        $id = $this->data['user_id'] ?? null;

        return $id
            ? route('admin.users.show', $id)
            : route('admin.users.index');
    }
}
