<?php

namespace Tests\Feature\Products;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use Modules\Core\Models\Role;
use Modules\Orders\Models\Order;
use Modules\Products\Enums\LicenseType;
use Modules\Products\Enums\ProductType;
use Modules\Products\Livewire\UserPanel\MyProducts;
use Modules\Products\Models\Download;
use Modules\Products\Models\License;
use Modules\Products\Models\Product;
use Modules\Products\Models\ProductCategory;
use Modules\Products\Services\DownloadService;
use Modules\Products\Services\LicenseService;
use Tests\TestCase;

/**
 * تست‌های محصولات من — کارت محصول + کلید لایسنس + دکمه دانلود.
 *
 * CHECKPOINT ۶: پرداخت موفق → /dashboard/products → دانلود کار کند + لاگ در downloads.
 */
class MyProductsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        $this->user = User::create([
            'name' => 'خریدار محصولات',
            'email' => 'myproducts@test.com',
            'phone' => '09121234567',
            'password' => 'password',
            'is_active' => true,
        ]);
        $this->user->roles()->attach(Role::where('slug', 'user')->firstOrFail());

        $category = ProductCategory::create(['name' => 'قالب', 'slug' => 'tpl', 'is_active' => true]);

        $this->product = Product::create([
            'category_id' => $category->id,
            'name' => 'قالب تستی',
            'slug' => 'test-template',
            'price' => 1000000,
            'type' => ProductType::Template->value,
            'file_path' => 'products/test.zip',
            'version' => '1.0.0',
            'is_published' => true,
        ]);
    }

    private function createLicense(LicenseType $type = LicenseType::Lifetime): License
    {
        return app(LicenseService::class)->issue($this->user, $this->product, $type);
    }

    // ===== دسترسی صفحه =====

    public function test_guest_is_redirected_from_my_products(): void
    {
        $this->get(route('user.products.index'))
            ->assertRedirect(route('auth.login'));
    }

    public function test_authenticated_user_can_view_my_products(): void
    {
        $this->createLicense();

        Livewire::actingAs($this->user)
            ->test(MyProducts::class)
            ->assertStatus(200);
    }

    public function test_my_products_page_renders(): void
    {
        $this->createLicense();

        $this->actingAs($this->user)
            ->get(route('user.products.index'))
            ->assertOk()
            ->assertSee('قالب تستی');
    }

    // ===== نمایش لایسنس‌ها =====

    public function test_widget_lists_user_licenses(): void
    {
        $license = $this->createLicense();

        Livewire::actingAs($this->user)
            ->test(MyProducts::class)
            ->assertViewHas('licenses', fn ($licenses) => $licenses->count() === 1
                && $licenses->first()->id === $license->id);
    }

    public function test_widget_shows_license_key(): void
    {
        $license = $this->createLicense();

        Livewire::actingAs($this->user)
            ->test(MyProducts::class)
            ->assertSee($license->license_key);
    }

    public function test_widget_shows_empty_state_when_no_purchases(): void
    {
        Livewire::actingAs($this->user)
            ->test(MyProducts::class)
            ->assertSee('هنوز محصولی خریداری نکرده‌اید');
    }

    public function test_widget_shows_stats(): void
    {
        $this->createLicense(LicenseType::Lifetime);
        $this->createLicense(LicenseType::Single);

        Livewire::actingAs($this->user)
            ->test(MyProducts::class)
            ->assertViewHas('validLicenses', 2)
            ->assertViewHas('totalDownloads', 0);
    }

    public function test_other_users_licenses_are_not_shown(): void
    {
        $this->createLicense();

        $other = User::create([
            'name' => 'کاربر دیگر',
            'email' => 'other@test.com',
            'phone' => '09120000000',
            'password' => 'password',
            'is_active' => true,
        ]);
        $other->roles()->attach(Role::where('slug', 'user')->firstOrFail());

        $otherLicense = app(LicenseService::class)->issue($other, $this->product, LicenseType::Single);

        Livewire::actingAs($this->user)
            ->test(MyProducts::class)
            ->assertDontSee($otherLicense->license_key);
    }

    // ===== دانلود =====

    public function test_signed_download_url_is_generated(): void
    {
        $license = $this->createLicense();

        $url = app(DownloadService::class)->signedUrl($this->user, $this->product, $license);

        $this->assertStringContainsString('/dashboard/products/test-template/download', $url);
        $this->assertStringContainsString('signature=', $url);
        $this->assertStringContainsString('expires=', $url);
    }

    public function test_download_logs_to_downloads_table(): void
    {
        $license = $this->createLicense();

        $this->assertDatabaseCount('downloads', 0);

        // ساخت لینک امقاشده نباید لاگ ثبت کند (فقط دانلود واقعی)
        $url = app(DownloadService::class)->signedUrl($this->user, $this->product, $license);
        $this->assertDatabaseCount('downloads', 0);

        // دانلود واقعی → لاگ
        $this->actingAs($this->user)->get($url);

        $this->assertDatabaseCount('downloads', 1);
        $this->assertDatabaseHas('downloads', [
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
        ]);
    }

    public function test_download_requires_valid_signature(): void
    {
        $this->createLicense();

        // کانونشن پروژه: ۴۰۳ → ۴۰۴ (bootstrap/app.php)
        $this->actingAs($this->user)
            ->get(route('user.products.download', ['slug' => $this->product->slug]).'?signature=invalid')
            ->assertNotFound();
    }

    public function test_download_denied_without_license(): void
    {
        // کاربر لایسنسی ندارد
        $url = URL::temporarySignedRoute(
            'user.products.download',
            now()->addHour(),
            ['slug' => $this->product->slug]
        );

        // کانونشن پروژه: ۴۰۳ → ۴۰۴ (bootstrap/app.php)
        $this->actingAs($this->user)
            ->get($url)
            ->assertNotFound();
    }

    public function test_widget_exposes_download_url_for_licensed_product(): void
    {
        $this->createLicense();

        Livewire::actingAs($this->user)
            ->test(MyProducts::class)
            ->assertViewHas('licenses', fn ($licenses) => $licenses->first()->download_url !== null);
    }

    public function test_download_count_reflected_in_stats(): void
    {
        $license = $this->createLicense();

        $url = app(DownloadService::class)->signedUrl($this->user, $this->product, $license);

        // ساخت لینک دانلود لاگ ثبت نمی‌کند
        Livewire::actingAs($this->user)
            ->test(MyProducts::class)
            ->assertViewHas('totalDownloads', 0);

        // دانلود واقعی
        $this->actingAs($this->user)->get($url);

        Livewire::actingAs($this->user)
            ->test(MyProducts::class)
            ->assertViewHas('totalDownloads', 1);

        $this->assertSame(1, Download::where('user_id', $this->user->id)->count());
    }

    public function test_has_access_check(): void
    {
        $service = app(DownloadService::class);

        $this->assertFalse($service->hasAccess($this->user, $this->product));

        $this->createLicense();

        $this->assertTrue($service->hasAccess($this->user, $this->product));
    }

    // ===== لایسنس =====

    public function test_license_key_is_uuid_format(): void
    {
        $license = $this->createLicense();

        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $license->license_key
        );
    }

    public function test_license_max_activations_by_type(): void
    {
        $this->assertSame(1, LicenseType::Single->maxActivations());
        $this->assertSame(5, LicenseType::Multi->maxActivations());
        $this->assertSame(999, LicenseType::Lifetime->maxActivations());
    }

    public function test_lifetime_license_never_expires(): void
    {
        $license = $this->createLicense(LicenseType::Lifetime);

        $this->assertNull($license->expires_at);
        $this->assertTrue($license->is_valid);
    }

    public function test_single_license_expires(): void
    {
        $license = $this->createLicense(LicenseType::Single);

        $this->assertNotNull($license->expires_at);
        $this->assertTrue($license->is_valid);
    }

    public function test_license_activation_increments_count(): void
    {
        $license = $this->createLicense(LicenseType::Multi);

        $service = app(LicenseService::class);

        $this->assertTrue($service->activate($license));
        $this->assertSame(1, (int) $license->fresh()->activations_count);

        $this->assertTrue($service->activate($license));
        $this->assertSame(2, (int) $license->fresh()->activations_count);
    }

    public function test_license_activation_fails_when_exhausted(): void
    {
        $license = $this->createLicense(LicenseType::Single);

        $service = app(LicenseService::class);

        $this->assertTrue($service->activate($license));
        $this->assertFalse($service->activate($license));
        $this->assertSame(1, (int) $license->fresh()->activations_count);
    }

    // ===== جریان کامل خرید → محصولات من =====

    public function test_purchase_creates_license_visible_in_my_products(): void
    {
        Order::create([
            'user_id' => $this->user->id,
            'order_number' => 'ORD-TEST-001',
            'title' => 'خرید محصول',
            'status' => 'completed',
            'amount' => 1090000,
            'paid_amount' => 1090000,
        ]);

        $license = app(LicenseService::class)->issue($this->user, $this->product, LicenseType::Single);

        $this->assertNotNull($license);

        Livewire::actingAs($this->user)
            ->test(MyProducts::class)
            ->assertSee($license->license_key);
    }
}
