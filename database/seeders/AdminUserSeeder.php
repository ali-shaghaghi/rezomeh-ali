<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Modules\Core\Models\Role;

/**
 * ساخت حساب‌های کادر فنی — فقط در جدول admins.
 *
 * هیچ حساب ادمینی دیگر در جدول users ساخته نمی‌شود؛
 * کاربران عادی در RegularUserSeeder ساخته می‌شوند.
 */
class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admins = [
            ['name' => 'Ali Shaghaghi', 'email' => 'alishaghaghi.it@gmail.com', 'phone' => '09210344617', 'role' => 'super-admin'],
            ['name' => 'Admin User', 'email' => 'admin2@alishaghaghi.ir', 'phone' => '09121234568', 'role' => 'admin'],
            ['name' => 'Editor User', 'email' => 'editor@alishaghaghi.ir', 'phone' => '09121234569', 'role' => 'editor'],
            ['name' => 'Support User', 'email' => 'support@alishaghaghi.ir', 'phone' => '09121234570', 'role' => 'support'],
            ['name' => 'Consultant User', 'email' => 'consultant@alishaghaghi.ir', 'phone' => '09121234571', 'role' => 'consultant'],
        ];

        foreach ($admins as $data) {
            $role = Role::where('slug', $data['role'])->first();

            if (! $role) {
                continue;
            }

            // idempotent — اگر قبلاً ساخته شده دوباره ساخته نمی‌شود
            $admin = Admin::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'phone' => $data['phone'],
                    'password' => bcrypt('password'),
                    'email_verified_at' => now(),
                    'phone_verified_at' => now(),
                    'is_active' => true,
                ]
            );

            if (! $admin->roles()->where('role_id', $role->id)->exists()) {
                $admin->roles()->attach($role);
            }
        }
    }
}
