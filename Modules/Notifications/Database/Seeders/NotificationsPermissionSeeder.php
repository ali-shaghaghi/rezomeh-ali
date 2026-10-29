<?php

namespace Modules\Notifications\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Models\Permission;
use Modules\Core\Models\Role;

class NotificationsPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // مجوزهای گروه notifications (idempotent)
        $permissions = [
            ['name' => 'مشاهده اعلان‌ها', 'slug' => 'view_notifications', 'group' => 'notifications'],
            ['name' => 'مدیریت اعلان‌ها', 'slug' => 'manage_notifications', 'group' => 'notifications'],
        ];

        foreach ($permissions as $data) {
            Permission::firstOrCreate(['slug' => $data['slug']], $data);
        }

        // نقش‌های ادمین هر دو مجوز را دارند؛ کاربر عادی فقط view_notifications
        $adminSlugs = ['super-admin', 'admin', 'support'];

        foreach ($adminSlugs as $slug) {
            $role = Role::where('slug', $slug)->first();
            if ($role) {
                $role->permissions()->syncWithoutDetaching(
                    Permission::whereIn('slug', ['view_notifications', 'manage_notifications'])->pluck('id')
                );
            }
        }

        $userRole = Role::where('slug', 'user')->first();
        if ($userRole) {
            $userRole->permissions()->syncWithoutDetaching(
                Permission::where('slug', 'view_notifications')->pluck('id')
            );
        }
    }
}
