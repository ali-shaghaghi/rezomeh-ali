<?php

namespace Modules\Orders\Livewire\UserPanel;

use Livewire\Component;
use Modules\Orders\Models\Order;
use Modules\Orders\Services\UserOrderService;

/**
 * جزئیات سفارش من — تسک‌ها/پرداخت‌ها/لایسنس‌ها (پنل کاربر، ماژول Orders)
 */
class OrderDetails extends Component
{
    public Order $order;

    public function mount(string $orderNumber): void
    {
        $order = app(UserOrderService::class)->order(auth()->user(), $orderNumber);

        abort_unless($order, 404);

        $this->order = $order;
    }

    public function render()
    {
        return view('orders::livewire.user-panel.order-details');
    }
}
