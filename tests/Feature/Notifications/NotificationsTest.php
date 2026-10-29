<?php

namespace Tests\Feature\Notifications;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Modules\Core\Models\Role;
use Modules\Notifications\Database\Seeders\NotificationsPermissionSeeder;
use Modules\Notifications\Livewire\Admin\NotificationsBell;
use Modules\Notifications\Livewire\Admin\NotificationsList;
use Modules\Notifications\Livewire\User\UserNotifications;
use Modules\Notifications\Notifications\NewOrderNotification;
use Modules\Notifications\Notifications\PaymentReceivedNotification;
use Modules\Notifications\Notifications\ProductSoldNotification;
use Modules\Notifications\Services\NotificationService;
use Tests\TestCase;

/**
 * تست‌های ماژول Notifications (فاز ۱۵) — مسیرها، سرویس و کامپوننت‌های Livewire.
 */
class NotificationsTest extends TestCase
{
    use RefreshDatabase;

    protected \App\Models\Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        $this->seed(NotificationsPermissionSeeder::class);

        $this->admin = $this->makeAdmin('notif-admin@test.com', 'super-admin');
    }

    private function makeUser(string $email = 'reader@test.com'): User
    {
        $user = User::create([
            'name' => 'Reader', 'email' => $email,
            'password' => 'password', 'is_active' => true,
        ]);
        $user->roles()->attach(Role::where('slug', 'user')->firstOrFail());

        return $user;
    }

    private function notifyAdmin(string $notificationClass, array $data = []): void
    {
        $this->admin->notify(new $notificationClass($data));
    }

    // ===== مجوزها =====

    public function test_permissions_seeded_for_admin_and_user(): void
    {
        $this->assertTrue($this->admin->hasPermission('view_notifications'));
        $this->assertTrue($this->admin->hasPermission('manage_notifications'));

        $user = $this->makeUser();

        $this->assertTrue($user->hasPermission('view_notifications'));
        $this->assertFalse($user->hasPermission('manage_notifications'));
    }

    // ===== مسیرها =====

    public function test_guest_is_redirected_from_admin_notifications(): void
    {
        $this->get('/admin/notifications')->assertRedirect('/admin/login');
    }

    public function test_admin_can_view_notifications_page(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->get('/admin/notifications')
            ->assertOk()
            ->assertSeeLivewire('notifications.list');
    }

    public function test_plain_user_gets_404_on_admin_notifications(): void
    {
        $this->actingAs($this->makeUser())
            ->get('/admin/notifications')
            ->assertNotFound();
    }

    public function test_user_no_longer_has_notifications_access(): void
    {
        // اعلان‌ها ویژگی ادمین است — تب notifications از پنل کاربر حذف شده است.
        $user = $this->makeUser();

        // لینک قدیمی ?tab=notifications بدون خطا به داشبورد عادی می‌رسد و
        // کامپوننت اعلان کاربر در صفحه نیست.
        $this->actingAs($user)
            ->get('/dashboard?tab=notifications')
            ->assertOk()
            ->assertDontSeeLivewire('notifications.user');

        // مسیر قدیمی /dashboard/notifications هم به داشبورد برمی‌گردد
        $this->actingAs($user)
            ->get('/dashboard/notifications')
            ->assertRedirect('/dashboard');
    }

    // ===== NotificationService =====

    public function test_unread_count_reflects_notifications(): void
    {
        $service = app(NotificationService::class);

        $this->assertSame(0, $service->unreadCount($this->admin));

        $this->notifyAdmin(NewOrderNotification::class, ['order_number' => 'ORD-123']);

        $this->assertSame(1, $service->unreadCount($this->admin));
    }

    public function test_mark_as_read(): void
    {
        $this->notifyAdmin(NewOrderNotification::class, ['order_number' => 'ORD-123']);
        $service = app(NotificationService::class);

        $notification = $this->admin->notifications->first();
        $this->assertNotNull($notification);

        $this->assertTrue($service->markAsRead($this->admin, $notification->id));
        $this->assertSame(0, $service->unreadCount($this->admin));
    }

    public function test_mark_as_read_fails_for_other_users_notification(): void
    {
        $user = $this->makeUser();
        $user->notify(new NewOrderNotification(['order_number' => 'ORD-X']));
        $service = app(NotificationService::class);

        $notification = $user->notifications->first();
        $this->assertNotNull($notification);

        // admin نمی‌تواند اعلان user را بخواند
        $this->assertFalse($service->markAsRead($this->admin, $notification->id));
    }

    public function test_mark_all_as_read(): void
    {
        $this->notifyAdmin(NewOrderNotification::class, ['order_number' => 'ORD-1']);
        $this->notifyAdmin(PaymentReceivedNotification::class, ['amount' => 1000]);
        $service = app(NotificationService::class);

        $this->assertSame(2, $service->unreadCount($this->admin));

        $service->markAllAsRead($this->admin);

        $this->assertSame(0, $service->unreadCount($this->admin));
    }

    public function test_notify_users_with_permission_targets_admins(): void
    {
        Notification::fake();

        $service = app(NotificationService::class);

        $service->notifyUsersWithPermission(
            'view_notifications',
            NewOrderNotification::class,
            ['order_number' => 'ORD-BROADCAST']
        );

        Notification::assertSentTo($this->admin, NewOrderNotification::class);
    }

    public function test_style_for_maps_notification_types(): void
    {
        $service = app(NotificationService::class);

        $this->assertSame('orders', $service->styleFor(NewOrderNotification::class)['icon']);
        $this->assertSame('wallet', $service->styleFor(PaymentReceivedNotification::class)['icon']);
        $this->assertSame('products', $service->styleFor(ProductSoldNotification::class)['icon']);
        $this->assertSame('bell', $service->styleFor('UnknownNotification')['icon']);
    }

    // ===== کلاس‌های Notification =====

    public function test_new_order_notification_content(): void
    {
        $notification = new NewOrderNotification([
            'order_number' => 'ORD-123',
            'order_title' => 'سایت فروشگاهی',
            'amount' => 5000000,
        ]);

        $this->assertSame('سفارش جدید ثبت شد', $notification->title());
        $this->assertStringContainsString('ORD-123', $notification->description());
        $this->assertStringContainsString('سایت فروشگاهی', $notification->description());
        $this->assertSame(['database'], $notification->via($this->admin));
    }

    public function test_payment_received_notification_content(): void
    {
        $notification = new PaymentReceivedNotification([
            'amount' => 1000000,
            'order_number' => 'ORD-456',
        ]);

        $this->assertSame('پرداخت دریافت شد', $notification->title());
        $this->assertStringContainsString('ORD-456', $notification->description());
    }

    // ===== کامپوننت‌های Livewire =====

    public function test_bell_component_renders_with_count(): void
    {
        $this->notifyAdmin(NewOrderNotification::class, ['order_number' => 'ORD-1']);

        Livewire::actingAs($this->admin, 'admin')
            ->test(NotificationsBell::class)
            ->assertOk()
            ->assertViewHas('unreadCount', 1);
    }

    public function test_bell_component_marks_notification_as_read(): void
    {
        $this->notifyAdmin(NewOrderNotification::class, ['order_number' => 'ORD-1']);
        $notificationId = $this->admin->notifications->first()->id;

        Livewire::actingAs($this->admin, 'admin')
            ->test(NotificationsBell::class)
            ->call('markAsRead', $notificationId)
            ->assertViewHas('unreadCount', 0);
    }

    public function test_bell_component_marks_all_as_read(): void
    {
        $this->notifyAdmin(NewOrderNotification::class, ['order_number' => 'ORD-1']);
        $this->notifyAdmin(PaymentReceivedNotification::class, ['amount' => 1000]);

        Livewire::actingAs($this->admin, 'admin')
            ->test(NotificationsBell::class)
            ->call('markAllRead')
            ->assertViewHas('unreadCount', 0);
    }

    public function test_list_component_filters_unread(): void
    {
        $this->notifyAdmin(NewOrderNotification::class, ['order_number' => 'ORD-1']);
        $this->notifyAdmin(PaymentReceivedNotification::class, ['amount' => 1000]);
        app(NotificationService::class)->markAllAsRead($this->admin);
        $this->notifyAdmin(NewOrderNotification::class, ['order_number' => 'ORD-2']);

        Livewire::actingAs($this->admin, 'admin')
            ->test(NotificationsList::class)
            ->assertViewHas('notifications', fn ($notifications) => $notifications->total() === 3)
            ->set('filter', 'unread')
            ->assertViewHas('notifications', fn ($notifications) => $notifications->total() === 1);
    }

    public function test_user_notifications_component(): void
    {
        $user = $this->makeUser();
        $user->notify(new NewOrderNotification(['order_number' => 'ORD-U1']));

        Livewire::actingAs($user)
            ->test(UserNotifications::class)
            ->assertOk()
            ->assertViewHas('unreadCount', 1);
    }

    public function test_user_notifications_component_marks_as_read(): void
    {
        $user = $this->makeUser();
        $user->notify(new NewOrderNotification(['order_number' => 'ORD-U1']));
        $notificationId = $user->notifications->first()->id;

        Livewire::actingAs($user)
            ->test(UserNotifications::class)
            ->call('markAsRead', $notificationId)
            ->assertViewHas('unreadCount', 0);
    }

    // ===== زنجیره کامل (Integration) =====

    public function test_creating_order_via_service_generates_notification(): void
    {
        $user = $this->makeUser('buyer@test.com');

        // ثبت سفارش جدید از طریق OrderService — باید اعلان برای ادمین ارسال کند
        $order = app(\Modules\Orders\Services\OrderService::class)->save(new \Modules\Orders\Models\Order, [
            'user_id' => $user->id,
            'title' => 'سفارش تستی از زنجیره',
            'status' => 'pending',
            'amount' => 2000000,
            'paid_amount' => 0,
        ]);

        $service = app(NotificationService::class);

        $this->assertSame(1, $service->unreadCount($this->admin));

        $notification = $this->admin->notifications->first();
        $this->assertNotNull($notification);
        $this->assertSame(NewOrderNotification::class, $notification->type);
        $this->assertSame($order->order_number, $notification->data['order_number']);
    }

    public function test_successful_payment_generates_payment_notification(): void
    {
        $user = $this->makeUser('buyer2@test.com');
        $order = app(\Modules\Orders\Services\OrderService::class)->save(new \Modules\Orders\Models\Order, [
            'user_id' => $user->id,
            'title' => 'سفارش پرداختی',
            'status' => 'pending',
            'amount' => 1000000,
            'paid_amount' => 0,
        ]);

        // پرداخت کامل موفق
        app(\Modules\Orders\Services\OrderService::class)->addPayment($order, [
            'user_id' => $user->id,
            'amount' => 1000000,
            'status' => 'completed',
            'payment_method' => 'gateway',
        ]);

        $service = app(NotificationService::class);

        // ۱ سفارش + ۱ پرداخت = ۲ اعلان
        $this->assertSame(2, $service->unreadCount($this->admin));

        $types = $this->admin->notifications->pluck('type')->map(fn ($t) => class_basename($t))->toArray();
        $this->assertContains('PaymentReceivedNotification', $types);
    }
}
