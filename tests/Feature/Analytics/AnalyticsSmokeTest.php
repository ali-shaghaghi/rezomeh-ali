<?php

namespace Tests\Feature\Analytics;

use App\Models\Admin;
use App\Models\User;
use Database\Seeders\DashboardSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Models\BlogPost;
use Modules\Core\Models\Role;
use Modules\Core\Models\UserActivity;
use Modules\Orders\Database\Seeders\OrdersContentSeeder;
use Modules\Orders\Models\Order;
use Modules\Orders\Models\Payment;
use Modules\Products\Models\OrderItem;
use Tests\TestCase;

/**
 * Smoke test — رندر واقعی هر چهار صفحه گزارش با داده نمونه seed شده.
 */
class AnalyticsSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;

    protected User $author;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        $this->seed(OrdersContentSeeder::class);
        $this->seed(DashboardSeeder::class);

        $this->admin = $this->makeAdmin('smoke@test.com', 'super-admin');

        // نویسنده محتوا (کاربر عادی) — فایل‌های وابسته به FK جدول users
        $this->author = User::create([
            'name' => 'Author', 'email' => 'smoke-author@test.com',
            'password' => 'password', 'is_active' => true,
        ]);

        // فعالیت کاربر برای گزارش ترافیک
        UserActivity::create([
            'user_id' => $this->author->id, 'page' => '/admin/dashboard',
            'ip_address' => '127.0.0.1', 'last_active_at' => now(),
        ]);
    }

    public function test_all_analytics_pages_render_with_seeded_data(): void
    {
        $this->actingAs($this->admin, 'admin');

        foreach (['/admin/analytics', '/admin/analytics/sales', '/admin/analytics/traffic', '/admin/analytics/content'] as $url) {
            $response = $this->get($url);
            $this->assertSame(200, $response->status(), "صفحه {$url} خطا داشت");
        }
    }

    public function test_overview_page_contains_all_charts(): void
    {
        $response = $this->actingAs($this->admin, 'admin')->get('/admin/analytics');

        $response->assertOk()
            ->assertSee('revenueChart', false)
            ->assertSee('ordersChart', false)
            ->assertSee('usersChart', false)
            ->assertSee('orderStatusChart', false)
            ->assertSee('paymentMethodChart', false);
    }

    public function test_seeded_blog_posts_appear_in_content_report(): void
    {
        // DashboardSeeder فقط در صورت وجود کاربر پست می‌سازد — اینجا مستقیم می‌سازیم
        $post = BlogPost::create([
            'user_id' => $this->author->id, 'title' => 'مقاله نمونه دودی', 'slug' => 'smoke-'.uniqid(),
            'content' => 'متن', 'status' => 'published', 'views' => 42,
            'published_at' => now(),
        ]);

        $this->actingAs($this->admin, 'admin')
            ->get('/admin/analytics/content')
            ->assertOk()
            ->assertSee($post->title, false);
    }
}
