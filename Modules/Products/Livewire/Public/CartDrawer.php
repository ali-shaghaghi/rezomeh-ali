<?php

namespace Modules\Products\Livewire\Public;

use Livewire\Component;
use Modules\Products\Enums\LicenseType;
use Modules\Products\Services\CartService;

/**
 * کشوی سبد خرید — از سمت چپ باز می‌شود (RTL).
 * روی همه صفحات لود می‌شود؛ با رویداد product-added-to-cart باز می‌شود.
 */
class CartDrawer extends Component
{
    public bool $open = false;

    public string $licenseType = '';

    protected $listeners = [
        'product-added-to-cart' => 'openDrawer',
        'product-cart-error' => 'showError',
        'open-cart-drawer' => 'openDrawer',
    ];

    public string $errorMessage = '';

    public function openDrawer(string $message = ''): void
    {
        $this->open = true;
        $this->errorMessage = '';
    }

    public function closeDrawer(): void
    {
        $this->open = false;
    }

    /**
     * بستن کشو و رفتن به صفحه محصولات (#۲۳: قبلاً فقط کشو را می‌بست)
     */
    public function browseProducts(): void
    {
        $this->open = false;
        $this->redirect(route('public.products.index'));
    }

    public function showError(string $message = ''): void
    {
        $this->errorMessage = $message;
    }

    public function increment(int $itemId): void
    {
        $cart = app(CartService::class);
        $item = $cart->cart()?->items()->find($itemId);
        if ($item) {
            $cart->updateQuantity($itemId, $item->quantity + 1);
        }
    }

    public function decrement(int $itemId): void
    {
        $cart = app(CartService::class);
        $item = $cart->cart()?->items()->find($itemId);
        if ($item) {
            $cart->updateQuantity($itemId, $item->quantity - 1);
        }
    }

    public function removeItem(int $itemId): void
    {
        app(CartService::class)->remove($itemId);
    }

    public function clearCart(): void
    {
        app(CartService::class)->clear();
    }

    /**
     * رفتن به صفحه سبد خرید (/cart) — کاربر آنجا می‌تواند محصولات بیشتری اضافه کند
     * و سپس از طریق دکمه «تکمیل خرید» صفحه سبد به مراحل checkout برود (#۳)
     */
    public function checkout(): void
    {
        $this->open = false;
        $this->redirect(route('public.cart.index'));
    }

    public function render()
    {
        $cartService = app(CartService::class);
        $cart = $cartService->cart();

        return view('products::livewire.public.cart-drawer', [
            'cart' => $cart,
            'items' => $cart?->items ?? collect(),
            'subtotal' => $cartService->subtotal(),
            'count' => $cartService->count(),
            'licenseTypes' => LicenseType::cases(),
        ]);
    }
}
