<?php

namespace Modules\Products\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Models\Permission;
use Modules\Core\Models\Role;

class ProductsPermissionSeeder extends Seeder
{
    /**
     * ثبت مجوزهای ماژول محصولات به صورت idempotent
     */
    public function run(): void
    {
        $newPermissions = [
            ['name' => 'مدیریت محصولات', 'slug' => 'manage_products', 'group' => 'products'],
            ['name' => 'مشاهده محصولات', 'slug' => 'view_products', 'group' => 'products'],
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
