<?php

namespace Modules\UserPanel\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Modules\Orders\Models\Order;
use Modules\Tickets\Models\Ticket;

/**
 * سرویس پنل کاربر — فقط آمار داشبورد (منطق سفارش‌ها به ماژول Orders و
 * تیکت‌ها به ماژول Tickets منتقل شد: UserOrderService / UserTicketService).
 */
class UserPanelService
{
    /**
     * آمار کلی کاربر برای داشبورد و بج‌های سایدبار شل ریل‌تایم
     */
    public function stats(User $user): array
    {
        $orders = Order::where('user_id', $user->id);

        return [
            'orders_total' => (clone $orders)->count(),
            'orders_active' => (clone $orders)->whereIn('status', ['pending', 'review', 'accepted', 'development'])->count(),
            'orders_completed' => (clone $orders)->where('status', 'completed')->count(),
            'total_spent' => (clone $orders)->where('status', '!=', 'cancelled')->sum('paid_amount'),
            'tickets_total' => Ticket::where('user_id', $user->id)->count(),
            'tickets_open' => Ticket::where('user_id', $user->id)->unresolved()->count(),
        ];
    }

    public function latestOrders(User $user): Collection
    {
        return Order::where('user_id', $user->id)->latest()->latest('id')->limit(5)->get();
    }

    public function latestTickets(User $user): Collection
    {
        return Ticket::where('user_id', $user->id)->latest()->latest('id')->limit(5)->get();
    }
}
