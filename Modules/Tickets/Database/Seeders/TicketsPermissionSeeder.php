<?php

namespace Modules\Tickets\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Models\Permission;
use Modules\Core\Models\Role;

class TicketsPermissionSeeder extends Seeder
{
    /**
     * ثبت مجوزهای ماژول تیکت‌ها به صورت idempotent
     */
    public function run(): void
    {
        $newPermissions = [
            ['name' => 'مدیریت تیکت‌ها', 'slug' => 'manage_tickets', 'group' => 'tickets'],
            ['name' => 'مشاهده تیکت‌ها', 'slug' => 'view_tickets', 'group' => 'tickets'],
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
