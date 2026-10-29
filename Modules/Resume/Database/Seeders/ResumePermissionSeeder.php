<?php

namespace Modules\Resume\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Models\Permission;
use Modules\Core\Models\Role;

class ResumePermissionSeeder extends Seeder
{
    /**
     * ثبت مجوزهای ماژول رزومه (نمونه کار/مهارت/تحصیلات/تجربیات/زبان‌ها) به صورت idempotent
     */
    public function run(): void
    {
        $newPermissions = [
            ['name' => 'مدیریت نمونه کارها', 'slug' => 'manage_projects', 'group' => 'resume'],
            ['name' => 'مشاهده نمونه کارها', 'slug' => 'view_projects', 'group' => 'resume'],
            ['name' => 'مدیریت مهارت‌ها', 'slug' => 'manage_skills', 'group' => 'resume'],
            ['name' => 'مشاهده مهارت‌ها', 'slug' => 'view_skills', 'group' => 'resume'],
            ['name' => 'مدیریت تحصیلات', 'slug' => 'manage_education', 'group' => 'resume'],
            ['name' => 'مشاهده تحصیلات', 'slug' => 'view_education', 'group' => 'resume'],
            ['name' => 'مدیریت تجربیات', 'slug' => 'manage_experience', 'group' => 'resume'],
            ['name' => 'مشاهده تجربیات', 'slug' => 'view_experience', 'group' => 'resume'],
            ['name' => 'مدیریت زبان‌ها', 'slug' => 'manage_languages', 'group' => 'resume'],
            ['name' => 'مشاهده زبان‌ها', 'slug' => 'view_languages', 'group' => 'resume'],
            ['name' => 'مدیریت رزومه', 'slug' => 'manage_resume', 'group' => 'resume'],
            ['name' => 'مشاهده رزومه', 'slug' => 'view_resume', 'group' => 'resume'],
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
