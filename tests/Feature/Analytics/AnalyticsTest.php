<?php

namespace Tests\Feature\Analytics;

use App\Models\Admin;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\Analytics\Livewire\Admin\AnalyticsOverview;
use Modules\Analytics\Livewire\Admin\ContentReport;
use Modules\Analytics\Livewire\Admin\SalesReport;
use Modules\Analytics\Livewire\Admin\TrafficReport;
use Modules\Analytics\Services\AnalyticsService;
use Modules\Core\Models\BlogPost;
use Modules\Core\Models\Role;
use Modules\Core\Models\UserActivity;
use Modules\Orders\Models\Order;
use Modules\Orders\Models\Payment;
use Modules\Products\Models\OrderItem;
use Tests\TestCase;

/**
 * تست‌های ماژول Analytics (فاز ۱۴) — مسیرها، سرویس و کامپوننت‌های Livewire.
 */
class AnalyticsTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;

    protected User $author;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        $this->admin = $this->makeAdmin('analytics-admin@test.com', 'super-admin');

        // نویسنده محتوا (کاربر عادی) — برای FK جدول users (پست/فعالیت)
        $this->author = $this->makeUser('analytics-author@test.com');
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

    private function makeOrder(User $user, float $amount = 1000000): Order
    {
        return Order::create([
            'user_id' => $user->id,
            'order_number' => 'ORD-TEST'.uniqid(),
            'title' => 'سفارش تستی',
            'status' => 'completed',
            'amount' => $amount,
            'paid_amount' => $amount,
        ]);
    }

    private function makePayment(Order $order, float $amount, string $method = 'gateway', ?string $paidAt = null): Payment
    {
        return Payment::create([
            'user_id' => $order->user_id,
            'order_id' => $order->id,
            'amount' => $amount,
            'status' => 'completed',
            'payment_method' => $method,
            'transaction_id' => 'TX-'.uniqid(),
            'paid_at' => $paidAt ?? now(),
        ]);
    }

    // ===== مسیرها =====

    public function test_guest_is_redirected_from_analytics_pages(): void
    {
        $this->get('/admin/analytics')->assertRedirect('/admin/login');
        $this->get('/admin/analytics/sales')->assertRedirect('/admin/login');
        $this->get('/admin/analytics/traffic')->assertRedirect('/admin/login');
        $this->get('/admin/analytics/content')->assertRedirect('/admin/login');
    }

    public function test_admin_can_view_analytics_pages(): void
    {
        $this->actingAs($this->admin, 'admin');

        $this->get('/admin/analytics')->assertOk()->assertSeeLivewire('analytics.overview');
        $this->get('/admin/analytics/sales')->assertOk()->assertSeeLivewire('analytics.sales');
        $this->get('/admin/analytics/traffic')->assertOk()->assertSeeLivewire('analytics.traffic');
        $this->get('/admin/analytics/content')->assertOk()->assertSeeLivewire('analytics.content');
    }

    public function test_plain_user_cannot_access_analytics_pages(): void
    {
        $this->actingAs($this->makeUser());

        // قانون پروژه: دسترسی غیرمجاز = 404
        $this->get('/admin/analytics')->assertNotFound();
        $this->get('/admin/analytics/sales')->assertNotFound();
        $this->get('/admin/analytics/traffic')->assertNotFound();
        $this->get('/admin/analytics/content')->assertNotFound();
    }

    // ===== AnalyticsService: آمار کلی =====

    public function test_key_stats_counts_revenue_and_users(): void
    {
        $user = $this->makeUser();
        $order = $this->makeOrder($user);
        $this->makePayment($order, 500000);
        $this->makePayment($order, 250000, 'manual');

        $stats = app(AnalyticsService::class)->keyStats();

        $this->assertSame(750000.0, $stats['total_revenue']);
        $this->assertSame(1, $stats['total_orders']);
        $this->assertSame(1, $stats['completed_orders']);
        $this->assertSame(2, $stats['total_users']); // نویسنده + خواننده (ادمین در جدول جدا)
        $this->assertSame(0, $stats['open_tickets']);
    }

    public function test_pending_payments_excluded_from_revenue(): void
    {
        $user = $this->makeUser();
        $order = $this->makeOrder($user);
        $this->makePayment($order, 500000);

        Payment::create([
            'user_id' => $user->id,
            'order_id' => $order->id,
            'amount' => 999000,
            'status' => 'pending',
            'payment_method' => 'gateway',
        ]);

        $this->assertSame(500000.0, app(AnalyticsService::class)->keyStats()['total_revenue']);
    }

    // ===== AnalyticsService: نمودارهای ماهانه =====

    public function test_monthly_revenue_returns_six_months(): void
    {
        $chart = app(AnalyticsService::class)->monthlyRevenue(6);

        $this->assertCount(6, $chart['labels']);
        $this->assertCount(6, $chart['data']);
        $this->assertNotEmpty($chart['labels'][0]);
    }

    public function test_monthly_revenue_sums_current_month_payments(): void
    {
        $user = $this->makeUser();
        $order = $this->makeOrder($user);
        $this->makePayment($order, 400000, 'gateway', now());

        $chart = app(AnalyticsService::class)->monthlyRevenue(6);

        $this->assertSame(400000.0, end($chart['data']));
    }

    public function test_monthly_orders_and_users_shape(): void
    {
        $service = app(AnalyticsService::class);

        $ordersChart = $service->monthlyOrders(6);
        $usersChart = $service->monthlyUsers(6);

        $this->assertCount(6, $ordersChart['data']);
        $this->assertCount(6, $usersChart['data']);
    }

    // ===== AnalyticsService: نمودارهای دونات =====

    public function test_orders_by_status_counts_each_status(): void
    {
        $user = $this->makeUser();
        $this->makeOrder($user);

        $chart = app(AnalyticsService::class)->ordersByStatus();

        $this->assertContains(1, $chart['data']);
        $this->assertContains('تکمیل شده', $chart['labels']);
    }

    public function test_payments_by_method_groups_correctly(): void
    {
        $user = $this->makeUser();
        $order = $this->makeOrder($user);
        $this->makePayment($order, 100000, 'gateway');
        $this->makePayment($order, 200000, 'installment');
        $this->makePayment($order, 300000, 'manual');

        $chart = app(AnalyticsService::class)->paymentsByMethod();

        $this->assertSame(['درگاه آنلاین', 'اقساطی', 'دستی'], $chart['labels']);
        $this->assertSame([1, 1, 1], $chart['data']);
        $this->assertSame([100000.0, 200000.0, 300000.0], $chart['amounts']);
    }

    // ===== AnalyticsService: گزارش فروش =====

    public function test_payments_filter_by_date_range(): void
    {
        $user = $this->makeUser();
        $order = $this->makeOrder($user);

        $old = $this->makePayment($order, 100000, 'gateway', now()->subYear());
        $recent = $this->makePayment($order, 200000, 'gateway', now());

        $service = app(AnalyticsService::class);

        $all = $service->payments();
        $this->assertCount(2, $all);

        $filtered = $service->payments(now()->subMonth()->format('Y-m-d'));
        $this->assertCount(1, $filtered);
        $this->assertSame($recent->id, $filtered->first()->id);

        $oldOnly = $service->payments(null, now()->subMonth()->format('Y-m-d'));
        $this->assertCount(1, $oldOnly);
        $this->assertSame($old->id, $oldOnly->first()->id);
    }

    public function test_payments_filter_by_method(): void
    {
        $user = $this->makeUser();
        $order = $this->makeOrder($user);
        $this->makePayment($order, 100000, 'gateway');
        $this->makePayment($order, 200000, 'manual');

        $filtered = app(AnalyticsService::class)->payments(null, null, 'manual');

        $this->assertCount(1, $filtered);
        $this->assertSame('manual', $filtered->first()->payment_method);
    }

    public function test_top_products_aggregates_quantity_and_revenue(): void
    {
        $user = $this->makeUser();
        $order = $this->makeOrder($user);

        OrderItem::create([
            'order_id' => $order->id, 'product_id' => null, 'product_name' => 'قالب A',
            'license_type' => 'single', 'quantity' => 2, 'unit_price' => 100, 'total_price' => 200,
        ]);
        OrderItem::create([
            'order_id' => $order->id, 'product_id' => null, 'product_name' => 'قالب A',
            'license_type' => 'multi', 'quantity' => 3, 'unit_price' => 100, 'total_price' => 300,
        ]);
        OrderItem::create([
            'order_id' => $order->id, 'product_id' => null, 'product_name' => 'قالب B',
            'license_type' => 'single', 'quantity' => 1, 'unit_price' => 100, 'total_price' => 100,
        ]);

        $top = app(AnalyticsService::class)->topProducts();

        $this->assertSame('قالب A', $top->first()->product_name);
        $this->assertSame(5, (int) $top->first()->quantity);
        $this->assertSame(500.0, (float) $top->first()->revenue);
    }

    // ===== AnalyticsService: گزارش ترافیک =====

    public function test_online_users_returns_only_recent(): void
    {
        $user = $this->makeUser();

        UserActivity::create([
            'user_id' => $user->id, 'page' => '/dashboard',
            'last_active_at' => now(),
        ]);
        UserActivity::create([
            'user_id' => $this->author->id, 'page' => '/admin',
            'last_active_at' => now()->subHour(),
        ]);

        $online = app(AnalyticsService::class)->onlineUsers(5);

        $this->assertCount(1, $online);
        $this->assertSame($user->id, $online->first()->user_id);
    }

    public function test_top_pages_orders_by_visits(): void
    {
        UserActivity::create(['user_id' => null, 'page' => '/about', 'last_active_at' => now()]);
        UserActivity::create(['user_id' => null, 'page' => '/contact', 'last_active_at' => now()]);
        UserActivity::create(['user_id' => null, 'page' => '/contact', 'last_active_at' => now()]);

        $pages = app(AnalyticsService::class)->topPages();

        $this->assertSame('/contact', $pages->first()->page);
        $this->assertSame(2, (int) $pages->first()->visits);
    }

    // ===== AnalyticsService: گزارش محتوا =====

    public function test_top_posts_orders_by_views(): void
    {
        $low = BlogPost::create([
            'user_id' => $this->author->id, 'title' => 'کم بازدید', 'slug' => 'low-'.uniqid(),
            'content' => 'متن', 'status' => 'published', 'views' => 5,
        ]);
        $high = BlogPost::create([
            'user_id' => $this->author->id, 'title' => 'پر بازدید', 'slug' => 'high-'.uniqid(),
            'content' => 'متن', 'status' => 'published', 'views' => 500,
        ]);

        $posts = app(AnalyticsService::class)->topPosts();

        $this->assertSame($high->id, $posts->first()->id);
        $this->assertSame($low->id, $posts->last()->id);
    }

    public function test_product_stats_aggregates_sales(): void
    {
        $user = $this->makeUser();
        $order = $this->makeOrder($user);

        OrderItem::create([
            'order_id' => $order->id, 'product_id' => null, 'product_name' => 'قالب',
            'license_type' => 'single', 'quantity' => 4, 'unit_price' => 250, 'total_price' => 1000,
        ]);

        $stats = app(AnalyticsService::class)->productStats();

        $this->assertSame(4, $stats['total_sales']);
        $this->assertSame(1000.0, $stats['sales_revenue']);
    }

    // ===== کامپوننت‌های Livewire =====

    public function test_overview_component_renders_with_stats(): void
    {
        Livewire::actingAs($this->admin, 'admin')
            ->test(AnalyticsOverview::class)
            ->assertOk()
            ->assertViewHas('stats');
    }

    public function test_sales_component_filters_by_method(): void
    {
        $user = $this->makeUser();
        $order = $this->makeOrder($user);
        $this->makePayment($order, 100000, 'gateway');
        $this->makePayment($order, 200000, 'manual');

        Livewire::actingAs($this->admin, 'admin')
            ->test(SalesReport::class)
            ->assertSet('method', '')
            ->set('method', 'manual')
            ->assertViewHas('payments', fn ($payments) => $payments->count() === 1);
    }

    public function test_sales_component_reset_filters(): void
    {
        $user = $this->makeUser();
        $order = $this->makeOrder($user);
        $this->makePayment($order, 100000, 'gateway');
        $this->makePayment($order, 200000, 'manual');

        Livewire::actingAs($this->admin, 'admin')
            ->test(SalesReport::class)
            ->set('method', 'manual')
            ->call('resetFilters')
            ->assertSet('method', '')
            ->assertViewHas('payments', fn ($payments) => $payments->count() === 2);
    }

    public function test_traffic_component_renders_activity(): void
    {
        UserActivity::create([
            'user_id' => $this->author->id, 'page' => '/admin/analytics',
            'last_active_at' => now(),
        ]);

        Livewire::actingAs($this->admin, 'admin')
            ->test(TrafficReport::class)
            ->assertOk()
            ->assertViewHas('onlineUsers');
    }

    public function test_content_component_renders_reports(): void
    {
        Livewire::actingAs($this->admin, 'admin')
            ->test(ContentReport::class)
            ->assertOk()
            ->assertViewHas(['topPosts', 'productStats', 'consultationStats']);
    }
}
