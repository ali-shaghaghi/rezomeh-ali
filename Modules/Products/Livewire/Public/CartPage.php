<?php

namespace Modules\Products\Livewire\Public;

use Livewire\Component;
use Modules\Products\Enums\LicenseType;
use Modules\Products\Services\CartService;

/**
 * صفحه سبد خرید (/cart) — نسخه صفحه‌ای از CartDrawer با همان CartService.
 * افزودن/کاهش/حذف/خالی‌کردن بدون رفرش؛ «تکمیل خرید» به مرحله ۱ checkout می‌رود.
 */
class CartPage extends Component
{
    public string $errorMessage = '';

    protected $listeners = [
        'product-added-to-cart' => 'refreshCart',
        'product-cart-error' => 'showError',
    ];

    public function refreshCart(): void
    {
        // render به‌صورت خودکار با commit جدید اجرا می‌شود
        $this->errorMessage = '';
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
     * رفتن به مرحله ۱ checkout — کاربر باید وارد شده باشد
     */
    public function checkout(): void
    {
        if (app(CartService::class)->isEmpty()) {
            $this->errorMessage = __('shop.cart_empty').'.';

            return;
        }

        if (! auth()->check()) {
            $this->js('window.location.href = "'.route('auth.login').'?redirect='.urlencode(route('checkout.step1')).'"');

            return;
        }

        $this->redirect(route('checkout.step1'));
    }

    public function render()
    {
        $cartService = app(CartService::class);
        $cart = $cartService->cart();

        return view('products::livewire.public.cart-page', [
            'cart' => $cart,
            'items' => $cart?->items ?? collect(),
            'subtotal' => $cartService->subtotal(),
            'count' => $cartService->count(),
            'licenseTypes' => LicenseType::cases(),
        ]);
    }
}
