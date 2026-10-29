<?php

namespace Tests\Feature\Orders;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Orders\Enums\PaymentMethod;
use Modules\Orders\Models\Order;
use Modules\Orders\Services\OrderService;
use Modules\Orders\Services\PaymentService;
use Modules\Settings\Models\Setting;
use Tests\TestCase;

/**
 * تست‌های PaymentService مشترک — درگاه + callback + روش‌های پرداخت
 *
 * CHECKPOINT ۱۰: خرید محصول / رزرو مشاوره / پرداخت پروژه از درگاه کار کنند.
 */
class PaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        $this->user = User::factory()->create(['is_active' => true]);

        // درگاه پیش‌فرض روی شبیه‌ساز
        Setting::set('payment_driver', 'simulator', 'payment', true);
        Setting::flushCache();
    }

    private function makeOrder(): Order
    {
        return app(OrderService::class)->save(new Order, [
            'user_id' => $this->user->id,
            'title' => 'سفارش تستی',
            'status' => 'pending',
            'amount' => 1000000,
            'paid_amount' => 0,
        ]);
    }

    // ===== PaymentMethod enum =====

    public function test_payment_method_enum_labels(): void
    {
        $this->assertSame('درگاه آنلاین', PaymentMethod::Gateway->label());
        $this->assertSame('پرداخت اقساطی', PaymentMethod::Installment->label());
        $this->assertSame('کارت به کارت (دستی)', PaymentMethod::Manual->label());
    }

    public function test_only_manual_requires_admin_approval(): void
    {
        $this->assertTrue(PaymentMethod::Manual->requiresAdminApproval());
        $this->assertFalse(PaymentMethod::Gateway->requiresAdminApproval());
        $this->assertFalse(PaymentMethod::Installment->requiresAdminApproval());
    }

    public function test_from_value_falls_back_to_gateway(): void
    {
        $this->assertSame(PaymentMethod::Gateway, PaymentMethod::fromValue('gateway'));
        $this->assertSame(PaymentMethod::Manual, PaymentMethod::fromValue('manual'));
        $this->assertSame(PaymentMethod::Gateway, PaymentMethod::fromValue(null));
        $this->assertSame(PaymentMethod::Gateway, PaymentMethod::fromValue('invalid'));
    }

    // ===== تنظیمات درگاه =====

    public function test_active_driver_read_from_settings(): void
    {
        $service = app(PaymentService::class);

        $this->assertSame('simulator', $service->activeDriver());
        $this->assertSame('شبیه‌ساز پرداخت', $service->driverLabel());
        $this->assertTrue($service->isOnlineEnabled());
    }

    public function test_manual_driver_disables_online_payment(): void
    {
        Setting::set('payment_driver', 'manual', 'payment', true);
        Setting::flushCache();

        $service = app(PaymentService::class);

        $this->assertSame('manual', $service->activeDriver());
        $this->assertFalse($service->isOnlineEnabled());
    }

    public function test_installment_settings_from_settings_group(): void
    {
        Setting::set('installment_enabled', '1', 'payment_extended');
        Setting::set('installment_max_count', '6', 'payment_extended');
        Setting::set('installment_interest_rate', '5', 'payment_extended');
        Setting::flushCache();

        $service = app(PaymentService::class);

        $this->assertTrue($service->isInstallmentEnabled());
        $this->assertSame(6, $service->installmentMaxCount());
        $this->assertSame(5.0, $service->installmentInterestRate());
    }

    // ===== ساخت و تکمیل پرداخت =====

    public function test_create_records_pending_payment(): void
    {
        $order = $this->makeOrder();

        $payment = app(PaymentService::class)->create($order, [
            'amount' => 500000,
            'method' => PaymentMethod::Gateway,
            'description' => 'تست پرداخت',
        ]);

        $this->assertSame('pending', $payment->status);
        $this->assertSame('gateway', $payment->payment_method);
        $this->assertEquals(500000, (float) $payment->amount);
        $this->assertSame($order->id, $payment->order_id);
    }
}
