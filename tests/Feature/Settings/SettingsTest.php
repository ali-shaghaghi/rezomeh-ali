<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\Core\Models\Role;
use Modules\Settings\Database\Seeders\SettingsDatabaseSeeder;
use Modules\Settings\Livewire\Admin\SettingsForm;
use Modules\Settings\Livewire\Admin\SettingsManager;
use Modules\Settings\Models\Setting;
use Modules\Settings\Services\SettingsService;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    protected \App\Models\Admin $superAdmin;

    protected User $plainUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        $this->seed(SettingsDatabaseSeeder::class);

        $superRole = Role::where('slug', 'super-admin')->firstOrFail();
        $userRole = Role::where('slug', 'user')->firstOrFail();

        $this->superAdmin = $this->makeAdmin('super@test.com', 'super-admin');

        $this->plainUser = User::create(['name' => 'User', 'email' => 'user@test.com', 'password' => 'password', 'is_active' => true]);
        $this->plainUser->roles()->attach($userRole);
    }

    // ===== تست مدل Setting =====

    public function test_setting_get_and_set(): void
    {
        Setting::set('test_key', 'test_value', 'site');
        $this->assertEquals('test_value', Setting::get('test_key'));

        // پیش‌فرض
        $this->assertNull(Setting::get('nonexistent'));
        $this->assertEquals('fallback', Setting::get('nonexistent', 'fallback'));
    }

    public function test_setting_set_updates_existing(): void
    {
        Setting::set('test_key', 'first', 'site');
        Setting::set('test_key', 'second', 'site');

        $this->assertEquals('second', Setting::get('test_key'));
        $this->assertEquals(1, Setting::where('key', 'test_key')->count());
    }

    public function test_setting_group_method(): void
    {
        Setting::set('g1', 'v1', 'smtp');
        Setting::set('g2', 'v2', 'smtp');
        Setting::set('g3', 'v3', 'site');

        $group = Setting::group('smtp');

        $this->assertEquals('v1', $group['g1']);
        $this->assertEquals('v2', $group['g2']);
        $this->assertArrayNotHasKey('g3', $group);
    }

    public function test_setting_get_bool(): void
    {
        Setting::set('flag_on', '1', 'site');
        Setting::set('flag_off', '0', 'site');

        $this->assertTrue(Setting::getBool('flag_on'));
        $this->assertFalse(Setting::getBool('flag_off'));
        $this->assertTrue(Setting::getBool('nonexistent', true));
    }

    // ===== تست Service =====

    public function test_settings_service_schema_has_groups(): void
    {
        $schema = app(SettingsService::class)->schema();

        $this->assertArrayHasKey('site', $schema);
        $this->assertArrayHasKey('smtp', $schema);
        $this->assertArrayHasKey('payment', $schema);
        $this->assertArrayHasKey('ai', $schema);
        $this->assertArrayHasKey('social', $schema);
    }

    public function test_settings_service_save_group(): void
    {
        $service = app(SettingsService::class);

        $service->saveGroup('site', [
            'site_title' => 'عنوان جدید تست',
            'maintenance_mode' => true,
        ]);

        $this->assertEquals('عنوان جدید تست', Setting::get('site_title'));
        $this->assertEquals('1', Setting::get('maintenance_mode'));
    }

    public function test_settings_service_save_group_skips_empty_password(): void
    {
        $service = app(SettingsService::class);

        // مقدار اولیه
        $service->saveGroup('smtp', ['smtp_password' => 'secret123']);

        // ذخیره password خالی = بدون تغییر
        $service->saveGroup('smtp', ['smtp_password' => '']);

        $this->assertEquals('secret123', Setting::get('smtp_password'));
    }

    public function test_settings_service_group_values_merges_defaults(): void
    {
        $service = app(SettingsService::class);

        // مقدار پیش‌فرض schema باید برگردد وقتی رکورد نیست
        $values = $service->groupValues('site');

        $this->assertEquals('علی شقاقی | توسعه‌دهنده فول‌استک', $values['site_title']);
    }

    // ===== تست دسترسی مسیرها =====

    public function test_guest_is_redirected_from_settings(): void
    {
        $this->get('/admin/settings')->assertRedirect('/admin/login');
    }

    public function test_super_admin_can_access_settings_groups(): void
    {
        $this->actingAs($this->superAdmin, 'admin');

        $this->get('/admin/settings')->assertOk();
        $this->get('/admin/settings/site')->assertOk();
        $this->get('/admin/settings/smtp')->assertOk();
        $this->get('/admin/settings/payment')->assertOk();
        $this->get('/admin/settings/ai')->assertOk();
        $this->get('/admin/settings/social')->assertOk();
        $this->get('/admin/settings/security')->assertOk();
    }

    public function test_unknown_settings_group_is_404(): void
    {
        $this->actingAs($this->superAdmin, 'admin');

        $this->get('/admin/settings/nonexistent')->assertNotFound();
    }

    public function test_boolean_toggles_are_styled_green_when_active(): void
    {
        // سوییچ‌های بولین (مثل حالت توسعه) وقتی فعال شوند سبز می‌شوند
        $this->actingAs($this->superAdmin, 'admin');

        $this->get('/admin/settings/site')
            ->assertOk()
            ->assertSee('checked:bg-emerald-500', false)
            ->assertSee('has-[:checked]:bg-emerald-50', false);
    }

    public function test_legacy_general_redirects_to_site(): void
    {
        $this->actingAs($this->superAdmin, 'admin');

        $this->get('/admin/settings/general')->assertRedirect('/admin/settings/site');
    }

    public function test_plain_user_is_forbidden_from_settings(): void
    {
        $this->actingAs($this->plainUser);

        // قانون پروژه: دسترسی غیرمجاز = فقط صفحه «یافت نشد» (404)
        $this->get('/admin/settings')->assertNotFound();
    }

    // ===== تست Livewire =====

    public function test_settings_form_saves_values(): void
    {
        Livewire::actingAs($this->superAdmin, 'admin')
            ->test(SettingsForm::class, [
                'group' => 'site',
                'initialValues' => app(SettingsService::class)->groupValues('site'),
            ])
            ->set('values.site_title', 'عنوان تستی از فرم')
            ->call('save');

        $this->assertEquals('عنوان تستی از فرم', Setting::get('site_title'));
    }

    public function test_settings_form_validates_email(): void
    {
        Livewire::actingAs($this->superAdmin, 'admin')
            ->test(SettingsForm::class, [
                'group' => 'contact',
                'initialValues' => app(SettingsService::class)->groupValues('contact'),
            ])
            ->set('values.contact_email', 'invalid-email-format')
            ->call('save')
            ->assertHasErrors(['values.contact_email']);
    }

    // ===== باگ «حالت توسعه» — سوییچ بولین بعد از لود صفحه برمی‌گشت =====

    public function test_boolean_group_values_return_real_booleans(): void
    {
        // رشته '0' در جاوااسکریپت truthy است؛ اگر groupValues رشته برگرداند
        // سوییچ بعد از هر لود روشن نمایش داده می‌شود (رگرسیون باگ حالت توسعه)
        Setting::set('dev_mode', '0', 'site');
        $values = app(SettingsService::class)->groupValues('site');
        $this->assertIsBool($values['dev_mode']);
        $this->assertFalse($values['dev_mode']);

        Setting::set('dev_mode', '1', 'site');
        $values = app(SettingsService::class)->groupValues('site');
        $this->assertIsBool($values['dev_mode']);
        $this->assertTrue($values['dev_mode']);
    }

    public function test_dev_mode_toggle_persists_both_directions(): void
    {
        // فعال‌سازی از صفحه تنظیمات → ذخیره → مقدار واقعی '1' می‌شود
        Livewire::actingAs($this->superAdmin, 'admin')
            ->test(SettingsManager::class, ['initialTab' => 'site'])
            ->set('values.dev_mode', true)
            ->call('save');

        $this->assertSame('1', Setting::get('dev_mode'));

        // مونت مجدد (معادل لود دوباره صفحه) باید bool واقعی true ببیند
        $values = app(SettingsService::class)->groupValues('site');
        $this->assertTrue($values['dev_mode']);

        // غیرفعال‌سازی → ذخیره → '0' و بعد از مونت مجدد هم false برمی‌گردد
        Livewire::actingAs($this->superAdmin, 'admin')
            ->test(SettingsManager::class, ['initialTab' => 'site'])
            ->set('values.dev_mode', false)
            ->call('save');

        $this->assertSame('0', Setting::get('dev_mode'));
        $this->assertFalse(app(SettingsService::class)->groupValues('site')['dev_mode']);
        $this->assertFalse(\Modules\Settings\Models\Setting::getBool('dev_mode', true));
    }
}
