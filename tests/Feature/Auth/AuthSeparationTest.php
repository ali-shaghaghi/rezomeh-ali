<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Auth\Services\AdminLoginToken;
use Modules\SMS\Database\Seeders\SmsTemplateSeeder;
use Tests\TestCase;

/**
 * تست‌های تفکیک احراز هویت (جدول users در برابر admins):
 *
 *  - ورود کاربر با موبایل → کد پیامکی اجباری
 *  - ورود ادمین فقط از /admin/login با موبایل + کد پیامکی
 *  - هیچ راهی برای ارتقای سطح از جدول users وجود ندارد
 */
class AuthSeparationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        $this->seed(SmsTemplateSeeder::class);

        config(['services.mailtrap.api_key' => null]);
    }

    private function makePhoneUser(array $attributes = []): User
    {
        $user = User::create(array_merge([
            'name' => 'کاربر موبایل',
            'email' => 'phone-user@test.com',
            'phone' => '09125550001',
            'password' => 'password',
            'is_active' => true,
        ], $attributes));

        $user->roles()->attach(\Modules\Core\Models\Role::where('slug', 'user')->firstOrFail());

        return $user;
    }

    // ===== ورود کاربر: موبایل + کد پیامکی =====

    public function test_phone_login_requires_sms_otp_before_dashboard(): void
    {
        $user = $this->makePhoneUser();

        $this->post(route('auth.login'), [
            'credential' => '09125550001',
            'password' => 'password',
        ])->assertRedirect(route('auth.otp.verify'));

        // هنوز وارد نشده — باید کد پیامکی را وارد کند
        $this->assertGuest();
        $this->assertEquals($user->id, session('otp_user_id'));
        $this->assertSame('phone', session('otp_method'));

        // پیامک واقعاً ارسال (درایور log در محیط تست) و لاگ شده است
        $this->assertDatabaseHas('sms_logs', [
            'phone' => '09125550001',
            'status' => 'sent',
        ]);
    }

    public function test_email_login_without_user_optin_goes_direct_to_user_dashboard(): void
    {
        $user = $this->makePhoneUser();

        $this->post(route('auth.login'), [
            'credential' => 'phone-user@test.com',
            'password' => 'password',
        ])->assertRedirect(route('user.dashboard'));

        $this->assertAuthenticatedAs($user);
        // ورود ایمیلی بدون فعال‌سازی ۲FA توسط خود کاربر → بدون OTP
        $this->assertNull(session('otp_user_id'));
    }

    // ===== ورود ادمین: مسیر جدا + کد پیامکی اجباری =====

    public function test_admin_login_sends_sms_code_and_keeps_guest_until_verify(): void
    {
        $admin = $this->makeAdmin('sep-admin@test.com', 'super-admin');
        // روت ورود ادمین توکن‌دار است (/admin/login/{token})
        $token = AdminLoginToken::global();

        $this->post(route('admin.post.login', $token), [
            'phone' => $admin->phone,
            'password' => 'password',
        ])->assertRedirect(route('admin.login.verify', $token));

        $this->assertGuest('admin');
        $this->assertNotNull(session('admin_login_id'));

        $this->assertDatabaseHas('sms_logs', [
            'phone' => $admin->phone,
            'status' => 'sent',
        ]);

        // صفحه تایید: شمارش معکوس زندهٔ ارسال مجدد (قبلاً متن ثابت بود و نمی‌شمرد)
        $this->get(route('admin.login.verify', $token))
            ->assertOk()
            ->assertSee('ارسال مجدد کد تا')
            ->assertSee('x-text="left"', false);
    }

    public function test_admin_login_completes_with_sms_code_and_uses_admin_guard(): void
    {
        $admin = $this->makeAdmin('sep-admin2@test.com', 'super-admin');
        $token = AdminLoginToken::global();

        $this->post(route('admin.post.login', $token), [
            'phone' => $admin->phone,
            'password' => 'password',
        ])->assertRedirect(route('admin.login.verify', $token));

        // کد OTP از متن پیامک ارسال‌شده (لاگ درایور log) خوانده می‌شود —
        // بدون دستکاری env (که CSRF را فعال می‌کند و درخواست‌ها را 419 می‌کند)
        $sms = \Modules\SMS\Models\SmsLog::where('phone', $admin->phone)->latest('id')->first();
        $this->assertNotNull($sms, 'پیامک کد تایید باید ارسال شده باشد');
        $this->assertMatchesRegularExpression('/\b\d{6}\b/', $sms->message);

        preg_match('/\b(\d{6})\b/', $sms->message, $matches);
        $code = $matches[1];

        $this->post(route('admin.login.verify.post', $token), ['code' => $code])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin, 'admin');
        // هرگز وارد گارد کاربران (web) نشده است
        $this->assertGuest();
    }

    public function test_admin_login_rejects_wrong_password_without_sending_sms(): void
    {
        $admin = $this->makeAdmin('sep-admin3@test.com', 'super-admin');
        $token = AdminLoginToken::global();

        $this->post(route('admin.post.login', $token), [
            'phone' => $admin->phone,
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('phone');

        $this->assertGuest('admin');
        $this->assertDatabaseMissing('sms_logs', ['phone' => $admin->phone]);
        $this->assertDatabaseHas('login_attempts', [
            'phone' => $admin->phone,
            'is_successful' => false,
            'failure_reason' => 'invalid_password',
        ]);
    }

    public function test_regular_user_credentials_fail_at_admin_login_endpoint(): void
    {
        $user = $this->makePhoneUser();
        $token = AdminLoginToken::global();

        $this->post(route('admin.post.login', $token), [
            'phone' => $user->phone,
            'password' => 'password',
        ])->assertSessionHasErrors('phone');

        // پیام عمومی — بدون تایید وجود/نوجود حساب و بدون هیچ نشستی
        $this->assertGuest();
        $this->assertGuest('admin');
        $this->assertDatabaseHas('login_attempts', [
            'phone' => $user->phone,
            'failure_reason' => 'admin_not_found',
        ]);
    }

    public function test_admin_credentials_fail_at_user_login_endpoint(): void
    {
        $admin = $this->makeAdmin('sep-admin4@test.com', 'super-admin');

        $this->post(route('auth.login'), [
            'credential' => $admin->phone,
            'password' => 'password',
        ])->assertSessionHasErrors('credential');

        // ادمین‌ها از جدول users خوانده نمی‌شوند — از این مسیر وارد نمی‌شوند
        $this->assertGuest();
        $this->assertGuest('admin');
    }

    // ===== جلوگیری از ارتقا سطح در ثبت‌نام =====

    public function test_register_ignores_role_and_privilege_input(): void
    {
        $this->post(route('auth.register'), [
            'name' => 'Mallory',
            'email' => 'mallory@test.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            // تلاش برای ارتقا — هیچ‌کدام پذیرفته نمی‌شوند
            'role' => 'super-admin',
            'is_admin' => true,
            'is_active' => false,
        ]);

        $user = User::where('email', 'mallory@test.com')->firstOrFail();

        $this->assertSame(['user'], $user->roles->pluck('slug')->all());
        $this->assertTrue($user->is_active);
        $this->assertGuest('admin');
    }

    public function test_users_table_has_no_staff_role_rows_after_seed(): void
    {
        // سید کامل: ادمین‌ها در جدول admins، کاربران عادی در جدول users
        $this->seed(\Database\Seeders\AdminUserSeeder::class);
        $this->seed(\Database\Seeders\RegularUserSeeder::class);

        // بعد از seed هیچ کاربری در جدول users نقش کادر فنی ندارد
        $staffRoles = \Modules\Core\Models\Role::where('slug', '!=', 'user')->pluck('id');

        $escalated = User::whereHas('roles', function ($query) use ($staffRoles) {
            $query->whereIn('roles.id', $staffRoles);
        })->count();

        $this->assertSame(0, $escalated);

        // ادمین‌ها همگی در جدول admins هستند و کاربران عادی فقط نقش user دارند
        $this->assertGreaterThan(0, \App\Models\Admin::count());
        $this->assertGreaterThan(0, User::count());
    }
}
