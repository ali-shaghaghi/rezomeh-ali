<?php

namespace Modules\Products\Services;

use App\Models\User;
use Illuminate\Support\Facades\Session;
use Modules\Products\Enums\LicenseType;
use Modules\Products\Models\Cart;
use Modules\Products\Models\CartItem;
use Modules\Products\Models\Product;

/**
 * سبد خرید — مبتنی بر session_id (کاربر مهمان) + user_id (در صورت ورود).
 * سبد در دیتابیس ذخیره می‌شود تا بین سشن‌ها حفظ شود.
 */
class CartService
{
    private const SESSION_KEY = 'cart_session_id';

    /**
     * شناسه سشن سبد — در صورت عدم وجود ساخته می‌شود
     */
    public function sessionId(): string
    {
        $id = Session::get(self::SESSION_KEY);

        if (! $id) {
            $id = \Illuminate\Support\Str::uuid()->toString();
            Session::put(self::SESSION_KEY, $id);
        }

        return $id;
    }

    /**
     * سبد فعلی کاربر (یا مهمان) — اگر نباشد ساخته نمی‌شود
     */
    public function cart(): ?Cart
    {
        return Cart::with(['items.product'])
            ->where('session_id', $this->sessionId())
            ->first();
    }

    /**
     * سبد فعلی — اگر نباشد ساخته می‌شود
     */
    public function getOrCreateCart(): Cart
    {
        $user = auth()->user();

        return Cart::with(['items.product'])->firstOrCreate(
            ['session_id' => $this->sessionId()],
            [
                'user_id' => $user?->id,
                'expires_at' => now()->addDays(7),
            ]
        );
    }

    /**
     * اتصال سبد مهمان به کاربر پس از ورود
     */
    public function assignToUser(User $user): void
    {
        $cart = $this->cart();

        if ($cart && $cart->user_id === null) {
            $cart->update(['user_id' => $user->id]);
        }
    }

    /**
     * افزودن محصول به سبد — اگر محصول با همان لایسنس وجود باشد، تعداد افزایش می‌یابد
     */
    public function add(int|Product $product, string $licenseType = LicenseType::Single->value, int $quantity = 1): CartItem
    {
        $product = $product instanceof Product ? $product : Product::published()->findOrFail($product);

        $license = LicenseType::tryFrom($licenseType) ?? LicenseType::Single;

        $cart = $this->getOrCreateCart();

        $unitPrice = $product->priceForLicense($license->value);

        /** @var CartItem|null $existing */
        $existing = $cart->items()
            ->where('product_id', $product->id)
            ->where('license_type', $license->value)
            ->first();

        if ($existing) {
            $existing->increment('quantity', $quantity);
            $existing->update(['unit_price' => $unitPrice]); // همگام با قیمت روز محصول

            return $existing->refresh();
        }

        return $cart->items()->create([
            'product_id' => $product->id,
            'license_type' => $license->value,
            'quantity' => max(1, $quantity),
            'unit_price' => $unitPrice,
        ]);
    }

    /**
     * تغییر تعداد یک آیتم
     */
    public function updateQuantity(int $itemId, int $quantity): void
    {
        $cart = $this->cart();
        if (! $cart) {
            return;
        }

        $item = $cart->items()->find($itemId);
        if (! $item) {
            return;
        }

        if ($quantity <= 0) {
            $item->delete();

            return;
        }

        $item->update(['quantity' => $quantity]);
    }

    /**
     * حذف یک آیتم از سبد
     */
    public function remove(int $itemId): void
    {
        $cart = $this->cart();
        if (! $cart) {
            return;
        }

        $cart->items()->whereKey($itemId)->delete();
    }

    /**
     * خالی کردن کامل سبد
     */
    public function clear(): void
    {
        $cart = $this->cart();
        if (! $cart) {
            return;
        }

        $cart->items()->delete();
        $cart->delete();
    }

    /**
     * تعداد کل آیتم‌های سبد (برای badge هدر)
     */
    public function count(): int
    {
        $cart = $this->cart();

        return $cart?->items_count ?? 0;
    }

    /**
     * جمع کل سبد
     */
    public function subtotal(): float
    {
        $cart = $this->cart();

        return $cart?->subtotal ?? 0;
    }

    /**
     * آیا سبد خالی است؟
     */
    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }
}
