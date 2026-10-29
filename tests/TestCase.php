<?php

namespace Tests;

use App\Models\Admin;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Modules\Core\Models\Role;

abstract class TestCase extends BaseTestCase
{
    /**
     * شمارنده شماره موبایل یکتای تستی برای ادمین‌ها.
     */
    private int $adminPhoneSeq = 0;

    protected function setUp(): void
    {
        parent::setUp();

        // ایمیل‌ها در تست‌ها هرگز خارج از فرآیند تست ارسال نشوند
        // (MAIL_MAILER=array در phpunit.xml با .env مخدوش می‌شود)
        config(['mail.default' => 'array']);
    }

    /**
     * ساخت ادمین تستی در جدول admins (کادر فنی) با نقش داده‌شده.
     *
     * نیازمند اجرای RolePermissionSeeder در setUp تست.
     */
    protected function makeAdmin(
        string $email = 'admin@test.com',
        string $roleSlug = 'super-admin',
        array $attributes = []
    ): Admin {
        $admin = Admin::create(array_merge([
            'name' => $roleSlug,
            'email' => $email,
            'phone' => '0912345'.str_pad((string) ++$this->adminPhoneSeq, 4, '0', STR_PAD_LEFT),
            'password' => 'password',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
            'is_active' => true,
        ], $attributes));

        if ($roleSlug !== '') {
            $admin->roles()->attach(Role::where('slug', $roleSlug)->firstOrFail());
        }

        return $admin;
    }

    /**
     * ورود تستی با گارد admin (نه web).
     */
    protected function actingAsAdmin(Admin $admin): static
    {
        return $this->actingAs($admin, 'admin');
    }
}
