<?php

namespace Modules\Orders\Policies;

use App\Models\User;
use Modules\Orders\Models\Order;

class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['manage_orders', 'view_orders']);
    }

    public function view(User $user, Order $order): bool
    {
        return $user->hasAnyPermission(['manage_orders', 'view_orders']);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('manage_orders');
    }

    public function update(User $user, Order $order): bool
    {
        return $user->hasPermission('manage_orders');
    }

    public function delete(User $user, Order $order): bool
    {
        return $user->hasPermission('manage_orders');
    }
}
