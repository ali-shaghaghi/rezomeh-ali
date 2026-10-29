<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // مجوز مدیریت پیامک — خودترمیم برای دیتابیس‌هایی که قبلاً Seed شده‌اند
    public function up(): void
    {
        $now = now();

        $exists = DB::table('permissions')->where('slug', 'manage_sms')->first();

        if (! $exists) {
            DB::table('permissions')->insert([
                'name' => 'مدیریت پیامک',
                'slug' => 'manage_sms',
                'group' => 'sms',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // اتصال مجوز به نقش‌های ادمین (super-admin همه مجوزها را دارد)
        $permissionId = DB::table('permissions')->where('slug', 'manage_sms')->value('id');

        $roleIds = DB::table('roles')->whereIn('slug', ['super-admin', 'admin'])->pluck('id');

        foreach ($roleIds as $roleId) {
            $attached = DB::table('role_permission')
                ->where('role_id', $roleId)
                ->where('permission_id', $permissionId)
                ->exists();

            if (! $attached) {
                DB::table('role_permission')->insert([
                    'role_id' => $roleId,
                    'permission_id' => $permissionId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        $permissionId = DB::table('permissions')->where('slug', 'manage_sms')->value('id');

        if ($permissionId) {
            DB::table('role_permission')->where('permission_id', $permissionId)->delete();
            DB::table('permissions')->where('id', $permissionId)->delete();
        }
    }
};