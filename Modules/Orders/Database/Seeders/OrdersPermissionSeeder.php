<?php

namespace Modules\Orders\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Models\Permission;
use Modules\Core\Models\Role;

class OrdersPermissionSeeder extends Seeder
{
    /**
     * ثبت مجوزهای ماژول سفارشات به صورت idempotent
     */
    public function run(): void
    {
        $newPermissions = [
            ['name' => 'مدیریت سفارشات', 'slug' => 'manage_orders', 'group' => 'orders'],
            ['name' => 'مشاهده سفارشات', 'slug' => 'view_orders', 'group' => 'orders'],
            ['name' => 'مدیریت پرداخت‌ها', 'slug' => 'manage_payments', 'group' => 'payments'],
            ['name' => 'مشاهده پرداخت‌ها', 'slug' => 'view_payments', 'group' => 'payments'],
        ];

        foreach ($newPermissions as $permission) {
            Permission::firstOrCreate(['slug' => $permission['slug']], $permission);
        }

        // اتصال مجوزهای جدید به نقش‌ها
        $rolePermissions = [
            'admin' => array_column($newPermissions, 'slug'),
            'support' => array_column($newPermissions, 'slug'),
        ];

        foreach ($rolePermissions as $roleSlug => $permissionSlugs) {
            $role = Role::where('slug', $roleSlug)->first();
            if (! $role) {
                continue;
            }

            $permissions = Permission::whereIn('slug', $permissionSlugs)->get();
            $role->permissions()->syncWithoutDetaching($permissions);
        }

        // نقش super-admin تمام مجوزها را دارد
        $superAdmin = Role::where('slug', 'super-admin')->first();
        if ($superAdmin) {
            $superAdmin->permissions()->syncWithoutDetaching(Permission::all());
        }
    }
}
