<?php

namespace Modules\Orders\Livewire\Admin;

use Livewire\Component;
use Modules\Orders\Models\Order;

class OrderShow extends Component
{
    public Order $order;

    public function mount(Order $order): void
    {
        $this->order = $order->load(['user', 'payments' => fn ($q) => $q->latest()]);
    }

    public function render()
    {
        return view('orders::livewire.admin.order-show');
    }
}
