<?php

namespace Modules\Notifications\Notifications;

class NewOrderNotification extends BaseNotification
{
    public function title(): string
    {
        return 'سفارش جدید ثبت شد';
    }

    public function description(): string
    {
        $number = $this->data['order_number'] ?? '—';
        $title = $this->data['order_title'] ?? 'بدون عنوان';
        $amount = isset($this->data['amount']) ? number_format((float) $this->data['amount']) : null;

        $desc = "سفارش «{$title}» (شماره {$number})";

        if ($amount) {
            $desc .= " به مبلغ {$amount} تومان";
        }

        return $desc;
    }

    public function url(): string
    {
        $id = $this->data['order_id'] ?? null;

        return $id
            ? route('admin.orders.show', $id)
            : route('admin.orders.index');
    }
}
