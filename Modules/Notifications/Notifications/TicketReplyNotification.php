<?php

namespace Modules\Notifications\Notifications;

class TicketReplyNotification extends BaseNotification
{
    public function title(): string
    {
        return 'پاسخ جدید به تیکت';
    }

    public function description(): string
    {
        $subject = $this->data['ticket_subject'] ?? 'بدون موضوع';
        $responder = $this->data['responder_name'] ?? 'کارشناس';

        return "{$responder} به تیکت «{$subject}» پاسخ داد";
    }

    public function url(): string
    {
        $id = $this->data['ticket_id'] ?? null;

        return $id
            ? route('admin.tickets.show', $id)
            : route('admin.tickets.index');
    }
}
