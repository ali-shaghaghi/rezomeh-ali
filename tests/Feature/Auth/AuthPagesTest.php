<?php

namespace Tests\Feature\Auth;

use App\Models\Admin;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\Auth\Livewire\OtpVerification;
use Modules\Core\Models\LoginAttempt;
use Modules\Core\Models\Role;
use Modules\Core\Services\OtpService;
use Tests\TestCase;

class AuthPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        // جلوگیری از تماس واقعی با API مایل‌ترپ در تست‌ها (fallback به میل‌درایور array)
        config(['services.mailtrap.api_key' => null]);
    }

    private function createSuperAdmin(): Admin
    {
        // ادمین از جدول جدا (گارد admin)
        return $this->makeAdmin('auth-super@test.com', 'super-admin');
    }

    // ------------------------------------------------------------------
    // ۱) رندر صفحات Auth (UI یکسان ماژول Auth برای ادمین و کاربر عادی)
    // ------------------------------------------------------------------

    public function test_login_page_renders_for_guest(): void
    {
        $this->get(route('auth.login'))
            ->assertOk()
            ->assertSee('ورود به حساب کاربری');
    }

    public function test_register_page_renders_for_guest(): void
    {
        $this->get(route('auth.register'))
            ->assertOk()
            ->assertSee('ایجاد حساب کاربری');
    }

    public function test_password_reset_page_renders_for_guest(): void
    {
        $this->get(route('auth.password.request'))->assertOk();
    }

    /** سازگاری با روت‌های قدیمی ماژول Public (forgot-password) */
    public function test_legacy_forgot_password_route_still_works(): void
    {
        $this->get(route('auth.forgot-password'))->assertOk();
    }

    public function test_otp_page_renders_with_pending_session(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        $this->withSession(['otp_user_id' => $user->id])
            ->get(route('auth.otp.verify'))
            ->assertOk();
    }

    public function test_two_factor_page_renders_with_pending_session(): void
    {
        $user = User::factory()->create(['is_active' => true, 'phone' => '09121234567']);

        $this->withSession(['two_factor_user_id' => $user->id])
            ->get(route('auth.two-factor.verify'))
            ->assertOk();
    }

    public function test_change_password_page_renders_for_admin(): void
    {
        $this->actingAs($this->createSuperAdmin(), 'admin')
            ->get(route('auth.change-password'))
            ->assertOk();
    }

    // ------------------------------------------------------------------
    // ۲) ثبت‌نام + ریدایرکت نقش‌محور (کاربر عادی → پنل کاربر)
    // ------------------------------------------------------------------

    public function test_register_creates_user_with_user_role_and_redirects_to_user_dashboard(): void
    {
        $response = $this->post(route('auth.register'), [
            'name' => 'کاربر تست',
            'email' => 'new-user@test.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'کاربر تست',
            'email' => 'new-user@test.com',
            'is_active' => true,
        ]);

        $user = User::where('email', 'new-user@test.com')->first();

        $this->assertTrue($user->hasRole('user'), 'نقش user به کاربر جدید متصل نشد');
        $this->assertFalse(
            $user->hasAnyRole(['super-admin', 'admin', 'editor', 'support', 'consultant']),
            'کاربر ثبت‌نام‌شده نباید هیچ نقش کادر فنی بگیرد'
        );
        $this->assertAuthenticatedAs($user);

        // ریدایرکت نقش‌محور: کاربر عادی → داشبورد کاربر
        $response->assertRedirect(route('user.dashboard'));
    }

    public function test_register_validates_input(): void
    {
        $this->post(route('auth.register'), [
            'name' => '',
            'email' => 'not-an-email',
            'password' => '123',
            'password_confirmation' => '456',
        ])->assertSessionHasErrors(['name', 'email', 'password']);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_register_rejects_duplicate_email(): void
    {
        User::factory()->create(['email' => 'dup@test.com']);

        $this->post(route('auth.register'), [
            'name' => 'کاربر دوم',
            'email' => 'dup@test.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ])->assertSessionHasErrors('email');

        $this->assertDatabaseCount('users', 1);
    }

    // ------------------------------------------------------------------
    // ۳) لاگین POST — امنیت: Rate Limit + LoginAttempt + OTP
    // ------------------------------------------------------------------

    public function test_login_post_rejects_wrong_password_and_logs_attempt(): void
    {
        $user = User::factory()->create(['password' => 'correct-password', 'is_active' => true]);

        $this->post(route('auth.login'), [
            'credential' => $user->email,
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('credential');

        $this->assertGuest();
        $this->assertDatabaseHas('login_attempts', [
            'user_id' => $user->id,
            'is_successful' => false,
            'failure_reason' => 'invalid_password',
        ]);
    }

    public function test_login_post_rejects_unknown_credential(): void
    {
        $this->post(route('auth.login'), [
            'credential' => 'nobody@test.com',
            'password' => 'whatever123',
        ])->assertSessionHasErrors('credential');

        $this->assertDatabaseHas('login_attempts', [
            'email' => 'nobody@test.com',
            'is_successful' => false,
            'failure_reason' => 'user_not_found',
        ]);
    }

    public function test_login_post_valid_credentials_dispatch_otp_and_redirect_to_verify(): void
    {
        // OTP ورود اختیاری است — این جریان فقط وقتی فعال باشد که کاربر فعالش کرده باشد
        $user = User::factory()->create(['password' => 'correct-password', 'is_active' => true, 'login_otp_enabled' => true]);

        $this->post(route('auth.login'), [
            'credential' => $user->email,
            'password' => 'correct-password',
        ])->assertRedirect(route('auth.otp.verify'));

        $this->assertEquals($user->id, session('otp_user_id'));
        $this->assertDatabaseHas('login_attempts', [
            'user_id' => $user->id,
            'is_successful' => true,
        ]);
        // هنوز وارد نشده — تا تایید OTP
        $this->assertGuest();
    }

    public function test_login_post_is_rate_limited_after_max_failed_attempts(): void
    {
        for ($i = 0; $i < 5; $i++) {
            LoginAttempt::log([
                'email' => 'victim@test.com',
                'ip_address' => '127.0.0.1',
                'is_successful' => false,
                'failure_reason' => 'invalid_password',
            ]);
        }

        $this->post(route('auth.login'), [
            'credential' => 'victim@test.com',
            'password' => 'whatever123',
        ])->assertSessionHasErrors('credential');

        // درخواست رد شده باید قبل از ثبت تلاش جدید بلاک شود
        $this->assertDatabaseCount('login_attempts', 5);
    }

    // ------------------------------------------------------------------
    // ۴) تایید OTP — کاربران هرگز به پنل ادمین نمی‌رسند (تفکیک گاردها)
    // ------------------------------------------------------------------

    public function test_staff_role_on_users_table_grants_no_admin_access(): void
    {
        // حتی اگر نقش بالا به‌اشتباه به ردیف جدول users بچسبد،
        // پنل ادمین فقط با گارد admin (جدول admins) باز می‌شود
        $user = User::factory()->create(['is_active' => true]);
        $user->roles()->attach(Role::where('slug', 'super-admin')->firstOrFail());

        $this->actingAs($user)->get('/admin/dashboard')->assertNotFound();
        $this->actingAs($user)->get('/admin/users')->assertNotFound();

        // جریان OTP کاربران هم فقط به پنل کاربر می‌رود
        $code = app(OtpService::class)->generate($user, 'login');
        $this->withSession(['otp_user_id' => $user->id]);

        Livewire::test(OtpVerification::class)
            ->set('otp', $code)
            ->call('verify')
            ->assertRedirect(route('user.dashboard'));

        $this->assertGuest('admin');
    }

    public function test_otp_verification_redirects_normal_user_to_user_dashboard(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->roles()->attach(Role::where('slug', 'user')->firstOrFail());

        $code = app(OtpService::class)->generate($user, 'login');

        $this->withSession(['otp_user_id' => $user->id]);

        Livewire::test(OtpVerification::class)
            ->set('otp', $code)
            ->call('verify')
            ->assertRedirect(route('user.dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_otp_verification_rejects_wrong_code(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        app(OtpService::class)->generate($user, 'login');

        $this->withSession(['otp_user_id' => $user->id]);

        Livewire::test(OtpVerification::class)
            ->set('otp', '000000')
            ->call('verify');

        $this->assertGuest();
    }

    // ------------------------------------------------------------------
    // نمایش کد OTP در حالت توسعه (بازگردانی نمایشِ کد در پایین صفحه تایید)
    // ------------------------------------------------------------------

    public function test_otp_page_shows_dev_code_when_dev_mode_enabled(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $code = app(OtpService::class)->generate($user, 'login');

        // dev_mode_enabled فقط در محیط local درست است → محیط را local می‌کنیم
        $this->app['env'] = 'local';
        \Modules\Settings\Models\Setting::set('dev_mode', '1', 'site');

        $this->withSession(['otp_user_id' => $user->id, 'otp_dev_code' => $code]);

        Livewire::test(OtpVerification::class)
            ->assertSee('حالت توسعه')
            ->assertSee($code);
    }

    public function test_otp_page_hides_dev_code_outside_dev_mode(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $code = app(OtpService::class)->generate($user, 'login');

        // محیط تست (غیر local) → کد توسعه نباید رندر شود (رفتار پروداکشن)
        $this->withSession(['otp_user_id' => $user->id, 'otp_dev_code' => $code]);

        Livewire::test(OtpVerification::class)
            ->assertDontSee('حالت توسعه')
            ->assertDontSee($code);
    }
}
