<?php

namespace Tests\Feature\Errors;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Orders\Models\Order;
use Modules\Orders\Services\OrderService;
use Modules\ProjectDev\Database\Seeders\ProjectDevPermissionSeeder;
use Tests\TestCase;

/**
 * قانون پروژه: هیچ صفحه ارور مجزایی (مثل 403) وجود ندارد؛
 * هر دسترسی غیرمجاز یا مسیر ناموجود فقط صفحه «یافت نشد» (404) را نشان می‌دهد
 * با دکمه‌های «بازگشت به صفحه قبلی» و «بازگشت به صفحه اصلی».
 */
class ErrorPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_unknown_route_renders_404_page_with_back_buttons(): void
    {
        $this->get('/this-route-does-not-exist')
            ->assertNotFound()
            ->assertSee('صفحه موردنظر یافت نشد')
            ->assertSee('بازگشت به صفحه قبلی')
            ->assertSee('بازگشت به صفحه اصلی');
    }

    public function test_plain_user_gets_404_not_403_for_admin_panel(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $this->actingAs($user);

        $this->get('/admin/orders')->assertNotFound();
        $this->get('/admin/projects/types')->assertNotFound();
        $this->get('/admin/tickets')->assertNotFound();
    }

    public function test_guest_gets_404_page_for_unknown_route_and_login_redirect_for_admin(): void
    {
        // مسیر ناموجود → صفحه ۴۰۴
        $this->get('/admin/nothing-here')->assertNotFound();

        // مهمان در پنل ادمین → ریدایرکت به ورود ادمین (/admin/login بدون توکن → 404)
        $this->get('/admin/orders')->assertRedirect('/admin/login');
    }

    public function test_projects_admin_routes_are_not_found_for_plain_user(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $this->actingAs($user);

        $this->get(route('admin.projectdev.requests'))->assertNotFound();
        $this->get(route('admin.projectdev.types'))->assertNotFound();
    }

    public function test_super_admin_can_access_projects_admin_pages(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $this->seed(ProjectDevPermissionSeeder::class);

        // ادمین از جدول جدا (گارد admin)
        $admin = $this->makeAdmin('super-projects@test.com', 'super-admin');
        $this->actingAsAdmin($admin);

        $this->get(route('admin.projectdev.requests'))->assertOk();
        $this->get(route('admin.projectdev.types'))->assertOk();
    }

    public function test_order_not_found_for_other_users_number(): void
    {
        $order = app(OrderService::class)->save(new Order, [
            'user_id' => User::factory()->create()->id,
            'title' => 'سفارش دیگران',
            'amount' => 1000,
        ]);

        $user = User::factory()->create(['is_active' => true]);
        $this->actingAs($user);

        $this->get(route('user.orders.show', $order->order_number))->assertNotFound();
    }
}
