<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Tests\TestCase;

class SocialiteAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        // کلاینت‌های OAuth پیکربندی‌شده فرض می‌شوند
        config([
            'services.google.client_id' => 'test-google-client-id',
            'services.google.client_secret' => 'test-google-secret',
            'services.google.redirect' => 'http://localhost:8000/auth/google/callback',
            'services.github.client_id' => 'test-github-client-id',
            'services.github.client_secret' => 'test-github-secret',
            'services.github.redirect' => 'http://localhost:8000/auth/github/callback',
        ]);
    }

    /**
     * ساخت یک Mock از کاربر اجتماعی (Socialite Two\User).
     */
    private function fakeSocialUser(array $overrides = []): object
    {
        $data = array_merge([
            'id' => '987654',
            'email' => 'social-user@test.com',
            'name' => 'کاربر اجتماعی',
            'nickname' => 'social',
            'avatar' => null,
        ], $overrides);

        $mock = Mockery::mock('SocialUserMock');
        $mock->shouldReceive('getId')->andReturn($data['id']);
        $mock->shouldReceive('getEmail')->andReturn($data['email']);
        $mock->shouldReceive('getName')->andReturn($data['name']);
        $mock->shouldReceive('getNickname')->andReturn($data['nickname']);
        $mock->shouldReceive('getAvatar')->andReturn($data['avatar']);

        return $mock;
    }

    /**
     * Mock کردن درایور Socialite با زنجیره redirectUrl()->user().
     */
    private function mockDriver(string $provider, object $socialUser): void
    {
        $driver = Mockery::mock('SocialiteDriverMock');
        $driver->shouldReceive('redirectUrl')->andReturnSelf();
        $driver->shouldReceive('user')->andReturn($socialUser);

        Socialite::shouldReceive('driver')->with($provider)->andReturn($driver);
    }

    // ------------------------------------------------------------------
    // ۱) شروع جریان OAuth (ریدایرکت به سرویس‌دهنده)
    // ------------------------------------------------------------------

    public function test_google_redirect_starts_oauth_flow(): void
    {
        $response = $this->get(route('auth.socialite.redirect', 'google'));

        $response->assertRedirect();
        $this->assertStringStartsWith('https://accounts.google.com', (string) $response->getTargetUrl());
    }

    public function test_redirect_shows_clear_message_when_provider_not_configured(): void
    {
        config([
            'services.github.client_id' => null,
            'services.github.client_secret' => null,
        ]);

        $this->get(route('auth.socialite.redirect', 'github'))
            ->assertRedirect(route('auth.login'))
            ->assertSessionHasErrors('credential');
    }

    // ------------------------------------------------------------------
    // ۲) Callback — ایجاد کاربر جدید + ریدایرکت نقش‌محور
    // ------------------------------------------------------------------

    public function test_google_callback_creates_user_and_redirects_to_user_dashboard(): void
    {
        $this->mockDriver('google', $this->fakeSocialUser());

        $this->get(route('auth.socialite.callback', 'google'))
            ->assertRedirect(route('user.dashboard'));

        $user = User::where('email', 'social-user@test.com')->firstOrFail();

        $this->assertDatabaseHas('users', [
            'email' => 'social-user@test.com',
            'social_provider' => 'google',
            'social_id' => '987654',
        ]);
        $this->assertTrue($user->hasRole('user'), 'نقش پیش‌فرض user به کاربر اجتماعی متصل نشد');
        $this->assertAuthenticatedAs($user);
    }

    public function test_callback_links_existing_account_by_email(): void
    {
        $existing = User::factory()->create(['email' => 'linked@test.com']);

        $this->mockDriver('github', $this->fakeSocialUser([
            'id' => '111222',
            'email' => 'linked@test.com',
        ]));

        $this->get(route('auth.socialite.callback', 'github'))
            ->assertRedirect(route('user.dashboard'));

        // حساب موجود باید استفاده شود — نه کاربر جدید
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseHas('users', [
            'email' => 'linked@test.com',
            'social_provider' => 'github',
            'social_id' => '111222',
        ]);
        $this->assertAuthenticatedAs($existing->fresh());
    }

    public function test_callback_never_opens_admin_panel(): void
    {
        // ورود اجتماعی فقط برای کاربران است — حتی ردیفی با نقش بالا
        // در جدول users هرگز نباید به پنل ادمین برسد (ورود ادمین فقط از /admin/login)
        $user = User::factory()->create(['email' => 'admin-social@test.com', 'is_active' => true]);
        $user->roles()->attach(\Modules\Core\Models\Role::where('slug', 'super-admin')->firstOrFail());

        $this->mockDriver('google', $this->fakeSocialUser(['email' => 'admin-social@test.com']));

        $this->get(route('auth.socialite.callback', 'google'))
            ->assertRedirect(route('user.dashboard'));

        $this->assertAuthenticatedAs($user->fresh());
        $this->assertGuest('admin');
    }

    public function test_callback_rejects_inactive_user(): void
    {
        User::factory()->create(['email' => 'inactive@test.com', 'is_active' => false]);

        $this->mockDriver('google', $this->fakeSocialUser(['email' => 'inactive@test.com']));

        $this->get(route('auth.socialite.callback', 'google'))
            ->assertRedirect(route('auth.login'))
            ->assertSessionHasErrors('credential');

        $this->assertGuest();
    }

    public function test_callback_failure_returns_to_login_with_error(): void
    {
        Socialite::shouldReceive('driver')->with('google')->andThrow(new \Exception('state mismatch'));

        $this->get(route('auth.socialite.callback', 'google'))
            ->assertRedirect(route('auth.login'))
            ->assertSessionHasErrors('credential');

        $this->assertGuest();
    }
}
