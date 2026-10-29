<?php

namespace Tests\Feature\AdminPanel;

use App\Models\Admin;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Models\Role;
use Modules\Orders\Models\Order;
use Modules\Orders\Models\Payment;
use Modules\Tickets\Models\Ticket;
use Tests\TestCase;

/**
 * تست‌های پنل ادمین — لایوت، داشبورد و کنترل دسترسی.
 *
 * CHECKPOINT ۱۲: پنل ادمین برای نقش ادمین باز می‌شود و کاربر عادی ۴۰۴ می‌گیرد.
 */
class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;

    protected User $plainUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        $this->admin = $this->makeAdmin('admin-panel@test.com', 'super-admin');

        $this->plainUser = User::create(['name' => 'User', 'email' => 'plain-panel@test.com', 'password' => 'password', 'is_active' => true]);
        $this->plainUser->roles()->attach(Role::where('slug', 'user')->firstOrFail());
    }

    // ===== کنترل دسترسی =====

    public function test_guest_is_redirected_to_login(): void
    {
        // ریدایرکت میان‌بر بدون توکن → /admin/login (روت توکن‌دار است؛ دنبال‌کردنش 404 می‌شود)
        $this->get('/admin/dashboard')->assertRedirect('/admin/login');
    }

    public function test_admin_can_view_dashboard(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->get('/admin/dashboard')
            ->assertOk()
            ->assertViewIs('adminpanel::dashboard.index');
    }

    public function test_plain_user_gets_404_on_admin_dashboard(): void
    {
        // قانون پروژه: دسترسی غیرمجاز = ۴۰۴ (نه ۴۰۳)
        $this->actingAs($this->plainUser)
            ->get('/admin/dashboard')
            ->assertNotFound();
    }

    public function test_inactive_admin_is_logged_out(): void
    {
        $this->admin->update(['is_active' => false]);

        $this->actingAs($this->admin, 'admin')
            ->get('/admin/dashboard')
            ->assertRedirect('/admin/login');

        $this->assertGuest('admin');
    }

    // ===== داشبورد: آمار =====

    public function test_dashboard_shows_stats(): void
    {
        Order::create([
            'user_id' => $this->plainUser->id,
            'order_number' => 'ORD-100001',
            'title' => 'سفارش تست',
            'status' => 'completed',
            'amount' => 500000,
            'paid_amount' => 500000,
        ]);

        Payment::create([
            'order_id' => Order::firstOrFail()->id,
            'user_id' => $this->plainUser->id,
            'amount' => 500000,
            'status' => 'completed',
            'paid_at' => now(),
        ]);

        Ticket::create([
            'user_id' => $this->plainUser->id,
            'subject' => 'تیکت تست',
            'message' => 'پیام تست',
            'status' => 'open',
            'priority' => 'medium',
        ]);

        $response = $this->actingAs($this->admin, 'admin')->get('/admin/dashboard');

        $response->assertOk();
        $response->assertViewHas('stats');
        $stats = $response->viewData('stats');

        $this->assertSame(1, $stats['total_users']); // فقط کاربر عادی — ادمین در جدول جدا است
        $this->assertSame(1, $stats['total_orders']);
        $this->assertSame(500000, (int) $stats['total_revenue']);
        $this->assertSame(1, $stats['open_tickets']);
    }

    public function test_dashboard_revenue_chart_groups_by_month(): void
    {
        Payment::create([
            'order_id' => Order::create([
                'user_id' => $this->plainUser->id,
                'order_number' => 'ORD-100002',
                'title' => 'سفارش نمودار',
                'status' => 'completed',
                'amount' => 200000,
                'paid_amount' => 200000,
            ])->id,
            'user_id' => $this->plainUser->id,
            'amount' => 200000,
            'status' => 'completed',
            'paid_at' => now(),
        ]);

        $response = $this->actingAs($this->admin, 'admin')->get('/admin/dashboard');

        $response->assertOk();
        $monthlyRevenue = $response->viewData('monthlyRevenue');

        $this->assertNotEmpty($monthlyRevenue);
        $this->assertSame(200000, (int) collect($monthlyRevenue)->first());
    }

    public function test_dashboard_lists_recent_records(): void
    {
        $response = $this->actingAs($this->admin, 'admin')->get('/admin/dashboard');

        $response->assertOk()
            ->assertViewHas(['recentOrders', 'recentPayments', 'recentActivity', 'weeklyTrafficLabels', 'weeklyTrafficData']);
    }

    // ===== صفحات گزارش‌ها (ماژول Analytics) =====

    public function test_analytics_pages_load(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->get('/admin/analytics')
            ->assertOk()
            ->assertViewIs('analytics::overview');

        $this->actingAs($this->admin, 'admin')
            ->get('/admin/analytics/sales')
            ->assertOk()
            ->assertViewIs('analytics::sales');

        $this->actingAs($this->admin, 'admin')
            ->get('/admin/analytics/traffic')
            ->assertOk()
            ->assertViewIs('analytics::traffic');

        $this->actingAs($this->admin, 'admin')
            ->get('/admin/analytics/content')
            ->assertOk()
            ->assertViewIs('analytics::content');
    }

    // ===== ویجت تماس در پنل ادمین =====

    public function test_admin_layout_renders_contact_widget(): void
    {
        \Modules\Settings\Models\Setting::set('contact_email', 'admin@example.com', 'contact');

        $this->actingAs($this->admin, 'admin')
            ->get('/admin/dashboard')
            ->assertSeeLivewire('contact.floating-widget');
    }
}
