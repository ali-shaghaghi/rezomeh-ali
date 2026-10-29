<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\Core\Models\Role;
use Modules\Settings\Livewire\Admin\SettingsManager;
use Modules\Settings\Models\Setting;
use Tests\TestCase;

class PaymentGatewayRenderTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_tab_renders_all_gateways(): void
    {
        // نقش‌ها و مجوزها (شامل manage_settings) — الگوی استاندارد تست‌های ادمین
        $this->seed(RolePermissionSeeder::class);

        $admin = $this->makeAdmin('gateway-admin@test.com', 'super-admin');

        $response = $this->actingAs($admin, 'admin')->get('/admin/settings/payment');

        $response->assertOk();
        $response->assertSee('زرین‌پال', false);
        $response->assertSee('آی‌دی‌پی', false);
        $response->assertSee('کیف پول ارز دیجیتال', false);
        $response->assertSee('کارت به کارت', false);
        $response->assertSeeLivewire('admin.settings.manager');
    }

    /**
     * تب درگاه پرداخت با کوئری‌استرینگ هم باز می‌شود (لینک‌های منوی ادمین)
     */
    public function test_payment_tab_renders_through_query_string(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $admin = $this->makeAdmin('gateway-admin@test.com', 'super-admin');

        $this->actingAs($admin, 'admin')
            ->get('/admin/settings?tab=payment')
            ->assertOk()
            ->assertSee('درگاه‌های پرداخت', false)
            ->assertSee('زرین‌پال', false)
            ->assertSee('کیف پول ارز دیجیتال', false);
    }

    /**
     * ذخیره تنظیمات درگاه‌ها (درایور، مرچنت هر درگاه، بازگشت، ارز دیجیتال)
     */
    public function test_gateway_settings_persist_including_crypto(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $admin = $this->makeAdmin('gateway-admin@test.com', 'super-admin');
        $this->actingAs($admin, 'admin');

        Livewire::actingAs($admin, 'admin')
            ->test(SettingsManager::class, ['initialTab' => 'payment'])
            ->call('toggleGateway', 'zarinpal')
            ->set('values.payment_merchant_id', 'zc-test-merchant')
            ->set('values.payment_callback_url', 'https://example.com/callback')
            ->set('values.payment_currency', 'تومان')
            ->call('saveGateway', 'zarinpal')
            ->assertHasNoErrors();

        $this->assertSame('zarinpal', Setting::get('payment_driver'));
        $this->assertSame('zc-test-merchant', Setting::get('payment_merchant_id_zarinpal'));
        $this->assertSame('https://example.com/callback', Setting::get('payment_callback_url'));

        // درگاه ارز دیجیتال — فیلدهای اختصاصی کیف پول
        Livewire::actingAs($admin, 'admin')
            ->test(SettingsManager::class, ['initialTab' => 'payment'])
            ->call('toggleGateway', 'crypto')
            ->set('values.crypto_wallet_address', 'TVh2xJ9vZ8abcd1234efgh')
            ->set('values.crypto_network', 'trc20')
            ->set('values.crypto_enabled', true)
            ->call('saveGateway', 'crypto')
            ->assertHasNoErrors();

        $this->assertSame('crypto', Setting::get('payment_driver'));
        $this->assertSame('TVh2xJ9vZ8abcd1234efgh', Setting::get('crypto_wallet_address'));
        $this->assertSame('1', Setting::get('crypto_enabled'));

        // مرچنت زرین‌پال بعد از فعال‌شدن ارز دیجیتال دست‌نخورده می‌ماند
        $this->assertSame('zc-test-merchant', Setting::get('payment_merchant_id_zarinpal'));
    }
}
