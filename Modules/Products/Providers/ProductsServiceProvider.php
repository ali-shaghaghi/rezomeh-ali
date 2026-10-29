<?php

namespace Modules\Products\Providers;

use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Modules\Core\Providers\BaseServiceProvider;
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
use Modules\Products\Models\Product;
use Modules\Products\Policies\ProductPolicy;

class ProductsServiceProvider extends BaseServiceProvider
{
    protected function moduleName(): string
    {
        return 'Products';
    }

    public function boot(): void
    {
        parent::boot();

        // دیتابیس ماژول محصولات — خودکفا
        $this->loadMigrationsFrom(module_path('Products', 'Database'.DIRECTORY_SEPARATOR.'Migrations'));

        // روتینگ ماژول محصولات — عمومی + ادمین
        $this->loadRoutesFrom(module_route_path('Products', 'web.php'));
        $this->loadRoutesFrom(module_route_path('Products', 'admin.php'));

        // ثبت Policy (مدل‌های ماژول کشف خودکار نمی‌شوند)
        Gate::policy(Product::class, ProductPolicy::class);

        // ===== کامپوننت‌های Livewire =====
        // عمومی
        Livewire::component('public.product-grid', ProductGrid::class);
        Livewire::component('public.add-to-cart-button', AddToCartButton::class);
        Livewire::component('public.cart-drawer', CartDrawer::class);
        Livewire::component('public.cart-page', \Modules\Products\Livewire\Public\CartPage::class);
        Livewire::component('public.cart-recommendations', \Modules\Products\Livewire\Public\CartRecommendations::class);

        // Checkout ۴ مرحله
        Livewire::component('checkout.step1-order', Step1Order::class);
        Livewire::component('checkout.step2-personal', Step2Personal::class);
        Livewire::component('checkout.step3-invoice', Step3Invoice::class);
        Livewire::component('checkout.step4-payment', Step4Payment::class);

        // ادمین
        Livewire::component('admin.products.index', ProductsIndex::class);
        Livewire::component('admin.products.categories', CategoriesManager::class);

        // پنل کاربر
        Livewire::component('user.my-products', MyProducts::class);
    }

    public function register(): void
    {
        parent::register();
    }
}
