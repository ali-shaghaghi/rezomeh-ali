<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Models\Permission;
use Modules\Core\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // نکته: افزایشی و idempotent — مجوزهای موجود دست نخورده، فقط جدیدها اضافه می‌شوند.
        // (مایگریشن‌های ماژول‌ها ممکن است قبل از این seeder مجوزهایی درج کرده باشند)
        $permissions = [
            // Users
            ['name' => 'مدیریت کاربران', 'slug' => 'manage_users', 'group' => 'users'],
            ['name' => 'مشاهده کاربران', 'slug' => 'view_users', 'group' => 'users'],

            // Admins (کادر فنی — جدول admins)
            ['name' => 'مدیریت ادمین‌ها', 'slug' => 'manage_admins', 'group' => 'users'],
            ['name' => 'مشاهده ادمین‌ها', 'slug' => 'view_admins', 'group' => 'users'],

            // Resume Module (نمونه کار + مهارت‌ها + تحصیلات + تجربیات + زبان‌ها)
            ['name' => 'مدیریت نمونه کارها', 'slug' => 'manage_projects', 'group' => 'resume'],
            ['name' => 'مشاهده نمونه کارها', 'slug' => 'view_projects', 'group' => 'resume'],

            // Skills
            ['name' => 'مدیریت مهارت‌ها', 'slug' => 'manage_skills', 'group' => 'resume'],
            ['name' => 'مشاهده مهارت‌ها', 'slug' => 'view_skills', 'group' => 'resume'],

            // Education
            ['name' => 'مدیریت تحصیلات', 'slug' => 'manage_education', 'group' => 'resume'],
            ['name' => 'مشاهده تحصیلات', 'slug' => 'view_education', 'group' => 'resume'],

            // Experience
            ['name' => 'مدیریت تجربیات', 'slug' => 'manage_experience', 'group' => 'resume'],
            ['name' => 'مشاهده تجربیات', 'slug' => 'view_experience', 'group' => 'resume'],

            // Languages
            ['name' => 'مدیریت زبان‌ها', 'slug' => 'manage_languages', 'group' => 'resume'],
            ['name' => 'مشاهده زبان‌ها', 'slug' => 'view_languages', 'group' => 'resume'],

            // Resume Management
            ['name' => 'مدیریت رزومه', 'slug' => 'manage_resume', 'group' => 'resume'],
            ['name' => 'مشاهده رزومه', 'slug' => 'view_resume', 'group' => 'resume'],

            // Orders
            ['name' => 'مدیریت سفارشات', 'slug' => 'manage_orders', 'group' => 'orders'],
            ['name' => 'مشاهده سفارشات', 'slug' => 'view_orders', 'group' => 'orders'],

            // Blog
            ['name' => 'مدیریت وبلاگ', 'slug' => 'manage_blog', 'group' => 'blog'],
            ['name' => 'مشاهده وبلاگ', 'slug' => 'view_blog', 'group' => 'blog'],
            ['name' => 'مدیریت نظرات', 'slug' => 'manage_comments', 'group' => 'blog'],
            ['name' => 'مشاهده نظرات', 'slug' => 'view_comments', 'group' => 'blog'],

            // Tickets
            ['name' => 'مدیریت تیکت‌ها', 'slug' => 'manage_tickets', 'group' => 'tickets'],
            ['name' => 'مشاهده تیکت‌ها', 'slug' => 'view_tickets', 'group' => 'tickets'],

            // SMS
            ['name' => 'مدیریت پیامک', 'slug' => 'manage_sms', 'group' => 'sms'],

            // Consultation
            ['name' => 'مدیریت مشاوره‌ها', 'slug' => 'manage_consultations', 'group' => 'consultation'],
            ['name' => 'مشاهده مشاوره‌ها', 'slug' => 'view_consultations', 'group' => 'consultation'],
            ['name' => 'مدیریت مشاوران', 'slug' => 'manage_consultants', 'group' => 'consultation'],
            ['name' => 'مدیریت زمان‌بندی', 'slug' => 'manage_schedules', 'group' => 'consultation'],

            // Payments
            ['name' => 'مدیریت پرداخت‌ها', 'slug' => 'manage_payments', 'group' => 'payments'],
            ['name' => 'مشاهده پرداخت‌ها', 'slug' => 'view_payments', 'group' => 'payments'],

            // Settings
            ['name' => 'مدیریت تنظیمات', 'slug' => 'manage_settings', 'group' => 'settings'],

            // AI

            // Reports
            ['name' => 'مشاهده گزارش‌ها', 'slug' => 'view_reports', 'group' => 'analytics'],

            // Media
            ['name' => 'مدیریت رسانه', 'slug' => 'manage_media', 'group' => 'media'],

            // Public Site
            ['name' => 'مشاهده سایت', 'slug' => 'view_site', 'group' => 'public'],
            ['name' => 'مشاهده مقالات سایت', 'slug' => 'view_site_blog', 'group' => 'public'],
            ['name' => 'مشاهده پروژه‌های سایت', 'slug' => 'view_site_portfolio', 'group' => 'public'],
            ['name' => 'ارسال سفارش', 'slug' => 'submit_order', 'group' => 'public'],
            ['name' => 'ارسال تیکت', 'slug' => 'submit_ticket', 'group' => 'public'],
            ['name' => 'مشاهده سفارشات من', 'slug' => 'view_my_orders', 'group' => 'public'],
            ['name' => 'مشاهده تیکت‌های من', 'slug' => 'view_my_tickets', 'group' => 'public'],
        ];

        // ثبت افزایشی مجوزها — موجودها دست نخورده، فقط جدیدها اضافه می‌شوند
        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['slug' => $permission['slug']],
                ['name' => $permission['name'], 'group' => $permission['group']]
            );
        }

        // Create Roles
        $superAdmin = Role::firstOrCreate([
            'name' => 'مدیر ارشد',
            'slug' => 'super-admin',
            'description' => 'دسترسی کامل به تمامی بخش‌های سیستم',
        ]);

        $admin = Role::firstOrCreate([
            'name' => 'مدیر',
            'slug' => 'admin',
            'description' => 'دسترسی به بیشتر بخش‌های سیستم',
        ]);

        $editor = Role::firstOrCreate([
            'name' => 'ویرایشگر',
            'slug' => 'editor',
            'description' => 'دسترسی به محتوا، پورتفولیو و وبلاگ',
        ]);

        $support = Role::firstOrCreate([
            'name' => 'پشتیبانی',
            'slug' => 'support',
            'description' => 'دسترسی به تیکت‌ها و سفارشات',
        ]);

        $consultant = Role::firstOrCreate([
            'name' => 'مشاور',
            'slug' => 'consultant',
            'description' => 'دسترسی به مشاوره‌ها و زمان‌بندی (کادر فنی)',
        ]);

        $user = Role::firstOrCreate([
            'name' => 'کاربر',
            'slug' => 'user',
            'description' => 'کاربر عادی سایت - مشاهده محتوا و ارسال سفارش',
        ]);

        // Assign Permissions to Roles

        // Super Admin - All permissions
        $superAdmin->permissions()->syncWithoutDetaching(Permission::all());

        // Admin - Most permissions except settings
        $admin->permissions()->syncWithoutDetaching(
            Permission::whereIn('slug', [
                'manage_users',
                'view_users',
                'manage_admins',
                'view_admins',
                'manage_consultations',
                'view_consultations',
                'manage_schedules',
                'manage_projects',
                'view_projects',
                'manage_skills',
                'view_skills',
                'manage_education',
                'view_education',
                'manage_experience',
                'view_experience',
                'manage_languages',
                'view_languages',
                'manage_resume',
                'view_resume',
                'manage_orders',
                'view_orders',
                'manage_blog',
                'view_blog',
                'manage_comments',
                'view_comments',
                'manage_tickets',
                'view_tickets',
                'manage_payments',
                'view_payments',
                'view_reports',
                'manage_media',
            ])->get()
        );

        // Editor - Content focused
        $editor->permissions()->syncWithoutDetaching(
            Permission::whereIn('slug', [
                'manage_projects',
                'view_projects',
                'manage_skills',
                'view_skills',
                'manage_education',
                'view_education',
                'manage_experience',
                'view_experience',
                'manage_languages',
                'view_languages',
                'manage_resume',
                'view_resume',
                'manage_blog',
                'view_blog',
                'manage_comments',
                'view_comments',
                'manage_media',
            ])->get()
        );

        // Support - Customer focused
        $support->permissions()->syncWithoutDetaching(
            Permission::whereIn('slug', [
                'view_users',
                'manage_orders',
                'view_orders',
                'manage_tickets',
                'view_tickets',
                'view_payments',
            ])->get()
        );

        // Consultant - Consultation focused (کادر فنی)
        $consultant->permissions()->syncWithoutDetaching(
            Permission::whereIn('slug', [
                'manage_consultations',
                'view_consultations',
                'manage_schedules',
                'view_reports',
            ])->get()
        );

        // User - Public site access
        $user->permissions()->syncWithoutDetaching(
            Permission::whereIn('slug', [
                'view_site',
                'view_site_blog',
                'view_site_portfolio',
                'submit_order',
                'submit_ticket',
                'view_my_orders',
                'view_my_tickets',
            ])->get()
        );
    }
}
