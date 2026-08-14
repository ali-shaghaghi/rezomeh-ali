<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Modules\Core\Models\Role;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        if (User::count() >= 4) {
            $this->addAdditionalUsers();
            return;
        }

        // Create Super Admin
        $superAdmin = User::create([
            'name' => 'Ali Shaghaghi',
            'email' => 'admin@alishaghaghi.ir',
            'phone' => '09121234567',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
            'is_active' => true,
        ]);

        $superAdminRole = Role::where('slug', 'super-admin')->first();
        if ($superAdminRole) {
            $superAdmin->roles()->attach($superAdminRole);
        }

        // Create Admin
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin2@alishaghaghi.ir',
            'phone' => '09121234568',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
            'is_active' => true,
        ]);

        $adminRole = Role::where('slug', 'admin')->first();
        if ($adminRole) {
            $admin->roles()->attach($adminRole);
        }

        // Create Editor
        $editor = User::create([
            'name' => 'Editor User',
            'email' => 'editor@alishaghaghi.ir',
            'phone' => '09121234569',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
            'is_active' => true,
        ]);

        $editorRole = Role::where('slug', 'editor')->first();
        if ($editorRole) {
            $editor->roles()->attach($editorRole);
        }

        // Create Support
        $support = User::create([
            'name' => 'Support User',
            'email' => 'support@alishaghaghi.ir',
            'phone' => '09121234570',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
            'is_active' => true,
        ]);

        $supportRole = Role::where('slug', 'support')->first();
        if ($supportRole) {
            $support->roles()->attach($supportRole);
        }

        $this->addAdditionalUsers();
    }

    private function addAdditionalUsers(): void
    {
        $additionalUsers = [
            ['name' => 'محمد رضایی', 'email' => 'mohammad@example.com', 'phone' => '09121111111', 'role' => 'admin'],
            ['name' => 'سارا احمدی', 'email' => 'sara@example.com', 'phone' => '09122222222', 'role' => 'editor'],
            ['name' => 'علی کریمی', 'email' => 'ali@example.com', 'phone' => '09123333333', 'role' => 'support'],
            ['name' => 'nazanin.mousavi', 'email' => 'nazanin@example.com', 'phone' => '09124444444', 'role' => 'editor', 'is_active' => false],
            ['name' => 'امیر حسینی', 'email' => 'amir@example.com', 'phone' => '09125555555', 'role' => 'admin'],
            ['name' => 'زهرا محمدی', 'email' => 'zahra@example.com', 'phone' => '09126666666', 'role' => 'support'],
            ['name' => 'رضا عباسی', 'email' => 'reza@example.com', 'phone' => '09127777777', 'role' => 'editor'],
            ['name' => 'مریم نوری', 'email' => 'maryam@example.com', 'phone' => '09128888888', 'role' => 'admin', 'is_active' => false],
            ['name' => 'حسن جعفری', 'email' => 'hasan@example.com', 'phone' => '09129999999', 'role' => 'support'],
            ['name' => 'فاطمه کاظمی', 'email' => 'fateme@example.com', 'phone' => '09120000000', 'role' => 'editor'],
        ];

        foreach ($additionalUsers as $userData) {
            $roleSlug = $userData['role'];
            unset($userData['role']);

            if (User::where('email', $userData['email'])->exists()) {
                continue;
            }

            $user = User::create(array_merge($userData, [
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
                'is_active' => $userData['is_active'] ?? true,
            ]));

            $role = Role::where('slug', $roleSlug)->first();
            if ($role) {
                $user->roles()->attach($role);
            }
        }
    }
}