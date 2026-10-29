<?php

namespace Tests\Feature\UserPanel;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Models\Role;
use Modules\Orders\Models\Order;
use Modules\Products\Enums\LicenseType;
use Modules\Products\Models\OrderItem;
use Modules\Products\Models\Product;
use Modules\Products\Models\ProductCategory;
use Modules\Products\Services\LicenseService;
use Tests\TestCase;

/**
 * نمایش لایسنس در پنل کاربر بعد از خرید محصول.
 *
 * جریان: پرداخت موفق → ریدایرکت به user.orders.show → کلید لایسنس،
 * وضعیت و دکمه دانلود باید همان‌جا دیده شود؛ صفحه «محصولات من» هم
 * باید از سایدبار در دسترس باشد.
 */
class OrderLicensesTest extends TestCase
{
    use RefreshDatabase;

    protected User $buyer;

    protected User $other;

    protected Product $product;

    protected Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        $this->buyer = User::create(['name' => 'خریدار', 'email' => 'buyer-lic@test.com', 'password' => 'password', 'is_active' => true]);
        $this->buyer->roles()->attach(Role::where('slug', 'user')->firstOrFail());

        $this->other = User::create(['name' => 'دیگری', 'email' => 'other-lic@test.com', 'password' => 'password', 'is_active' => true]);
        $this->other->roles()->attach(Role::where('slug', 'user')->firstOrFail());

        $category = ProductCategory::create(['name' => 'قالب‌ها', 'slug' => 'templates-lic', 'is_active' => true]);

        $this->product = Product::create([
            'category_id' => $category->id,
            'name' => 'قالب پرتفولیو حرفه‌ای',
            'slug' => 'pro-portfolio-template-lic',
            'short_description' => 'قالب قابل دانلود',
            'price' => 800000,
            'type' => 'template',
            'file_path' => 'products/pro-portfolio.zip',
            'version' => '2.0.0',
            'is_published' => true,
        ]);

        $this->order = Order::create([
            'user_id' => $this->buyer->id,
            'order_number' => 'ORD-LIC-1001',
            'title' => 'خرید قالب پرتفولیو حرفه‌ای',
            'description' => 'خرید محصول دانلودی',
            'status' => 'completed',
            'amount' => 800000,
            'paid_amount' => 800000,
        ]);

        $item = OrderItem::create([
            'order_id' => $this->order->id,
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'license_type' => LicenseType::Single->value,
            'quantity' => 2,
            'unit_price' => 400000,
            'total_price' => 800000,
        ]);

        $service = app(LicenseService::class);
        $service->issue($this->buyer, $this->product, LicenseType::Single, $item->id);
        $service->issue($this->buyer, $this->product, LicenseType::Single, $item->id);
    }

    public function test_order_page_shows_license_keys_and_download_after_purchase(): void
    {
        $response = $this->actingAs($this->buyer)
            ->get(route('user.orders.show', $this->order->order_number));

        $response->assertOk();
        $response->assertSee('لایسنس‌های خریداری‌شده');
        $response->assertSee('قالب پرتفولیو حرفه‌ای');

        foreach ($this->order->items->first()->licenses as $license) {
            $response->assertSee($license->license_key);
            $response->assertSee('دانلود');
        }
    }

    public function test_foreign_user_cannot_see_licenses_of_someone_elses_order(): void
    {
        $this->actingAs($this->other)
            ->get(route('user.orders.show', $this->order->order_number))
            ->assertNotFound();
    }

    public function test_guest_is_redirected_from_order_licenses(): void
    {
        $this->get(route('user.orders.show', $this->order->order_number))
            ->assertRedirect(route('auth.login'));
    }

    public function test_sidebar_links_to_my_products_page(): void
    {
        // سایدبار شل ریل‌تایم شامل تب «محصولات من» است (دکمه wire:click — بدون href)
        $response = $this->actingAs($this->buyer)
            ->get(route('user.dashboard', [], false).'?tab=orders');

        $response->assertOk();
        $response->assertSeeLivewire('userpanel.panel-shell');
        $response->assertSee('محصولات من (لایسنس‌ها)');
    }

    public function test_my_products_page_shows_purchased_licenses(): void
    {
        $response = $this->actingAs($this->buyer)
            ->get(route('user.products.index'));

        $response->assertOk();

        foreach ($this->order->items->first()->licenses as $license) {
            $response->assertSee($license->license_key);
        }
    }
}
