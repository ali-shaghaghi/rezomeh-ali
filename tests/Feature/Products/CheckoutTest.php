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
use Modules\Products\Enums\ProductType;
use Modules\Products\Livewire\Checkout\Step1Order;
use Modules\Products\Livewire\Checkout\Step2Personal;
use Modules\Products\Livewire\Checkout\Step3Invoice;
use Modules\Products\Livewire\Checkout\Step4Payment;
use Modules\Products\Models\CartItem;
use Modules\Products\Models\License;
use Modules\Products\Models\OrderItem;
use Modules\Products\Models\Product;
use Modules\Products\Models\ProductCategory;
use Modules\Products\Services\CartService;
use Modules\Products\Services\CheckoutService;
use Tests\TestCase;

/**
 * تست‌های Checkout ۴ مرحله‌ای — Progress Stepper، تخفیف، مالیات ۹٪ و تکمیل خرید.
 *
 * CHECKPOINT ۶: عبور از ۴ مرحله (Livewire بدون reload) → پرداخت موفق.
 * CHECKPOINT ۱۰: اتصال به PaymentService مشترک.
 */
class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        $this->seed(ProductsPermissionSeeder::class);

        $this->user = User::create([
            'name' => 'خریدار چک‌اوت',
            'email' => 'checkout@test.com',
            'phone' => '09121234567',
            'password' => 'password',
            'is_active' => true,
        ]);
        $this->user->roles()->attach(Role::where('slug', 'user')->firstOrFail());

        $category = ProductCategory::create(['name' => 'قالب', 'slug' => 'tpl', 'is_active' => true]);

        $this->product = Product::create([
            'category_id' => $category->id,
            'name' => 'قالب چک‌اوت',
            'slug' => 'checkout-template',
            'price' => 1000000,
            'type' => ProductType::Template->value,
            'file_path' => 'products/checkout.zip',
            'version' => '1.0.0',
            'is_published' => true,
        ]);
    }

    private function addToCart(string $license = LicenseType::Single->value): CartItem
    {
        return app(CartService::class)->add($this->product, $license);
    }

    // ===== شروع checkout =====

    public function test_checkout_with_empty_cart_has_no_items(): void
    {
        Livewire::actingAs($this->user)
            ->test(Step1Order::class)
            ->assertViewHas('items', []);
    }

    public function test_step1_starts_with_cart_contents(): void
    {
        $item = $this->addToCart();

        $component = Livewire::actingAs($this->user)->test(Step1Order::class);

        $items = $component->viewData('items');
        $this->assertCount(1, $items);
        $row = $items[$item->id];
        $this->assertSame($this->product->id, $row['product']->id);
        $this->assertSame($item->id, $row['item_id']);
    }

    public function test_step1_progress_stepper_shows_step_1(): void
    {
        $this->addToCart();

        Livewire::actingAs($this->user)
            ->test(Step1Order::class)
            ->assertViewHas('step', 1);
    }

    // ===== تغییر لایسنس در مرحله ۱ =====

    public function test_step1_changing_license_updates_price(): void
    {
        $item = $this->addToCart(LicenseType::Single->value);

        $component = Livewire::actingAs($this->user)->test(Step1Order::class);

        $this->assertSame(1000000.0, $component->viewData('totals')['subtotal']);

        $component->call('setLicense', $item->id, LicenseType::Lifetime->value)
            ->assertHasNoErrors();

        // lifetime ضریب ۳ دارد
        $this->assertSame(3000000.0, $component->viewData('totals')['subtotal']);
    }

    public function test_step1_license_change_persists_to_cart_item(): void
    {
        $item = $this->addToCart(LicenseType::Single->value);

        Livewire::actingAs($this->user)
            ->test(Step1Order::class)
            ->call('setLicense', $item->id, LicenseType::Multi->value);

        $item->refresh();

        $this->assertSame(LicenseType::Multi->value, $item->license_type);
        $this->assertSame(2000000.0, (float) $item->unit_price);
    }

    public function test_step1_invalid_item_id_is_rejected(): void
    {
        $this->addToCart();

        Livewire::actingAs($this->user)
            ->test(Step1Order::class)
            ->call('setLicense', 99999, LicenseType::Multi->value)
            ->assertSet('errorMessage', 'آیتم سبد یافت نشد.');
    }

    // ===== کد تخفیف =====

    public function test_discount_code_applys_percentage(): void
    {
        $this->addToCart();

        $component = Livewire::actingAs($this->user)->test(Step1Order::class);
        $component->set('discountCode', 'SAVE10')
            ->call('applyDiscount')
            ->assertHasNoErrors();

        $totals = $component->viewData('totals');

        // ۱٬۰۰۰٬۰۰۰ − ۱۰٪ = ۹۰۰٬۰۰۰
        $this->assertSame(10.0, $totals['discount_percent']);
        $this->assertSame(100000.0, (float) $totals['discount_amount']);
        $this->assertSame('SAVE10', $totals['discount_code']);
    }

    public function test_discount_code_case_insensitive(): void
    {
        $this->addToCart();

        Livewire::actingAs($this->user)
            ->test(Step1Order::class)
            ->set('discountCode', 'pro20')
            ->call('applyDiscount')
            ->assertHasNoErrors()
            ->assertViewHas('totals', fn ($totals) => $totals['discount_percent'] === 20.0);
    }

    public function test_invalid_discount_code_shows_error(): void
    {
        $this->addToCart();

        Livewire::actingAs($this->user)
            ->test(Step1Order::class)
            ->set('discountCode', 'INVALID')
            ->call('applyDiscount')
            ->assertSet('errorMessage', 'کد تخفیف نامعتبر است.');
    }

    public function test_discount_can_be_removed(): void
    {
        $this->addToCart();

        $component = Livewire::actingAs($this->user)->test(Step1Order::class);
        $component->set('discountCode', 'SAVE10')->call('applyDiscount');
        $component->call('removeDiscount')
            ->assertSet('discountCode', '');

        $totals = $component->viewData('totals');
        $this->assertSame(0.0, $totals['discount_percent']);
        $this->assertSame('', $totals['discount_code']);
    }

    // ===== مالیات ۹٪ =====

    public function test_tax_is_nine_percent_after_discount(): void
    {
        $this->addToCart();

        $component = Livewire::actingAs($this->user)->test(Step1Order::class);
        $component->set('discountCode', 'SAVE10')->call('applyDiscount');

        $totals = $component->viewData('totals');

        // ۹۰۰٬۰۰۰ × ۹٪ = ۸۱٬۰۰۰
        $this->assertSame(9.0, $totals['tax_percent']);
        $this->assertSame(81000.0, (float) $totals['tax_amount']);
        $this->assertSame(981000.0, (float) $totals['total']);
    }

    public function test_tax_without_discount(): void
    {
        $this->addToCart();

        $totals = Livewire::actingAs($this->user)
            ->test(Step1Order::class)
            ->viewData('totals');

        // ۱٬۰۰۰٬۰۰۰ + ۹۰٬۰۰۰ مالیات
        $this->assertSame(90000.0, (float) $totals['tax_amount']);
        $this->assertSame(1090000.0, (float) $totals['total']);
    }

    // ===== مرحله ۲: اطلاعات فردی =====

    public function test_step2_prefills_user_profile(): void
    {
        $this->addToCart();

        Livewire::actingAs($this->user)
            ->test(Step2Personal::class)
            ->assertSet('name', $this->user->name)
            ->assertSet('email', $this->user->email);
    }

    public function test_step2_requires_valid_phone(): void
    {
        $this->addToCart();

        Livewire::actingAs($this->user)
            ->test(Step2Personal::class)
            ->set('phone', '123')
            ->call('nextStep')
            ->assertHasErrors(['phone']);
    }

    public function test_step2_requires_name(): void
    {
        $this->addToCart();

        Livewire::actingAs($this->user)
            ->test(Step2Personal::class)
            ->set('name', '')
            ->call('nextStep')
            ->assertHasErrors(['name']);
    }

    public function test_step2_saves_personal_info(): void
    {
        $this->addToCart();

        Livewire::actingAs($this->user)
            ->test(Step2Personal::class)
            ->set('name', 'نام جدید')
            ->set('phone', '09120000000')
            ->set('notes', 'توضیحات تست')
            ->call('nextStep')
            ->assertHasNoErrors();

        $state = app(CheckoutService::class)->state();

        $this->assertSame('نام جدید', $state['personal']['name']);
        $this->assertSame('09120000000', $state['personal']['phone']);
        $this->assertSame('توضیحات تست', $state['personal']['notes']);
    }
}
