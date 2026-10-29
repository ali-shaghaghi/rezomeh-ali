<?php

namespace Modules\Products\Livewire\Public;

use Livewire\Component;
use Modules\Products\Enums\LicenseType;
use Modules\Products\Models\Product;
use Modules\Products\Services\CartService;

/**
 * دکمه افزودن به سبد — داخل کارت محصول + صفحه محصول.
 * نوع لایسنس قابل انتخاب است و بلافاصله badge هدر آپدیت می‌شود.
 */
class AddToCartButton extends Component
{
    public int $productId;

    public string $licenseType = LicenseType::Single->value;

    public string $layout = 'card'; // card | page

    public bool $justAdded = false;

    public function mount(int $productId, string $licenseType = LicenseType::Single->value, string $layout = 'card'): void
    {
        $this->productId = $productId;
        $this->licenseType = $licenseType;
        $this->layout = $layout;
    }

    public function updatedLicenseType(string $value): void
    {
        $this->licenseType = LicenseType::tryFrom($value)?->value ?? LicenseType::Single->value;
    }

    public function addToCart(): void
    {
        try {
            app(CartService::class)->add($this->productId, $this->licenseType);

            $this->justAdded = true;
            $this->dispatch('product-added-to-cart', message: __('shop.added_to_cart'));
        } catch (\Throwable $e) {
            $this->dispatch('product-cart-error', message: $e->getMessage());
        }
    }

    public function render()
    {
        $product = Product::published()->findOrFail($this->productId);
        $cartCount = app(CartService::class)->count();

        return view('products::livewire.public.add-to-cart-button', [
            'product' => $product,
            'licenseTypes' => LicenseType::cases(),
            'cartCount' => $cartCount,
            'finalPrice' => $product->priceForLicense($this->licenseType),
        ]);
    }
}
