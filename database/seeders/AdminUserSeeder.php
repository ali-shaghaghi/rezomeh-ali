<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Modules\Core\Models\Role;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
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
    }
}