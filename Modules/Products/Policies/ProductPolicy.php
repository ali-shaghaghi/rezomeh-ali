<?php

namespace Modules\Products\Policies;

use App\Models\User;
use Modules\Products\Models\Product;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['manage_products', 'view_products']);
    }

    public function view(User $user, ?Product $product = null): bool
    {
        return $user->hasAnyPermission(['manage_products', 'view_products']);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('manage_products');
    }

    public function update(User $user, ?Product $product = null): bool
    {
        return $user->hasPermission('manage_products');
    }

    public function delete(User $user, ?Product $product = null): bool
    {
        return $user->hasPermission('manage_products');
    }
}