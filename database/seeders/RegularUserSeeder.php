<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Modules\Core\Models\Role;

/**
 * ساخت کاربران عادی دمو — فقط نقش «کاربر» (جدول users).
 *
 * کاربران هیچ‌گاه نقش بالاتری نمی‌گیرند؛ کادر فنی در AdminUserSeeder
 * و جداگانه در جدول admins ساخته می‌شود.
 */
class RegularUserSeeder extends Seeder
{
    public function run(): void
    {
        $userRole = Role::where('slug', 'user')->first();

        if (! $userRole) {
            return;
        }

        $users = [
            ['name' => 'محمد رضایی', 'email' => 'mohammad@example.com', 'phone' => '09121111111'],
            ['name' => 'سارا احمدی', 'email' => 'sara@example.com', 'phone' => '09122222222'],
            ['name' => 'علی کریمی', 'email' => 'ali@example.com', 'phone' => '09123333333'],
            ['name' => 'nazanin.mousavi', 'email' => 'nazanin@example.com', 'phone' => '09124444444', 'is_active' => false],
            ['name' => 'امیر حسینی', 'email' => 'amir@example.com', 'phone' => '09125555555'],
            ['name' => 'زهرا محمدی', 'email' => 'zahra@example.com', 'phone' => '09126666666'],
            ['name' => 'رضا عباسی', 'email' => 'reza@example.com', 'phone' => '09127777777'],
            ['name' => 'مریم نوری', 'email' => 'maryam@example.com', 'phone' => '09128888888', 'is_active' => false],
            ['name' => 'حسن جعفری', 'email' => 'hasan@example.com', 'phone' => '09129999999'],
            ['name' => 'فاطمه کاظمی', 'email' => 'fateme@example.com', 'phone' => '09120000000'],
        ];

        foreach ($users as $data) {
            $isActive = $data['is_active'] ?? true;
            unset($data['is_active']);

            $user = User::firstOrCreate(
                ['email' => $data['email']],
                array_merge($data, [
                    'password' => bcrypt('password'),
                    'email_verified_at' => now(),
                    'phone_verified_at' => now(),
                    'is_active' => $isActive,
                ])
            );

            // فقط نقش «کاربر» — بدون هیچ ارتقای سطحی
            if (! $user->roles()->where('role_id', $userRole->id)->exists()) {
                $user->roles()->attach($userRole);
            }

            // پاک‌سازی تضمینی: هر نقش غیر «کاربر» که قبلاً روی ردیف بوده
            $user->roles()->where('slug', '!=', 'user')->detach();
        }
    }
}
