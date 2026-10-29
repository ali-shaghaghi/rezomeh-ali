<?php

namespace Tests\Feature\Orders;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\Core\Models\Role;
use Modules\Orders\Database\Seeders\OrdersPermissionSeeder;
use Modules\Orders\Livewire\Admin\OrdersIndex;
use Modules\Orders\Models\Order;
use Tests\TestCase;

class OrdersLivewireTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        $this->seed(OrdersPermissionSeeder::class);

        $superRole = Role::where('slug', 'super-admin')->firstOrFail();
        $this->admin = User::create(['name' => 'Admin', 'email' => 'admin@test.com', 'password' => 'password', 'is_active' => true]);
        $this->admin->roles()->attach($superRole);
    }

    // ===== OrdersIndex =====

    public function test_orders_index_renders_orders(): void
    {
        Order::create(['user_id' => $this->admin->id, 'order_number' => 'ORD-LW1', 'title' => 'سفارش تستی', 'status' => 'pending', 'amount' => 1000000]);

        Livewire::actingAs($this->admin)
            ->test(OrdersIndex::class)
            ->assertSee('سفارش تستی')
            ->assertSee('مدیریت سفارشات');
    }

    public function test_orders_index_search_filters(): void
    {
        Order::create(['user_id' => $this->admin->id, 'order_number' => 'ORD-SRC1', 'title' => 'طراحی فروشگاه', 'status' => 'pending', 'amount' => 100]);
        Order::create(['user_id' => $this->admin->id, 'order_number' => 'ORD-SRC2', 'title' => 'ربات تلگرام', 'status' => 'pending', 'amount' => 100]);

        Livewire::actingAs($this->admin)
            ->test(OrdersIndex::class)
            ->set('search', 'فروشگاه')
            ->assertSee('طراحی فروشگاه')
            ->assertDontSee('ربات تلگرام');
    }

    public function test_orders_index_status_filter(): void
    {
        Order::create(['user_id' => $this->admin->id, 'order_number' => 'ORD-FP1', 'title' => 'عنوان سفارش فعال', 'status' => 'pending', 'amount' => 100]);
        Order::create(['user_id' => $this->admin->id, 'order_number' => 'ORD-FP2', 'title' => 'عنوان سفارش تمام‌شده', 'status' => 'completed', 'amount' => 100]);

        Livewire::actingAs($this->admin)
            ->test(OrdersIndex::class)
            ->set('status', 'completed')
            ->assertSee('عنوان سفارش تمام‌شده')
            ->assertDontSee('عنوان سفارش فعال');
    }

    public function test_orders_index_empty_state(): void
    {
        Livewire::actingAs($this->admin)
            ->test(OrdersIndex::class)
            ->assertSee('هنوز سفارشی ثبت نشده است');
    }

    public function test_orders_index_creates_order(): void
    {
        Livewire::actingAs($this->admin)
            ->test(OrdersIndex::class)
            ->call('openCreateForm')
            ->set('title', 'سفارش جدید تست')
            ->set('amount', 5000000)
            ->set('user_id', $this->admin->id)
            ->set('order_status', 'pending')
            ->set('order_priority', 'high')
            ->call('saveOrder');

        $this->assertDatabaseHas('orders', ['title' => 'سفارش جدید تست']);

        $order = Order::where('title', 'سفارش جدید تست')->first();
        $this->assertStringStartsWith('ORD-', $order->order_number);
        $this->assertEquals('high', $order->priority);
    }

    public function test_orders_index_validation_requires_title(): void
    {
        Livewire::actingAs($this->admin)
            ->test(OrdersIndex::class)
            ->call('openCreateForm')
            ->set('title', '')
            ->set('amount', 1000000)
            ->set('user_id', $this->admin->id)
            ->call('saveOrder')
            ->assertHasErrors(['title' => 'required']);
    }

    public function test_orders_index_quick_status_action(): void
    {
        $order = Order::create(['user_id' => $this->admin->id, 'order_number' => 'ORD-QS', 'title' => 'تست', 'status' => 'pending', 'amount' => 100]);

        Livewire::actingAs($this->admin)
            ->test(OrdersIndex::class)
            ->call('quickStatus', $order->id, 'development');

        $this->assertEquals('development', $order->fresh()->status);
    }

    public function test_orders_index_edit_and_update(): void
    {
        $order = Order::create(['user_id' => $this->admin->id, 'order_number' => 'ORD-EDT', 'title' => 'قبل ویرایش', 'status' => 'pending', 'amount' => 1000000]);

        Livewire::actingAs($this->admin)
            ->test(OrdersIndex::class)
            ->call('openEditForm', $order->id)
            ->assertSet('editing', true)
            ->set('title', 'بعد ویرایش')
            ->set('amount', 2000000)
            ->set('user_id', $this->admin->id)
            ->set('order_status', 'review')
            ->set('order_priority', 'urgent')
            ->call('saveOrder');

        $this->assertEquals('بعد ویرایش', $order->fresh()->title);
        $this->assertEquals(2000000, (float) $order->fresh()->amount);
        $this->assertEquals('review', $order->fresh()->status);
        // شماره سفارش در ویرایش تغییر نمی‌کند
        $this->assertEquals('ORD-EDT', $order->fresh()->order_number);
    }

    public function test_orders_index_payment_modal_and_save(): void
    {
        $order = Order::create(['user_id' => $this->admin->id, 'order_number' => 'ORD-PAY', 'title' => 'تست پرداخت', 'status' => 'accepted', 'amount' => 10000000, 'paid_amount' => 0]);

        Livewire::actingAs($this->admin)
            ->test(OrdersIndex::class)
            ->call('openPaymentModal', $order->id)
            ->assertSet('paymentModalOpen', true)
            // مبلغ پیشنهادی = باقیمانده سفارش
            ->assertSet('payment_amount', 10000000)
            ->set('payment_method', 'زرین‌پال')
            ->set('payment_amount', 3000000)
            ->call('savePayment');

        $this->assertEquals(3000000, (float) $order->fresh()->paid_amount);
        $this->assertEquals(1, $order->fresh()->payments->count());
        $this->assertFalse($order->fresh()->is_paid);
    }

    public function test_orders_index_delete_with_confirmation(): void
    {
        $order = Order::create(['user_id' => $this->admin->id, 'order_number' => 'ORD-DEL', 'title' => 'حذف شونده', 'status' => 'pending', 'amount' => 100]);

        $component = Livewire::actingAs($this->admin)
            ->test(OrdersIndex::class)
            ->call('confirmDelete', $order->id)
            ->assertSet('confirmingDelete', true)
            ->call('deleteOrder');

        $this->assertFalse($component->get('confirmingDelete'));
        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
    }

    public function test_orders_index_pending_initial_filter(): void
    {
        Order::create(['user_id' => $this->admin->id, 'order_number' => 'ORD-PND', 'title' => 'فیلتر انتظار', 'status' => 'pending', 'amount' => 100]);
        Order::create(['user_id' => $this->admin->id, 'order_number' => 'ORD-CMP', 'title' => 'تکمیل فیلتر', 'status' => 'completed', 'amount' => 100]);

        Livewire::actingAs($this->admin)
            ->test(OrdersIndex::class, ['initialStatus' => 'pending'])
            ->assertSee('فیلتر انتظار')
            ->assertDontSee('تکمیل فیلتر');
    }
}
