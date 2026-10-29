<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Modules\Blog\Database\Seeders\BlogDatabaseSeeder;
use Modules\Consultation\Database\Seeders\ConsultationContentSeeder;
use Modules\Orders\Database\Seeders\OrdersDatabaseSeeder;
use Modules\Notifications\Database\Seeders\NotificationsPermissionSeeder;
use Modules\Products\Database\Seeders\ProductsDatabaseSeeder;
use Modules\ProjectDev\Database\Seeders\ProjectDevDatabaseSeeder;
use Modules\Resume\Database\Seeders\ResumeDatabaseSeeder;
use Modules\Settings\Database\Seeders\SettingsDatabaseSeeder;
use Modules\Tickets\Database\Seeders\TicketsDatabaseSeeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed roles and permissions first
        $this->call([
            RolePermissionSeeder::class,
        ]);

        // Seed staff admins (جدول admins — جدا از کاربران)
        $this->call([
            AdminUserSeeder::class,
        ]);

        // Seed regular users (جدول users — فقط نقش «کاربر»)
        $this->call([
            RegularUserSeeder::class,
        ]);

        // Seed dashboard data
        $this->call([
            DashboardSeeder::class,
        ]);

        // Seed resume module (نمونه کار، مهارت‌ها، تحصیلات، تجربیات، زبان‌ها + مجوزها)
        $this->call([
            ResumeDatabaseSeeder::class,
        ]);

        // Seed blog module (مقالات، دسته‌ها، برچسب‌ها، نظرات + مجوزها)
        $this->call([
            BlogDatabaseSeeder::class,
        ]);

        // Seed orders module (سفارشات + پرداخت‌ها + مجوزها)
        $this->call([
            OrdersDatabaseSeeder::class,
        ]);

        // Seed tickets module (تیکت‌ها + پاسخ‌ها + مجوزها)
        $this->call([
            TicketsDatabaseSeeder::class,
        ]);

        // Seed projects module (کاتالوگ قیمت‌گذاری پروژه‌ها + مجوزها)
        $this->call([
            ProjectDevDatabaseSeeder::class,
        ]);

        // Seed products module (دسته‌بندی + محصولات + لایسنس نمونه + مجوزها)
        $this->call([
            ProductsDatabaseSeeder::class,
        ]);

        // Seed notifications module (مجوزهای view/manage_notifications — idempotent)
        $this->call([
            NotificationsPermissionSeeder::class,
        ]);

        // Seed consultation module (انواع مشاوره — دو‌زبانه)
        $this->call([
            ConsultationContentSeeder::class,
        ]);

        // Seed settings module (تنظیمات سایت/SMTP/درگاه + مجوزها)
        $this->call([
            SettingsDatabaseSeeder::class,
        ]);
    }
}
