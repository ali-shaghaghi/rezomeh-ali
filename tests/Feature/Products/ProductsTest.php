<?php

namespace Tests\Feature\Products;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\Core\Models\Role;
use Modules\Products\Database\Seeders\ProductsPermissionSeeder;
use Modules\Products\Enums\LicenseType;
use Modules\Products\Enums\ProductType;
use Modules\Products\Livewire\Admin\CategoriesManager;
use Modules\Products\Livewire\Admin\ProductsIndex;
use Modules\Products\Livewire\Public\ProductGrid;
use Modules\Products\Models\Product;
use Modules\Products\Models\ProductCategory;
use Modules\Products\Services\ProductService;
use Tests\TestCase;

/**
 * تست‌های کاتالوگ محصولات + پنل ادمین (گرید، فیلتر، CRUD، دسته‌بندی).
 *
 * CHECKPOINT ۶: /products → گرید با فیلتر؛ /admin/products → مدیریت.
 */
class ProductsTest extends TestCase
{
    use RefreshDatabase;

    protected \App\Models\Admin $admin;

    protected ProductCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        $this->seed(ProductsPermissionSeeder::class);

        $this->admin = $this->makeAdmin('products-admin@test.com', 'super-admin');

        $this->category = ProductCategory::create([
            'name' => 'قالب‌های وب',
            'slug' => 'web-templates',
            'is_active' => true,
        ]);
    }

    private function makeProduct(array $attributes = []): Product
    {
        return Product::create(array_merge([
            'category_id' => $this->category->id,
            'name' => 'قالب تستی',
            'slug' => 'test-template-'.uniqid(),
            'short_description' => 'توضیح کوتاه',
            'price' => 1000000,
            'type' => ProductType::Template->value,
            'version' => '1.0.0',
            'is_published' => true,
        ], $attributes));
    }

    // ===== مدل: قیمت و تخفیف =====

    public function test_final_price_uses_sale_price_when_lower(): void
    {
        $product = $this->makeProduct(['price' => 1000000, 'sale_price' => 800000]);

        $this->assertSame(800000.0, $product->final_price);
        $this->assertTrue($product->has_sale);
        $this->assertSame(20, $product->discount_percent);
    }

    public function test_final_price_ignores_higher_sale_price(): void
    {
        $product = $this->makeProduct(['price' => 1000000, 'sale_price' => 1200000]);

        $this->assertSame(1000000.0, $product->final_price);
        $this->assertFalse($product->has_sale);
    }

    public function test_final_price_without_sale(): void
    {
        $product = $this->makeProduct(['price' => 500000, 'sale_price' => null]);

        $this->assertSame(500000.0, $product->final_price);
        $this->assertFalse($product->has_sale);
        $this->assertSame(0, $product->discount_percent);
    }

    public function test_price_for_license_applies_multiplier(): void
    {
        $product = $this->makeProduct(['price' => 1000000]);

        $single = $product->priceForLicense(LicenseType::Single->value);
        $multi = $product->priceForLicense(LicenseType::Multi->value);
        $lifetime = $product->priceForLicense(LicenseType::Lifetime->value);

        $this->assertSame(1000000.0, $single);
        $this->assertGreaterThan($single, $multi);
        $this->assertGreaterThan($multi, $lifetime);
    }

    public function test_is_downloadable_requires_file_path(): void
    {
        $this->assertTrue($this->makeProduct(['file_path' => 'products/x.zip'])->is_downloadable);
        $this->assertFalse($this->makeProduct(['file_path' => null])->is_downloadable);
    }

    public function test_type_label_attribute(): void
    {
        $product = $this->makeProduct(['type' => ProductType::Crm->value]);

        $this->assertSame(ProductType::Crm->label(), $product->type_label);
    }

    public function test_published_scope(): void
    {
        $this->makeProduct(['is_published' => true]);
        $this->makeProduct(['is_published' => false]);

        $this->assertSame(1, Product::published()->count());
    }

    public function test_show_in_resume_scope(): void
    {
        $this->makeProduct(['show_in_resume' => true, 'is_published' => true]);
        $this->makeProduct(['show_in_resume' => true, 'is_published' => false]);
        $this->makeProduct(['show_in_resume' => false, 'is_published' => true]);

        $this->assertSame(1, Product::showInResume()->count());
    }

    // ===== Enum‌ها =====

    public function test_product_type_enum_cases(): void
    {
        $this->assertSame('template', ProductType::Template->value);
        $this->assertSame('crm', ProductType::Crm->value);
        $this->assertSame('script', ProductType::Script->value);
        $this->assertSame('other', ProductType::Other->value);
    }

    public function test_license_type_enum_multipliers(): void
    {
        $this->assertGreaterThan(LicenseType::Single->multiplier(), LicenseType::Multi->multiplier());
        $this->assertGreaterThan(LicenseType::Multi->multiplier(), LicenseType::Lifetime->multiplier());
    }

    // ===== گرید عمومی + فیلتر =====

    public function test_grid_filters_by_search(): void
    {
        $this->makeProduct(['name' => 'قالب فروشگاهی']);
        $this->makeProduct(['name' => 'CRM مدیریت']);

        Livewire::test(ProductGrid::class)
            ->set('search', 'فروشگاهی')
            ->assertSee('قالب فروشگاهی')
            ->assertDontSee('CRM مدیریت');
    }

    public function test_grid_filters_by_type(): void
    {
        $this->makeProduct(['name' => 'قالب گرید', 'type' => ProductType::Template->value]);
        $this->makeProduct(['name' => 'CRM گرید', 'type' => ProductType::Crm->value]);

        Livewire::test(ProductGrid::class)
            ->set('type', ProductType::Crm->value)
            ->assertSee('CRM گرید')
            ->assertDontSee('قالب گرید');
    }

    public function test_grid_filters_by_category(): void
    {
        $other = ProductCategory::create(['name' => 'اسکریپت', 'slug' => 'scripts', 'is_active' => true]);

        $this->makeProduct(['name' => 'قالب دسته اول']);
        $this->makeProduct(['name' => 'قالب دسته دوم', 'category_id' => $other->id]);

        Livewire::test(ProductGrid::class)
            ->set('category', (string) $this->category->id)
            ->assertSee('قالب دسته اول')
            ->assertDontSee('قالب دسته دوم');
    }

    public function test_grid_hides_unpublished_products(): void
    {
        $this->makeProduct(['name' => 'منتشرشده', 'is_published' => true]);
        $this->makeProduct(['name' => 'منتشرنشده', 'is_published' => false]);

        Livewire::test(ProductGrid::class)
            ->assertSee('منتشرشده')
            ->assertDontSee('منتشرنشده');
    }

    public function test_grid_empty_state(): void
    {
        Livewire::test(ProductGrid::class)
            ->set('search', 'چیزی که وجود ندارد')
            ->assertSee('محصولی با این فیلترها یافت نشد');
    }

    public function test_grid_sort_options(): void
    {
        $this->makeProduct(['name' => 'گران', 'price' => 9000000]);
        $this->makeProduct(['name' => 'ارزان', 'price' => 100000]);

        $component = Livewire::test(ProductGrid::class)->set('sort', 'cheapest');
        $this->assertSame('cheapest', $component->get('sort'));
    }

    // ===== صفحه محصول =====

    public function test_product_show_page_displays_details(): void
    {
        $product = $this->makeProduct([
            'name' => 'قالب جزئیات',
            'description' => 'توضیحات کامل محصول',
            'demo_url' => 'https://demo.example.com',
        ]);

        $this->get(route('public.products.show', $product->slug))
            ->assertOk()
            ->assertSee('قالب جزئیات')
            ->assertSee('توضیحات کامل محصول');
    }

    public function test_unpublished_product_show_returns_404(): void
    {
        $product = $this->makeProduct(['is_published' => false]);

        $this->get(route('public.products.show', $product->slug))->assertNotFound();
    }

    // ===== پنل ادمین =====

    public function test_admin_products_index_requires_permission(): void
    {
        $plain = User::create(['name' => 'User', 'email' => 'plain-products@test.com', 'password' => 'password', 'is_active' => true]);
        $plain->roles()->attach(Role::where('slug', 'user')->firstOrFail());

        $this->actingAs($plain)->get('/admin/products')->assertNotFound();
    }

    public function test_admin_can_view_products_index(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->get('/admin/products')
            ->assertOk()
            ->assertSeeLivewire('admin.products.index');
    }

    public function test_admin_can_create_product(): void
    {
        Livewire::actingAs($this->admin, 'admin')
            ->test(ProductsIndex::class)
            ->call('openCreateForm')
            ->assertSet('showForm', true)
            ->set('name', 'قالب جدید ادمین')
            ->set('price', 2500000)
            ->set('type_field', ProductType::Crm->value)
            ->call('saveProduct')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('products', [
            'name' => 'قالب جدید ادمین',
            'price' => 2500000,
            'type' => ProductType::Crm->value,
        ]);
    }

    public function test_admin_can_edit_product(): void
    {
        $product = $this->makeProduct(['name' => 'نام قدیمی']);

        Livewire::actingAs($this->admin, 'admin')
            ->test(ProductsIndex::class)
            ->call('openEditForm', $product->id)
            ->assertSet('editing', true)
            ->set('name', 'نام جدید')
            ->call('saveProduct')
            ->assertHasNoErrors();

        $this->assertSame('نام جدید', $product->refresh()->name);
    }

    public function test_admin_can_toggle_published(): void
    {
        $product = $this->makeProduct(['is_published' => true]);

        Livewire::actingAs($this->admin, 'admin')
            ->test(ProductsIndex::class)
            ->call('togglePublished', $product->id);

        $this->assertFalse($product->refresh()->is_published);
    }

    public function test_admin_can_toggle_resume_visibility(): void
    {
        $product = $this->makeProduct(['show_in_resume' => false]);

        Livewire::actingAs($this->admin, 'admin')
            ->test(ProductsIndex::class)
            ->call('toggleResume', $product->id);

        $this->assertTrue($product->refresh()->show_in_resume);
    }

    public function test_admin_can_delete_product(): void
    {
        $product = $this->makeProduct();

        Livewire::actingAs($this->admin, 'admin')
            ->test(ProductsIndex::class)
            ->call('confirmDelete', $product->id)
            ->assertSet('confirmingDelete', true)
            ->call('deleteProduct');

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_admin_product_validation_requires_name(): void
    {
        Livewire::actingAs($this->admin, 'admin')
            ->test(ProductsIndex::class)
            ->call('openCreateForm')
            ->set('name', 'ab') // کمتر از ۳ کاراکتر
            ->set('price', 1000)
            ->call('saveProduct')
            ->assertHasErrors(['name']);
    }

    public function test_admin_product_validation_requires_price(): void
    {
        Livewire::actingAs($this->admin, 'admin')
            ->test(ProductsIndex::class)
            ->call('openCreateForm')
            ->set('name', 'نام معتبر')
            ->call('saveProduct')
            ->assertHasErrors(['price']);
    }

    public function test_admin_products_filter_by_search(): void
    {
        $this->makeProduct(['name' => 'قالب جستجو شونده']);

        Livewire::actingAs($this->admin, 'admin')
            ->test(ProductsIndex::class)
            ->set('search', 'جستجو شونده')
            ->assertSee('قالب جستجو شونده');
    }

    // ===== دسته‌بندی‌ها =====

    public function test_admin_can_view_categories_manager(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->get('/admin/products/categories')
            ->assertOk()
            ->assertSeeLivewire('admin.products.categories');
    }

    public function test_admin_can_create_category(): void
    {
        Livewire::actingAs($this->admin, 'admin')
            ->test(CategoriesManager::class)
            ->set('name', 'دسته جدید')
            ->call('saveCategory')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('product_categories', ['name' => 'دسته جدید']);
    }

    public function test_admin_can_edit_category(): void
    {
        Livewire::actingAs($this->admin, 'admin')
            ->test(CategoriesManager::class)
            ->call('editCategory', $this->category->id)
            ->set('name', 'قالب‌های وب ویرایش‌شده')
            ->call('saveCategory')
            ->assertHasNoErrors();

        $this->assertSame('قالب‌های وب ویرایش‌شده', $this->category->refresh()->name);
    }

    public function test_admin_can_toggle_category_active(): void
    {
        Livewire::actingAs($this->admin, 'admin')
            ->test(CategoriesManager::class)
            ->call('toggleActive', $this->category->id);

        $this->assertFalse($this->category->refresh()->is_active);
    }

    public function test_admin_can_delete_category(): void
    {
        $categoryId = $this->category->id;

        Livewire::actingAs($this->admin, 'admin')
            ->test(CategoriesManager::class)
            ->call('confirmDelete', $categoryId)
            ->call('deleteCategory');

        $this->assertDatabaseMissing('product_categories', ['id' => $categoryId]);
    }

    public function test_category_validation_requires_name(): void
    {
        Livewire::actingAs($this->admin, 'admin')
            ->test(CategoriesManager::class)
            ->set('name', 'ا')
            ->call('saveCategory')
            ->assertHasErrors(['name']);
    }

    // ===== ProductService =====

    public function test_service_stats_counts_products(): void
    {
        $this->makeProduct(['is_published' => true, 'show_in_resume' => true, 'type' => ProductType::Template->value]);
        $this->makeProduct(['is_published' => false, 'type' => ProductType::Crm->value]);

        $stats = app(ProductService::class)->stats();

        $this->assertSame(2, $stats['total']);
        $this->assertSame(1, $stats['published']);
        $this->assertSame(1, $stats['templates']);
        $this->assertSame(1, $stats['resume']);
    }

    public function test_service_generates_slug_from_name(): void
    {
        $product = app(ProductService::class)->save(new Product, [
            'name' => 'قالب تستی سرویس',
            'price' => 100000,
            'type' => ProductType::Template->value,
        ]);

        $this->assertNotNull($product->slug);
        $this->assertNotSame('', $product->slug);
    }

    public function test_service_finds_by_slug(): void
    {
        $product = $this->makeProduct(['slug' => 'find-by-slug-test']);

        $found = app(ProductService::class)->findBySlug('find-by-slug-test');

        $this->assertSame($product->id, $found->id);
    }

    public function test_service_delete_removes_product(): void
    {
        $product = $this->makeProduct();

        app(ProductService::class)->delete($product);

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }
}
