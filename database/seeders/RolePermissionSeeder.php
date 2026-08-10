<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Models\Role;
use Modules\Core\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Permissions
        $permissions = [
            // Users
            ['name' => 'مدیریت کاربران', 'slug' => 'manage_users', 'group' => 'users'],
            ['name' => 'مشاهده کاربران', 'slug' => 'view_users', 'group' => 'users'],

            // Projects/Portfolio
            ['name' => 'مدیریت پروژه‌ها', 'slug' => 'manage_projects', 'group' => 'portfolio'],
            ['name' => 'مشاهده پروژه‌ها', 'slug' => 'view_projects', 'group' => 'portfolio'],

            // Orders
            ['name' => 'مدیریت سفارشات', 'slug' => 'manage_orders', 'group' => 'orders'],
            ['name' => 'مشاهده سفارشات', 'slug' => 'view_orders', 'group' => 'orders'],

            // Blog
            ['name' => 'مدیریت وبلاگ', 'slug' => 'manage_blog', 'group' => 'blog'],
            ['name' => 'مشاهده وبلاگ', 'slug' => 'view_blog', 'group' => 'blog'],

            // Tickets
            ['name' => 'مدیریت تیکت‌ها', 'slug' => 'manage_tickets', 'group' => 'tickets'],
            ['name' => 'مشاهده تیکت‌ها', 'slug' => 'view_tickets', 'group' => 'tickets'],

            // Payments
            ['name' => 'مدیریت پرداخت‌ها', 'slug' => 'manage_payments', 'group' => 'payments'],
            ['name' => 'مشاهده پرداخت‌ها', 'slug' => 'view_payments', 'group' => 'payments'],

            // Settings
            ['name' => 'مدیریت تنظیمات', 'slug' => 'manage_settings', 'group' => 'settings'],

            // AI
            ['name' => 'مدیریت هوش مصنوعی', 'slug' => 'manage_ai', 'group' => 'ai'],

            // Reports
            ['name' => 'مشاهده گزارش‌ها', 'slug' => 'view_reports', 'group' => 'analytics'],

            // Media
            ['name' => 'مدیریت رسانه', 'slug' => 'manage_media', 'group' => 'media'],
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }

        // Create Roles
        $superAdmin = Role::create([
            'name' => 'مدیر ارشد',
            'slug' => 'super-admin',
            'description' => 'دسترسی کامل به تمامی بخش‌های سیستم',
        ]);

        $admin = Role::create([
            'name' => 'مدیر',
            'slug' => 'admin',
            'description' => 'دسترسی به بیشتر بخش‌های سیستم',
        ]);

        $editor = Role::create([
            'name' => 'ویرایشگر',
            'slug' => 'editor',
            'description' => 'دسترسی به محتوا، پورتفولیو و وبلاگ',
        ]);

        $support = Role::create([
            'name' => 'پشتیبانی',
            'slug' => 'support',
            'description' => 'دسترسی به تیکت‌ها و سفارشات',
        ]);

        // Assign Permissions to Roles

        // Super Admin - All permissions
        $superAdmin->permissions()->attach(Permission::all());

        // Admin - Most permissions except settings
        $admin->permissions()->attach(
            Permission::whereIn('slug', [
                'manage_users',
                'view_users',
                'manage_projects',
                'view_projects',
                'manage_orders',
                'view_orders',
                'manage_blog',
                'view_blog',
                'manage_tickets',
                'view_tickets',
                'manage_payments',
                'view_payments',
                'view_reports',
                'manage_media',
            ])->get()
        );

        // Editor - Content focused
        $editor->permissions()->attach(
            Permission::whereIn('slug', [
                'manage_projects',
                'view_projects',
                'manage_blog',
                'view_blog',
                'manage_media',
            ])->get()
        );

        // Support - Customer focused
        $support->permissions()->attach(
            Permission::whereIn('slug', [
                'view_users',
                'manage_orders',
                'view_orders',
                'manage_tickets',
                'view_tickets',
                'view_payments',
            ])->get()
        );
    }
}