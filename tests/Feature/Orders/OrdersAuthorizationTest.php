<?php

namespace Tests\Feature\Orders;

use App\Models\Admin;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Models\Role;
use Modules\Orders\Database\Seeders\OrdersPermissionSeeder;
use Modules\Orders\Models\Order;
use Modules\Orders\Models\Payment;
use Modules\Orders\Services\OrderService;
use Tests\TestCase;

class OrdersAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $superAdmin;

    protected User $author;

    protected User $support;

    protected User $plainUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        $this->seed(OrdersPermissionSeeder::class);

        $superRole = Role::where('slug', 'super-admin')->firstOrFail();
        $supportRole = Role::where('slug', 'support')->firstOrFail();
        $userRole = Role::where('slug', 'user')->firstOrFail();

        $this->superAdmin = $this->makeAdmin('super@test.com', 'super-admin');

        // کاربر عادی برای FK جدول users (سفارش‌ها)
        $this->author = User::create(['name' => 'Author', 'email' => 'orders-author@test.com', 'password' => 'password', 'is_active' => true]);
        $this->author->roles()->attach($userRole);

        $this->support = User::create(['name' => 'Support', 'email' => 'support@test.com', 'password' => 'password', 'is_active' => true]);
        $this->support->roles()->attach($supportRole);

        $this->plainUser = User::create(['name' => 'User', 'email' => 'user@test.com', 'password' => 'password', 'is_active' => true]);
        $this->plainUser->roles()->attach($userRole);
    }

    // ===== تست Policies =====

    public function test_super_admin_can_manage_orders(): void
    {
        $order = Order::create(['user_id' => $this->author->id, 'order_number' => 'ORD-TEST1', 'title' => 'تست', 'amount' => 1000000]);

        $this->assertTrue($this->superAdmin->can('viewAny', Order::class));
        $this->assertTrue($this->superAdmin->can('create', Order::class));
        $this->assertTrue($this->superAdmin->can('update', $order));
        $this->assertTrue($this->superAdmin->can('delete', $order));
    }

    public function test_support_can_manage_orders(): void
    {
        $order = Order::create(['user_id' => $this->plainUser->id, 'order_number' => 'ORD-TEST2', 'title' => 'تست', 'amount' => 1000000]);

        $this->assertTrue($this->support->can('viewAny', Order::class));
        $this->assertTrue($this->support->can('update', $order));
    }

    public function test_plain_user_cannot_manage_orders(): void
    {
        $order = Order::create(['user_id' => $this->author->id, 'order_number' => 'ORD-TEST3', 'title' => 'تست', 'amount' => 1000000]);

        $this->assertFalse($this->plainUser->can('viewAny', Order::class));
        $this->assertFalse($this->plainUser->can('create', Order::class));
        $this->assertFalse($this->plainUser->can('delete', $order));
    }

    // ===== تست دسترسی مسیرها =====

    public function test_guest_is_redirected_from_orders(): void
    {
        $this->get('/admin/orders')->assertRedirect('/admin/login');
    }

    public function test_admin_can_access_orders_pages(): void
    {
        $this->actingAs($this->superAdmin, 'admin');

        $order = Order::create(['user_id' => $this->author->id, 'order_number' => 'ORD-ROUTE', 'title' => 'تست', 'amount' => 1000000]);

        $this->get('/admin/orders')->assertOk();
        $this->get('/admin/orders/pending')->assertOk();
        $this->get('/admin/orders/'.$order->id)->assertOk();
    }

    public function test_plain_user_is_forbidden_from_orders(): void
    {
        $this->actingAs($this->plainUser);

        // قانون پروژه: دسترسی غیرمجاز = فقط صفحه «یافت نشد» (404)
        $this->get('/admin/orders')->assertNotFound();
        $this->get('/admin/orders/pending')->assertNotFound();
    }

    // ===== تست Service =====

    public function test_order_service_generates_unique_order_number(): void
    {
        $service = app(OrderService::class);

        $number1 = $service->generateOrderNumber();
        $number2 = $service->generateOrderNumber();

        $this->assertNotEquals($number1, $number2);
        $this->assertStringStartsWith('ORD-', $number1);
    }

    public function test_order_service_save_sets_order_number(): void
    {
        $service = app(OrderService::class);

        $order = $service->save(new Order, [
            'user_id' => $this->author->id,
            'title' => 'سفارش بدون شماره',
            'status' => 'pending',
            'priority' => 'medium',
            'amount' => 5000000,
        ]);

        $this->assertNotEmpty($order->order_number);
        $this->assertStringStartsWith('ORD-', $order->order_number);
    }

    public function test_order_service_search_and_filter(): void
    {
        $service = app(OrderService::class);

        Order::create(['user_id' => $this->author->id, 'order_number' => 'ORD-F1', 'title' => 'فروشگاه اینترنتی', 'status' => 'pending', 'amount' => 100]);
        Order::create(['user_id' => $this->author->id, 'order_number' => 'ORD-F2', 'title' => 'اپلیکیشن موبایل', 'status' => 'completed', 'amount' => 100]);

        // جستجو
        $this->assertEquals(1, $service->list(['search' => 'فروشگاه'])->total());

        // فیلتر وضعیت
        $this->assertEquals(1, $service->list(['status' => 'pending'])->total());
        $this->assertEquals(1, $service->list(['status' => 'completed'])->total());
    }

    public function test_order_service_set_status_and_priority(): void
    {
        $service = app(OrderService::class);
        $order = Order::create(['user_id' => $this->author->id, 'order_number' => 'ORD-ST', 'title' => 'تست', 'status' => 'pending', 'priority' => 'low', 'amount' => 100]);

        $service->setStatus($order, 'development');
        $this->assertEquals('development', $order->fresh()->status);

        $service->setPriority($order, 'urgent');
        $this->assertEquals('urgent', $order->fresh()->priority);
    }

    public function test_order_service_add_payment_updates_paid_amount(): void
    {
        $service = app(OrderService::class);
        $order = Order::create(['user_id' => $this->author->id, 'order_number' => 'ORD-PAY', 'title' => 'تست پرداخت', 'status' => 'accepted', 'amount' => 10000000, 'paid_amount' => 0]);

        // پرداخت اول
        $service->addPayment($order, ['amount' => 4000000, 'payment_method' => 'زرین‌پال']);
        $this->assertEquals(4000000, (float) $order->fresh()->paid_amount);

        // پرداخت دوم
        $service->addPayment($order, ['amount' => 6000000, 'payment_method' => 'کارت به کارت']);
        $this->assertEquals(10000000, (float) $order->fresh()->paid_amount);

        // تسویه کامل
        $this->assertTrue($order->fresh()->is_paid);
        $this->assertEquals(2, $order->fresh()->payments->count());
    }

    public function test_order_service_stats(): void
    {
        $service = app(OrderService::class);

        Order::create(['user_id' => $this->author->id, 'order_number' => 'ORD-S1', 'title' => 'یک', 'status' => 'pending', 'amount' => 100]);
        Order::create(['user_id' => $this->author->id, 'order_number' => 'ORD-S2', 'title' => 'دو', 'status' => 'completed', 'amount' => 100]);

        $stats = $service->stats();

        $this->assertEquals(2, $stats['total']);
        $this->assertEquals(1, $stats['pending']);
        $this->assertEquals(1, $stats['completed']);
    }

    public function test_order_service_delete_removes_payments(): void
    {
        $service = app(OrderService::class);
        $order = Order::create(['user_id' => $this->author->id, 'order_number' => 'ORD-DEL', 'title' => 'حذف', 'status' => 'pending', 'amount' => 100]);

        $service->addPayment($order, ['amount' => 50000]);
        $this->assertEquals(1, Payment::where('order_id', $order->id)->count());

        $service->delete($order);

        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
        $this->assertEquals(0, Payment::where('order_id', $order->id)->count());
    }
}
