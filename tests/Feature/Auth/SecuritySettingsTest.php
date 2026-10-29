<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use Modules\Auth\Livewire\OtpVerification;
use Modules\Auth\Livewire\TwoFactorForm;
use Modules\Core\Models\Role;
use Modules\Core\Services\OtpService;
use Modules\Settings\Livewire\Admin\SecurityToggles;
use Illuminate\Auth\Notifications\VerifyEmail;
use Tests\TestCase;

class SecuritySettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        config(['services.mailtrap.api_key' => null]);
        Cache::flush();
    }

    private function createSuperAdmin(): \App\Models\Admin
    {
        // ادمین از جدول جدا (گارد admin)
        return $this->makeAdmin('security-super@test.com', 'super-admin');
    }

    // ------------------------------------------------------------------
    // ۱) پیش‌فرض: هر دو قابلیت غیرفعال‌اند
    // ------------------------------------------------------------------

    public function test_security_features_are_disabled_by_default(): void
    {
        $user = User::factory()->create();

        $this->assertFalse($user->two_factor_enabled);
        $this->assertFalse($user->email_verification_enabled);
    }

    public function test_admin_settings_security_tab_renders(): void
    {
        $this->actingAs($this->createSuperAdmin(), 'admin')
            ->get(route('admin.settings.group', 'security'))
            ->assertOk()
            ->assertSee('ورود دو عاملی و تایید ایمیل');
    }

    public function test_user_panel_security_page_renders(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->roles()->attach(Role::where('slug', 'user')->firstOrFail());

        $this->actingAs($user)
            ->get(route('user.dashboard', [], false).'?tab=security')
            ->assertOk()
            ->assertSee('تنظیمات امنیتی');
    }

    // ------------------------------------------------------------------
    // ۲) ورود دو عاملی — فعال/غیرفعال‌سازی از تنظیمات امنیتی
    // ------------------------------------------------------------------

    public function test_admin_can_enable_and_disable_2fa_from_settings(): void
    {
        $admin = $this->createSuperAdmin();

        Livewire::actingAs($admin, 'admin')
            ->test(SecurityToggles::class)
            ->assertSet('twoFactorEnabled', false)
            ->call('enableTwoFactor')
            ->assertSet('twoFactorEnabled', true);

        $this->assertTrue($admin->fresh()->two_factor_enabled);
        $this->assertNotNull($admin->fresh()->two_factor_recovery_codes);

        // غیرفعال‌سازی با رمز اشتباه — باید رد شود
        Livewire::actingAs($admin, 'admin')
            ->test(SecurityToggles::class)
            ->set('disablePassword', 'wrong-password')
            ->call('disableTwoFactor')
            ->assertSet('twoFactorEnabled', true);

        $this->assertTrue($admin->fresh()->two_factor_enabled);

        // غیرفعال‌سازی با رمز صحیح
        Livewire::actingAs($admin, 'admin')
            ->test(SecurityToggles::class)
            ->set('disablePassword', 'password')
            ->call('disableTwoFactor')
            ->assertSet('twoFactorEnabled', false);

        $this->assertFalse($admin->fresh()->two_factor_enabled);
        $this->assertNull($admin->fresh()->two_factor_secret);
    }

    public function test_2fa_enabled_user_must_verify_second_factor_on_login(): void
    {
        $user = User::factory()->create(['password' => 'correct-password', 'is_active' => true, 'login_otp_enabled' => true]);
        $user->update(['two_factor_enabled' => true]);

        // مرحله ۱: ورود + OTP ورود
        $this->post(route('auth.login'), [
            'credential' => $user->email,
            'password' => 'correct-password',
        ])->assertRedirect(route('auth.otp.verify'));

        $loginCode = app(OtpService::class)->generate($user, 'login');

        $this->withSession(['otp_user_id' => $user->id]);
        Livewire::test(OtpVerification::class)
            ->set('otp', $loginCode)
            ->call('verify')
            ->assertRedirect(route('auth.two-factor.verify'));

        // هنوز وارد نشده — منتظر کد دومرحله‌ای
        $this->assertGuest();

        // مرحله ۲: تایید کد دومرحله‌ای
        $twoFactorCode = app(OtpService::class)->generate($user, 'two_factor');

        $this->withSession(['two_factor_user_id' => $user->id, 'two_factor_remember' => false]);
        Livewire::test(TwoFactorForm::class)
            ->set('otpDigits', str_split($twoFactorCode));

        $this->assertAuthenticatedAs($user);
    }

    // ------------------------------------------------------------------
    // ۳) تایید ایمیل — فعال‌سازی + جریان ورود + لینک تایید
    // ------------------------------------------------------------------

    public function test_email_verification_toggle_sends_verification_email(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create(['is_active' => true]);

        Livewire::actingAs($user)
            ->test(SecurityToggles::class)
            ->assertSet('emailVerificationEnabled', false)
            ->call('setEmailVerification', true)
            ->assertSet('emailVerificationEnabled', true);

        $this->assertTrue($user->fresh()->email_verification_enabled);

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_email_verification_toggle_can_be_disabled(): void
    {
        $user = User::factory()->create(['email_verification_enabled' => true]);

        Livewire::actingAs($user)
            ->test(SecurityToggles::class)
            ->call('setEmailVerification', false)
            ->assertSet('emailVerificationEnabled', false);

        $this->assertFalse($user->fresh()->email_verification_enabled);
    }

    public function test_login_redirects_to_verification_notice_when_enabled_and_unverified(): void
    {
        $user = User::factory()->unverified()->create(['password' => 'correct-password', 'is_active' => true]);
        $user->update(['email_verification_enabled' => true]);
        $user->roles()->attach(Role::where('slug', 'user')->firstOrFail());

        $this->post(route('auth.login'), [
            'credential' => $user->email,
            'password' => 'correct-password',
        ]);

        $code = app(OtpService::class)->generate($user, 'login');

        $this->withSession(['otp_user_id' => $user->id]);
        Livewire::test(OtpVerification::class)
            ->set('otp', $code)
            ->call('verify')
            ->assertRedirect(route('verification.notice'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_goes_straight_to_dashboard_when_email_verification_disabled(): void
    {
        $user = User::factory()->create(['password' => 'correct-password', 'is_active' => true]);
        $user->roles()->attach(Role::where('slug', 'user')->firstOrFail());

        $this->post(route('auth.login'), [
            'credential' => $user->email,
            'password' => 'correct-password',
        ]);

        $code = app(OtpService::class)->generate($user, 'login');

        $this->withSession(['otp_user_id' => $user->id]);
        Livewire::test(OtpVerification::class)
            ->set('otp', $code)
            ->call('verify')
            ->assertRedirect(route('user.dashboard'));
    }

    public function test_signed_verification_link_marks_email_as_verified(): void
    {
        $user = User::factory()->unverified()->create(['email' => 'verify@test.com', 'is_active' => true]);
        $user->update(['email_verification_enabled' => true]);

        $this->assertNull($user->email_verified_at);

        $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
            'id' => $user->id,
            'hash' => sha1($user->getEmailForVerification()),
        ]);

        $this->actingAs($user)
            ->get($url)
            ->assertRedirect(route('user.dashboard'))
            ->assertSessionHas('success');

        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_signed_verification_link_rejects_tampered_hash(): void
    {
        $user = User::factory()->unverified()->create(['email' => 'verify2@test.com', 'is_active' => true]);

        $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
            'id' => $user->id,
            'hash' => 'wrong-hash',
        ]);

        $this->get($url)->assertNotFound();

        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_resend_verification_is_throttled(): void
    {
        Notification::fake();

        $user = User::factory()->create(['email_verification_enabled' => true]);
        $user->sendEmailVerificationNotification();

        Notification::assertSentTimes(VerifyEmail::class, 1);
    }
}
