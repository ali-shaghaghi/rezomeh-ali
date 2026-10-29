<?php

namespace Tests\Feature\Products;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\Core\Models\Role;
use Modules\Orders\Models\Order;
use Modules\Orders\Models\Payment;
use Modules\Products\Database\Seeders\ProductsPermissionSeeder;
use Modules\Products\Enums\LicenseType;
use Modules\Products\Livewire\Admin\CategoriesManager;
use Modules\Products\Livewire\Admin\ProductsIndex;
use Modules\Products\Livewire\Checkout\Step1Order;
use Modules\Products\Livewire\Checkout\Step2Personal;
use Modules\Products\Livewire\Checkout\Step3Invoice;
use Modules\Products\Livewire\Checkout\Step4Payment;
use Modules\Products\Livewire\Public\AddToCartButton;
use Modules\Products\Livewire\Public\CartDrawer;
use Modules\Products\Livewire\Public\ProductGrid;
use Modules\Products\Livewire\UserPanel\MyProducts;
use Modules\Products\Models\Cart;
use Modules\Products\Models\CartItem;
use Modules\Products\Models\Download;
use Modules\Products\Models\License;
use Modules\Products\Models\OrderItem;
use Modules\Products\Models\Product;
use Modules\Products\Models\ProductCategory;
use Modules\Products\Services\CartService;
use Modules\Products\Services\CheckoutService;
use Tests\TestCase;

/**
 * گزارش: یک خرید کامل از صفر — از گرید محصول تا لایسنس و دانلود.
 *
 * جریان تست‌شده:
 *   /products (گرید + فیلتر) → افزودن به سبد (toast + badge) → CartDrawer
 *   → /checkout/step-1..4 (Livewire بدون reload) → پرداخت موفق
 *   → Order + OrderItem + License + Payment + سبد خالی → /dashboard/products
 *   → دانلود با لینک امضاشده + لاگ downloads
 */
class ProductsPurchaseTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Product $product;

    protected ProductCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        $this->seed(ProductsPermissionSeeder::class);

        $superRole = Role::where('slug', 'super-admin')->firstOrFail();
        $this->user = User::create([
            'name' => 'خریدار تست',
            'email' => 'buyer@test.com',
            'password' => 'password',
            'is_active' => true,
        ]);
        $this->user->roles()->attach($superRole);

        $this->category = ProductCategory::create([
            'name' => 'قالب‌های وب',
            'slug' => 'web-templates',
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'category_id' => $this->category->id,
            'name' => 'قالب فروشگاهی رزومه',
            'slug' => 'resume-shop-template',
            'short_description' => 'قالب فروشگاهی با پنل کاربری',
            'price' => 1000000,
            'type' => 'template',
            'file_path' => 'products/resume-shop.zip',
            'version' => '1.0.0',
            'is_published' => true,
            'show_in_resume' => true,
        ]);
    }

    // ===== ۶.۶ روتینگ =====

    public function test_products_routes_are_registered(): void
    {
        $this->assertNotNull(route('public.products.index'));
        $this->assertNotNull(route('public.products.show', $this->product->slug));
        $this->assertNotNull(route('public.cart.index'));
        $this->assertNotNull(route('checkout.step1'));
        $this->assertNotNull(route('checkout.step2'));
        $this->assertNotNull(route('checkout.step3'));
        $this->assertNotNull(route('checkout.step4'));
        $this->assertNotNull(route('admin.products.index'));
        $this->assertNotNull(route('user.products.index'));
    }

    public function test_guest_can_view_products_grid(): void
    {
        $response = $this->get(route('public.products.index'));
        $response->assertOk();
        $response->assertSee('قالب فروشگاهی رزومه');
    }

    public function test_guest_can_view_single_product(): void
    {
        $response = $this->get(route('public.products.show', $this->product->slug));
        $response->assertOk();
        $response->assertSee('قالب فروشگاهی رزومه');
    }

    public function test_guest_cannot_access_checkout(): void
    {
        $this->get(route('checkout.step1'))->assertRedirect(route('auth.login'));
    }

    // ===== CHECKPOINT: گرید با فیلتر =====

    public function test_product_grid_renders_published_products(): void
    {
        Livewire::test(ProductGrid::class)
            ->assertSee('قالب فروشگاهی رزومه');
    }

    public function test_product_grid_search_filter(): void
    {
        Product::create([
            'name' => 'CRM مدیریت مشتریان',
            'slug' => 'customer-crm',
            'price' => 5000000,
            'type' => 'crm',
            'is_published' => true,
        ]);

        Livewire::test(ProductGrid::class)
            ->set('search', 'فروشگاهی')
            ->assertSee('قالب فروشگاهی رزومه')
            ->assertDontSee('CRM مدیریت مشتریان');
    }

    public function test_product_grid_type_filter(): void
    {
        Product::create([
            'name' => 'CRM مدیریت مشتریان',
            'slug' => 'customer-crm',
            'price' => 5000000,
            'type' => 'crm',
            'is_published' => true,
        ]);

        Livewire::test(ProductGrid::class)
            ->set('type', 'crm')
            ->assertSee('CRM مدیریت مشتریان')
            ->assertDontSee('قالب فروشگاهی رزومه');
    }

    public function test_product_grid_hides_unpublished_products(): void
    {
        Product::create([
            'name' => 'محصول منتشرنشده',
            'slug' => 'unpublished',
            'price' => 1000,
            'is_published' => false,
        ]);

        Livewire::test(ProductGrid::class)
            ->assertDontSee('محصول منتشرنشده');
    }

    // ===== CHECKPOINT: افزودن به سبد → toast + badge =====

    public function test_add_to_cart_button_adds_product(): void
    {
        Livewire::test(AddToCartButton::class, ['productId' => $this->product->id])
            ->call('addToCart')
            ->assertDispatched('product-added-to-cart');

        $this->assertEquals(1, app(CartService::class)->count());
        $this->assertDatabaseHas('cart_items', [
            'product_id' => $this->product->id,
            'quantity' => 1,
        ]);
    }

    public function test_add_to_cart_twice_increments_quantity(): void
    {
        $cartService = app(CartService::class);

        $cartService->add($this->product->id);
        $cartService->add($this->product->id);

        $this->assertEquals(2, $cartService->count());
        $this->assertEquals(1, CartItem::count());
    }

    public function test_add_to_cart_with_license_type_uses_multiplier(): void
    {
        Livewire::test(AddToCartButton::class, ['productId' => $this->product->id])
            ->set('licenseType', LicenseType::Lifetime->value)
            ->call('addToCart');

        $item = CartItem::first();
        // lifetime ضریب ۳ دارد (config)
        $expected = $this->product->priceForLicense(LicenseType::Lifetime->value);
        $this->assertEquals($expected, (float) $item->unit_price);
    }

    // ===== CHECKPOINT: Drawer سبد =====

    public function test_cart_drawer_opens_on_event(): void
    {
        Livewire::test(CartDrawer::class)
            ->assertSet('open', false)
            ->dispatch('product-added-to-cart', message: 'x')
            ->assertSet('open', true);
    }

    /**
     * Regression: drawer anchored at left-0 must hide with -translate-x-full when closed.
     * translate-x-full (positive) slides a max-w-md drawer RIGHT instead of off-screen,
     * so it sits in the middle of the page and stays fully visible.
     */
    public function test_cart_drawer_hidden_off_screen_when_closed(): void
    {
        $closed = Livewire::test(CartDrawer::class)->html();

        $this->assertStringContainsString('-translate-x-full', $closed);
        $this->assertStringNotContainsString('>translate-x-full<', $closed);
    }

    public function test_cart_drawer_shows_items_and_subtotal(): void
    {
        app(CartService::class)->add($this->product->id);

        Livewire::test(CartDrawer::class)
            ->assertSee('قالب فروشگاهی رزومه')
            ->assertSee(fa_digits(number_format($this->product->price)));
    }

    public function test_cart_drawer_shows_empty_state(): void
    {
        Livewire::test(CartDrawer::class)
            ->assertSee('سبد خرید شما خالی است');
    }

    public function test_cart_drawer_increment_and_remove(): void
    {
        $cartService = app(CartService::class);
        $cartService->add($this->product->id);
        $itemId = $cartService->cart()->items()->first()->id;

        Livewire::test(CartDrawer::class)
            ->call('increment', $itemId)
            ->call('decrement', $itemId)
            ->call('removeItem', $itemId);

        $this->assertEquals(0, $cartService->count());
    }

    public function test_cart_drawer_checkout_redirects_to_cart_page(): void
    {
        app(CartService::class)->add($this->product->id);

        Livewire::test(CartDrawer::class)
            ->call('checkout')
            ->assertRedirect(route('public.cart.index'));
    }

    public function test_cart_drawer_checkout_redirects_to_cart_when_authenticated(): void
    {
        app(CartService::class)->add($this->product->id);

        Livewire::actingAs($this->user)
            ->test(CartDrawer::class)
            ->call('checkout')
            ->assertRedirect(route('public.cart.index'));
    }

    // ===== CHECKPOINT: Progress Stepper در ۴ مرحله =====

    public function test_progress_stepper_renders_in_step1(): void
    {
        $this->actingAs($this->user);
        app(CartService::class)->add($this->product->id);

        $this->get(route('checkout.step1'))
            ->assertOk()
            ->assertSee('مرحله ۱')
            ->assertViewIs('products::checkout.step1');
    }

    public function test_step1_renders_cart_items_and_totals(): void
    {
        $this->actingAs($this->user);
        app(CartService::class)->add($this->product->id);

        Livewire::test(Step1Order::class)
            ->assertSee('قالب فروشگاهی رزومه')
            ->assertSee('بازبینی سفارش')
            ->assertSee('مالیات'); // اشاره به محاسبه مالیات
    }

    public function test_step1_license_change_updates_price(): void
    {
        $this->actingAs($this->user);
        $cartService = app(CartService::class);
        $cartService->add($this->product->id);
        $itemId = $cartService->cart()->items()->first()->id;

        Livewire::test(Step1Order::class)
            ->call('setLicense', $itemId, LicenseType::Lifetime->value);

        $item = $cartService->cart()->items()->first();
        $this->assertEquals(LicenseType::Lifetime->value, $item->license_type);
        $this->assertEquals(
            $this->product->priceForLicense(LicenseType::Lifetime->value),
            (float) $item->unit_price
        );
    }

    public function test_step1_invalid_discount_code_shows_error(): void
    {
        $this->actingAs($this->user);
        app(CartService::class)->add($this->product->id);

        Livewire::test(Step1Order::class)
            ->set('discountCode', 'INVALID99')
            ->call('applyDiscount')
            ->assertSet('errorMessage', 'کد تخفیف نامعتبر است.');
    }

    public function test_step1_valid_discount_code_applies(): void
    {
        $this->actingAs($this->user);
        app(CartService::class)->add($this->product->id);

        Livewire::test(Step1Order::class)
            ->set('discountCode', 'SAVE10')
            ->call('applyDiscount');

        $totals = app(CheckoutService::class)->totals();
        $this->assertEquals('SAVE10', $totals['discount_code']);
        $this->assertEquals(10, $totals['discount_percent']);
        $this->assertEquals(100000, $totals['discount_amount']); // 10% of 1,000,000
    }

    public function test_step1_remove_discount(): void
    {
        $this->actingAs($this->user);
        app(CartService::class)->add($this->product->id);

        $checkout = app(CheckoutService::class);
        $checkout->applyDiscount('SAVE10');

        Livewire::test(Step1Order::class)
            ->call('removeDiscount');

        $this->assertEquals('', $checkout->totals()['discount_code']);
    }

    // ===== مرحله ۲: اطلاعات فردی =====

    public function test_step2_prefills_from_user_profile(): void
    {
        $this->actingAs($this->user);

        Livewire::test(Step2Personal::class)
            ->assertSet('name', 'خریدار تست')
            ->assertSet('email', 'buyer@test.com');
    }

    public function test_step2_validation_requires_name_and_phone(): void
    {
        $this->actingAs($this->user);

        Livewire::test(Step2Personal::class)
            ->set('name', '')
            ->set('phone', '123')
            ->call('nextStep')
            ->assertHasErrors(['name', 'phone']);
    }

    public function test_step2_saves_personal_info_and_advances(): void
    {
        $this->actingAs($this->user);

        Livewire::test(Step2Personal::class)
            ->set('name', 'علی شقاقی')
            ->set('phone', '09121234567')
            ->call('nextStep')
            ->assertRedirect(route('checkout.step3'));

        $this->assertEquals('علی شقاقی', app(CheckoutService::class)->state()['personal']['name']);
    }

    // ===== مرحله ۳: صورت‌حساب + مالیات ۹٪ =====

    public function test_step3_invoice_calculates_tax_correctly(): void
    {
        $this->actingAs($this->user);
        app(CartService::class)->add($this->product->id); // 1,000,000

        $totals = app(CheckoutService::class)->totals();

        $this->assertEquals(1000000, $totals['subtotal']);
        $this->assertEquals(9, $totals['tax_percent']);
        $this->assertEquals(90000, $totals['tax_amount']);
        $this->assertEquals(1090000, $totals['total']);
    }

    public function test_step3_invoice_with_discount_and_tax(): void
    {
        $this->actingAs($this->user);
        $checkout = app(CheckoutService::class);
        app(CartService::class)->add($this->product->id);
        $checkout->applyDiscount('SAVE10');

        $totals = $checkout->totals();

        // 1,000,000 - 10% = 900,000 → +9% tax = 981,000
        $this->assertEquals(100000, $totals['discount_amount']);
        $this->assertEquals(900000, $totals['after_discount']);
        $this->assertEquals(81000, $totals['tax_amount']);
        $this->assertEquals(981000, $totals['total']);
    }

    public function test_step3_selects_payment_method(): void
    {
        $this->actingAs($this->user);
        app(CartService::class)->add($this->product->id);

        Livewire::test(Step3Invoice::class)
            ->call('selectMethod', 'installment')
            ->assertSet('paymentMethod', 'installment');

        $this->assertEquals('installment', app(CheckoutService::class)->state()['payment_method']);
    }

    public function test_step3_renders_invoice_table(): void
    {
        $this->actingAs($this->user);
        app(CartService::class)->add($this->product->id);

        Livewire::test(Step3Invoice::class)
            ->assertSee('صورت‌حساب')
            ->assertSee('مالیات بر ارزش افزوده')
            ->assertSee('پرداخت اقساطی');
    }

    // ===== CHECKPOINT: پرداخت موفق → /dashboard/products =====

    public function test_step4_gateway_payment_completes_purchase(): void
    {
        $this->actingAs($this->user);
        $cartService = app(CartService::class);
        $cartService->add($this->product->id);

        $checkout = app(CheckoutService::class);
        $checkout->setPersonalInfo(['name' => 'علی شقاقی', 'phone' => '09121234567']);
        $checkout->setPaymentMethod('gateway');

        Livewire::test(Step4Payment::class)
            ->call('pay')
            ->assertRedirect(route('user.products.index'));

        // ۱) سفارش ساخته شده
        $order = Order::where('user_id', $this->user->id)->first();
        $this->assertNotNull($order);
        $this->assertEquals(1090000, (float) $order->amount);
        $this->assertEquals('completed', $order->status);
        $this->assertStringStartsWith('ORD-', $order->order_number);

        // ۲) OrderItem ساخته شده
        $orderItem = OrderItem::where('order_id', $order->id)->first();
        $this->assertNotNull($orderItem);
        $this->assertEquals($this->product->id, $orderItem->product_id);
        $this->assertEquals('قالب فروشگاهی رزومه', $orderItem->product_name);

        // ۳) لایسنس UUID ساخته شده
        $license = License::where('user_id', $this->user->id)->first();
        $this->assertNotNull($license);
        $this->assertEquals(36, strlen($license->license_key)); // UUID v4
        $this->assertEquals(LicenseType::Single->value, $license->type);

        // ۴) پرداخت ثبت شده
        $payment = Payment::where('order_id', $order->id)->first();
        $this->assertNotNull($payment);
        $this->assertEquals('completed', $payment->status);
        $this->assertEquals(1090000, (float) $payment->amount);

        // ۵) سبد خالی شده
        $this->assertEquals(0, $cartService->count());
        $this->assertEquals(0, Cart::count());
    }

    public function test_step4_manual_payment_creates_pending_order(): void
    {
        $this->actingAs($this->user);
        app(CartService::class)->add($this->product->id);

        $checkout = app(CheckoutService::class);
        $checkout->setPaymentMethod('manual');

        Livewire::test(Step4Payment::class)
            ->call('pay');

        $order = Order::where('user_id', $this->user->id)->first();
        $this->assertEquals('pending', $order->status);
        $this->assertEquals(0, (float) $order->paid_amount);

        // پرداخت دستی به‌صورت pending ثبت می‌شود — منتظر تأیید مدیر (PaymentService مشترک)
        $payment = $order->payments()->first();
        $this->assertNotNull($payment);
        $this->assertEquals('pending', $payment->status);
        $this->assertEquals('manual', $payment->payment_method);
    }

    public function test_step4_installment_payment_creates_installments(): void
    {
        $this->actingAs($this->user);
        app(CartService::class)->add($this->product->id);

        $checkout = app(CheckoutService::class);
        $checkout->setPaymentMethod('installment');

        Livewire::test(Step4Payment::class)
            ->call('pay');

        $order = Order::where('user_id', $this->user->id)->first();
        $installments = \Modules\ProjectDev\Models\OrderInstallment::where('order_id', $order->id)->get();

        $this->assertEquals(3, $installments->count());
        $this->assertEquals([30, 30, 40], $installments->pluck('percent')->all());
        $this->assertEquals('paid', $installments->first()->status);
        // ۳۰٪ از ۱,۰۹۰,۰۰۰
        $this->assertEquals(327000, (float) $installments->first()->amount);
    }

    public function test_step4_multiple_quantities_issue_multiple_licenses(): void
    {
        $this->actingAs($this->user);
        $cartService = app(CartService::class);
        $cartService->add($this->product->id, LicenseType::Single->value, 3);

        Livewire::test(Step4Payment::class)->call('pay');

        // ۳ لایسنس مجزا با کلیدهای یکتا
        $licenses = License::where('user_id', $this->user->id)->get();
        $this->assertEquals(3, $licenses->count());

        $keys = $licenses->pluck('license_key')->all();
        $this->assertEquals(count($keys), count(array_unique($keys)));

        // OrderItem یک رکورد است با quantity=3
        $orderItem = OrderItem::first();
        $this->assertEquals(3, $orderItem->quantity);
    }

    public function test_step4_empty_cart_fails(): void
    {
        $this->actingAs($this->user);

        Livewire::test(Step4Payment::class)
            ->call('pay')
            ->assertSet('errorMessage', 'سبد خرید شما خالی است.');
    }

    // ===== CHECKPOINT: MyProducts — دانلود + کلید =====

    public function test_my_products_page_requires_auth(): void
    {
        $this->get(route('user.products.index'))->assertRedirect(route('auth.login'));
    }

    public function test_my_products_lists_purchased_products_with_license_key(): void
    {
        $this->actingAs($this->user);

        // شبیه‌سازی خرید موفق
        app(CartService::class)->add($this->product->id);
        $checkout = app(CheckoutService::class);
        $checkout->setPaymentMethod('gateway');
        $order = $checkout->complete($this->user);

        Livewire::test(MyProducts::class)
            ->assertSee('قالب فروشگاهی رزومه')
            ->assertSee('کلید لایسنس');

        // کلید لایسنس باید در صفحه نمایش داده شود
        $license = License::first();
        $this->get(route('user.products.index'))
            ->assertSee($license->license_key);
    }

    public function test_my_products_shows_empty_state(): void
    {
        $this->actingAs($this->user);

        Livewire::test(MyProducts::class)
            ->assertSee('هنوز محصولی خریداری نکرده‌اید');
    }

    public function test_my_products_shows_download_link_for_downloadable_products(): void
    {
        $this->actingAs($this->user);

        app(CartService::class)->add($this->product->id);
        $checkout = app(CheckoutService::class);
        $checkout->setPaymentMethod('gateway');
        $checkout->complete($this->user);

        $component = Livewire::test(MyProducts::class);
        $license = $component->viewData('licenses')->getCollection()->first();

        $this->assertNotNull($license->download_url);
        $this->assertStringContainsString('/dashboard/products/resume-shop-template/download', $license->download_url);
    }

    public function test_my_products_hides_download_for_non_downloadable(): void
    {
        $this->actingAs($this->user);

        $this->product->update(['file_path' => null]);

        app(CartService::class)->add($this->product->id);
        $checkout = app(CheckoutService::class);
        $checkout->setPaymentMethod('gateway');
        $checkout->complete($this->user);

        $component = Livewire::test(MyProducts::class);
        $license = $component->viewData('licenses')->getCollection()->first();

        $this->assertNull($license->download_url);
    }

    // ===== CHECKPOINT: دانلود کار کند + لاگ در downloads =====

    public function test_download_logs_to_downloads_table(): void
    {
        $this->actingAs($this->user);

        // لایسنس مستقیم بساز (شبیه‌سازی خرید)
        $license = License::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'license_key' => \Illuminate\Support\Str::uuid()->toString(),
            'type' => LicenseType::Single->value,
            'max_activations' => 1,
            'activations_count' => 0,
        ]);

        // ساخت لینک امضاشده
        $url = app(\Modules\Products\Services\DownloadService::class)
            ->signedUrl($this->user, $this->product, $license);

        // دانلود واقعی → لاگ در downloads ثبت می‌شود
        $this->get($url);

        $this->assertEquals(1, Download::where('user_id', $this->user->id)->count());
        $this->assertStringContainsString('signature=', $url);
    }

    public function test_download_with_invalid_signature_is_forbidden(): void
    {
        $this->actingAs($this->user);

        License::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'license_key' => \Illuminate\Support\Str::uuid()->toString(),
            'type' => LicenseType::Single->value,
            'max_activations' => 1,
            'activations_count' => 0,
        ]);

        $url = route('user.products.download', $this->product->slug).'?signature=invalid';

        $this->get($url)->assertNotFound();
    }

    public function test_download_without_license_is_forbidden(): void
    {
        $this->actingAs($this->user);

        $url = app(\Modules\Products\Services\DownloadService::class)
            ->signedUrl($this->user, $this->product, null);

        // کاربر لایسنس ندارد
        $this->get($url)->assertNotFound();
    }

    public function test_download_service_has_access_check(): void
    {
        $downloadService = app(\Modules\Products\Services\DownloadService::class);

        // کاربر لایسنس ندارد
        $this->assertFalse($downloadService->hasAccess($this->user, $this->product));

        License::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'license_key' => \Illuminate\Support\Str::uuid()->toString(),
            'type' => LicenseType::Single->value,
            'max_activations' => 1,
            'activations_count' => 0,
        ]);

        $this->assertTrue($downloadService->hasAccess($this->user, $this->product));
    }

    // ===== ادمین: ProductsIndex + CategoriesManager =====

    public function test_admin_products_index_requires_permission(): void
    {
        $regularUser = User::create([
            'name' => 'کاربر عادی',
            'email' => 'regular@test.com',
            'password' => 'password',
            'is_active' => true,
        ]);

        $this->actingAs($regularUser)
            ->get(route('admin.products.index'))
            ->assertNotFound();
    }

    public function test_admin_products_index_renders(): void
    {
        // دسترسی پنل ادمین فقط از طریق گارد admin
        $this->actingAs($this->makeAdmin('purchase-admin@test.com', 'super-admin'), 'admin');

        $this->get(route('admin.products.index'))
            ->assertOk()
            ->assertSee('مدیریت محصولات')
            ->assertSee('قالب فروشگاهی رزومه');
    }

    public function test_admin_products_index_search(): void
    {
        Product::create([
            'name' => 'CRM مدیریت',
            'slug' => 'crm',
            'price' => 5000000,
            'type' => 'crm',
            'is_published' => true,
        ]);

        Livewire::actingAs($this->user)
            ->test(ProductsIndex::class)
            ->set('search', 'CRM')
            ->assertSee('CRM مدیریت')
            ->assertDontSee('قالب فروشگاهی رزومه');
    }

    public function test_admin_can_create_product(): void
    {
        Livewire::actingAs($this->user)
            ->test(ProductsIndex::class)
            ->call('openCreateForm')
            ->set('name', 'اسکریپت خبرنامه')
            ->set('price', 800000)
            ->set('type_field', 'script')
            ->set('category_id', $this->category->id)
            ->call('saveProduct');

        $this->assertDatabaseHas('products', [
            'name' => 'اسکریپت خبرنامه',
            'price' => 800000,
            'type' => 'script',
        ]);

        // slug خودکار تولید شده
        $product = Product::where('name', 'اسکریپت خبرنامه')->first();
        $this->assertNotNull($product->slug);
    }

    public function test_admin_can_edit_product(): void
    {
        Livewire::actingAs($this->user)
            ->test(ProductsIndex::class)
            ->call('openEditForm', $this->product->id)
            ->assertSet('editing', true)
            ->set('price', 2000000)
            ->call('saveProduct');

        $this->assertEquals(2000000, (float) $this->product->fresh()->price);
    }

    public function test_admin_can_toggle_published(): void
    {
        Livewire::actingAs($this->user)
            ->test(ProductsIndex::class)
            ->call('togglePublished', $this->product->id);

        $this->assertFalse((bool) $this->product->fresh()->is_published);
    }

    public function test_admin_can_toggle_resume_visibility(): void
    {
        Livewire::actingAs($this->user)
            ->test(ProductsIndex::class)
            ->call('toggleResume', $this->product->id);

        $this->assertFalse((bool) $this->product->fresh()->show_in_resume);
    }

    public function test_admin_can_delete_product_with_confirmation(): void
    {
        $component = Livewire::actingAs($this->user)
            ->test(ProductsIndex::class)
            ->call('confirmDelete', $this->product->id)
            ->assertSet('confirmingDelete', true)
            ->call('deleteProduct');

        $this->assertFalse($component->get('confirmingDelete'));
        $this->assertDatabaseMissing('products', ['id' => $this->product->id]);
    }

    public function test_admin_can_create_category(): void
    {
        Livewire::actingAs($this->user)
            ->test(CategoriesManager::class)
            ->set('name', 'اسکریپت‌ها')
            ->call('saveCategory');

        $this->assertDatabaseHas('product_categories', ['name' => 'اسکریپت‌ها']);
    }

    public function test_admin_can_edit_category(): void
    {
        Livewire::actingAs($this->user)
            ->test(CategoriesManager::class)
            ->call('editCategory', $this->category->id)
            ->assertSet('editing', true)
            ->set('name', 'قالب‌های حرفه‌ای')
            ->call('saveCategory');

        $this->assertEquals('قالب‌های حرفه‌ای', $this->category->fresh()->name);
    }

    public function test_admin_can_delete_category(): void
    {
        Livewire::actingAs($this->user)
            ->test(CategoriesManager::class)
            ->call('confirmDelete', $this->category->id)
            ->call('deleteCategory');

        $this->assertDatabaseMissing('product_categories', ['id' => $this->category->id]);
    }

    public function test_admin_categories_page_renders(): void
    {
        $this->actingAs($this->makeAdmin('purchase-admin2@test.com', 'super-admin'), 'admin');

        $this->get(route('admin.products.categories'))
            ->assertOk()
            ->assertSee('دسته‌بندی محصولات')
            ->assertSee('قالب‌های وب');
    }

    // ===== گزارش: یک خرید کامل از صفر =====

    public function test_full_purchase_journey_from_zero(): void
    {
        // ۱) کاربر گرید محصولات را می‌بیند
        $this->get(route('public.products.index'))
            ->assertOk()
            ->assertSee('قالب فروشگاهی رزومه');

        // ۲) محصول را به سبد اضافه می‌کند
        $this->actingAs($this->user);

        Livewire::test(AddToCartButton::class, ['productId' => $this->product->id])
            ->call('addToCart')
            ->assertDispatched('product-added-to-cart');

        $this->assertEquals(1, app(CartService::class)->count());

        // ۳) وارد checkout می‌شود
        $checkout = app(CheckoutService::class);
        $checkout->start();

        // ۴) مرحله ۱: نوع لایسنس را multi انتخاب می‌کند
        $itemId = app(CartService::class)->cart()->items()->first()->id;
        $checkout->updateItemLicense($itemId, LicenseType::Multi->value);

        // ۵) مرحله ۲: اطلاعات فردی
        $checkout->setPersonalInfo([
            'name' => 'علی شقاقی',
            'phone' => '09121234567',
            'email' => 'ali@example.com',
        ]);

        // ۶) مرحله ۳: روش پرداخت درگاه
        $checkout->setPaymentMethod('gateway');

        // ۷) مرحله ۴: پرداخت
        $order = $checkout->complete($this->user);

        // ===== اعتبارسنجی نهایی =====

        // سفارش + آیتم
        $this->assertInstanceOf(Order::class, $order);
        $this->assertEquals(1, OrderItem::where('order_id', $order->id)->count());
        $this->assertEquals(LicenseType::Multi->value, OrderItem::first()->license_type);

        // لایسنس با کلید UUID
        $license = License::first();
        $this->assertNotEmpty($license->license_key);
        $this->assertEquals(LicenseType::Multi->maxActivations(), $license->max_activations);

        // پرداخت ثبت شده
        $this->assertEquals(1, Payment::where('order_id', $order->id)->count());

        // سبد خالی شده
        $this->assertEquals(0, app(CartService::class)->count());

        // کاربر محصول را در MyProducts می‌بیند
        $this->get(route('user.products.index'))
            ->assertOk()
            ->assertSee('قالب فروشگاهی رزومه')
            ->assertSee($license->license_key);

        // لینک دانلود امشاشده ساخته می‌شود + لاگ هنگام دانلود واقعی
        $downloadsBefore = Download::count();
        $url = app(\Modules\Products\Services\DownloadService::class)
            ->signedUrl($this->user, $this->product, $license);

        $this->assertStringContainsString('signature=', $url);

        $this->actingAs($this->user)->get($url);

        $this->assertGreaterThan($downloadsBefore, Download::count());
    }
}
