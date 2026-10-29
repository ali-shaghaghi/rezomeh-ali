<?php

namespace Modules\Orders\Http\Controllers;

use App\Http\Controllers\Controller;

class UserPanelOrderController extends Controller
{
    /**
     * جزئیات سفارش من — صفحه میزبان کامپوننت Livewire ماژول Orders
     * (خودِ داده و دسترسی داخل OrderDetails با UserOrderService چک می‌شود).
     */
    public function show(string $orderNumber)
    {
        return view('userpanel::orders.show', ['orderNumber' => $orderNumber]);
    }
}
