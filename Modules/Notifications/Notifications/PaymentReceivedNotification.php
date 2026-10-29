<?php

namespace Modules\Notifications\Notifications;

class PaymentReceivedNotification extends BaseNotification
{
    public function title(): string
    {
        return 'پرداخت دریافت شد';
    }

    public function description(): string
    {
        $amount = isset($this->data['amount']) ? number_format((float) $this->data['amount']) : '—';
        $orderNumber = $this->data['order_number'] ?? '';

        $desc = "مبلغ {$amount} تومان دریافت شد";

        if ($orderNumber) {
            $desc .= " برای سفارش {$orderNumber}";
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
