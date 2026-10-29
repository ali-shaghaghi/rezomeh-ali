<?php

namespace Modules\Blog\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Models\Permission;
use Modules\Core\Models\Role;

class BlogPermissionSeeder extends Seeder
{
    /**
     * ثبت مجوزهای ماژول وبلاگ به صورت idempotent
     */
    public function run(): void
    {
        $newPermissions = [
            ['name' => 'مدیریت وبلاگ', 'slug' => 'manage_blog', 'group' => 'blog'],
            ['name' => 'مشاهده وبلاگ', 'slug' => 'view_blog', 'group' => 'blog'],
            ['name' => 'مدیریت نظرات', 'slug' => 'manage_comments', 'group' => 'blog'],
            ['name' => 'مشاهده نظرات', 'slug' => 'view_comments', 'group' => 'blog'],
        ];

        foreach ($newPermissions as $permission) {
            Permission::firstOrCreate(['slug' => $permission['slug']], $permission);
        }

        // اتصال مجوزهای جدید به نقش‌ها
        $rolePermissions = [
            'admin' => array_column($newPermissions, 'slug'),
            'editor' => array_column($newPermissions, 'slug'),
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
