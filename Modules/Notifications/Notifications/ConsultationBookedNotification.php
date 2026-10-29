<?php

namespace Modules\Notifications\Notifications;

class ConsultationBookedNotification extends BaseNotification
{
    public function title(): string
    {
        return 'مشاوره رزرو شد';
    }

    public function description(): string
    {
        $userName = $this->data['user_name'] ?? 'کاربر';
        $typeName = $this->data['type_name'] ?? 'مشاوره';
        $date = $this->data['date'] ?? '';

        $desc = "{$userName} یک جلسه {$typeName} رزرو کرد";

        if ($date) {
            $desc .= " برای تاریخ {$date}";
        }

        return $desc;
    }

    public function url(): string
    {
        return route('admin.consultations.index');
    }
}
