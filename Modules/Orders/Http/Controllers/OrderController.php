<?php

namespace Modules\Orders\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Modules\Orders\Models\Order;

class OrderController extends Controller
{
    use AuthorizesRequests;

    /**
     * لیست سفارش‌ها (Livewire)
     */
    public function index()
    {
        $this->authorize('viewAny', Order::class);

        return view('orders::index');
    }

    /**
     * سفارش‌های در انتظار (همان Livewire با فیلتر پیش‌فرض)
     */
    public function pending()
    {
        $this->authorize('viewAny', Order::class);

        return view('orders::pending');
    }

    /**
     * نمایش مستقل سفارش (Livewire)
     */
    public function show(Order $order)
    {
        $this->authorize('view', $order);

        return view('orders::show', compact('order'));
    }
}
