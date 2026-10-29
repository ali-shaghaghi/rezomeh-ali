<?php

namespace Modules\Notifications\Notifications;

class ProductSoldNotification extends BaseNotification
{
    public function title(): string
    {
        return 'محصول فروخته شد';
    }

    public function description(): string
    {
        $productName = $this->data['product_name'] ?? 'محصول';
        $quantity = $this->data['quantity'] ?? 1;
        $revenue = isset($this->data['revenue']) ? number_format((float) $this->data['revenue']) : null;

        $desc = "{$productName} ({$quantity} عدد)";

        if ($revenue) {
            $desc .= " — درآمد {$revenue} تومان";
        }

        return $desc;
    }

    public function url(): string
    {
        return route('admin.analytics.sales');
    }
}
