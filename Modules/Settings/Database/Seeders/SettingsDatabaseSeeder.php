<?php

namespace Modules\Settings\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Models\Permission;
use Modules\Core\Models\Role;
use Modules\Settings\Models\Setting;
use Modules\Settings\Services\SettingsService;

class SettingsDatabaseSeeder extends Seeder
{
    /**
     * ثبت مجوزها + مقادیر پیش‌فرض تنظیمات (idempotent)
     */
    public function run(): void
    {
        // ===== مجوزها =====
        $newPermissions = [
            ['name' => 'مدیریت تنظیمات', 'slug' => 'manage_settings', 'group' => 'settings'],
        ];

        foreach ($newPermissions as $permission) {
            Permission::firstOrCreate(['slug' => $permission['slug']], $permission);
        }

        // فقط super-admin و admin به تنظیمات دسترسی دارند
        foreach (['super-admin', 'admin'] as $roleSlug) {
            $role = Role::where('slug', $roleSlug)->first();
            if (! $role) {
                continue;
            }

            $permissions = Permission::whereIn('slug', array_column($newPermissions, 'slug'))->get();
            $role->permissions()->syncWithoutDetaching($permissions);
        }

        $superAdmin = Role::where('slug', 'super-admin')->first();
        if ($superAdmin) {
            $superAdmin->permissions()->syncWithoutDetaching(Permission::all());
        }

        // ===== مقادیر پیش‌فرض تنظیمات (از schema سرویس) =====
        $service = app(SettingsService::class);

        foreach ($service->schema() as $groupKey => $groupDef) {
            foreach (($groupDef['fields'] ?? []) as $key => $field) {
                Setting::firstOrCreate(
                    ['key' => $key],
                    [
                        'group' => $groupKey,
                        'value' => (string) ($field['default'] ?? ''),
                        'is_public' => str_starts_with($key, 'site_') || str_starts_with($key, 'social_'),
                    ]
                );
            }
        }

        // شبکه‌های اجتماعی پیش‌فرض (JSON داینامیک)
        if (! Setting::where('key', 'social_links')->exists()) {
            $service->saveSocialLinks([
                ['name' => 'GitHub', 'url' => 'https://github.com/ali-shaghaghi', 'icon' => 'github', 'order' => 1],
                ['name' => 'تلگرام', 'url' => 'https://t.me/alishaghaghi', 'icon' => 'telegram', 'order' => 2],
            ]);
        }

        // پاک کردن کش پس از seed
        Setting::flushCache();
    }
}
