<?php

namespace Modules\Notifications\Notifications;

class NewTicketNotification extends BaseNotification
{
    public function title(): string
    {
        return 'تیکت پشتیبانی جدید';
    }

    public function description(): string
    {
        $subject = $this->data['ticket_subject'] ?? 'بدون موضوع';
        $userName = $this->data['user_name'] ?? 'کاربر';

        return "تیکت «{$subject}» توسط {$userName} باز شد";
    }

    public function url(): string
    {
        $id = $this->data['ticket_id'] ?? null;

        return $id
            ? route('admin.tickets.show', $id)
            : route('admin.tickets.index');
    }
}
