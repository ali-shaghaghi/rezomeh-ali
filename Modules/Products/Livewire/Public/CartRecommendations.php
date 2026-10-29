<?php

namespace Modules\Products\Livewire\Public;

use Livewire\Component;
use Modules\Products\Models\Product;
use Modules\Products\Services\CartService;

/**
 * پیشنهاد هوشمند سبد خرید — بر اساس علایق کاربر (#۳).
 *
 * ponytail: هیوریستیک وزن‌دهی ساده — سهم دسته‌بندی و نوع محصول در سبد جاری
 * به‌عنوان «علاقه» حساب می‌شود؛ محصولات منتشرشده از همان دسته/نوع پیشنهاد داده
 * می‌شوند. ارتقاء بعدی: ذخیره تاریخچه بازدید/خرید در جدول و امتیازدهی بلندمدت.
 */
class CartRecommendations extends Component
{
    public function quickAdd(int $productId): void
    {
        try {
            app(CartService::class)->add($productId);

            $this->dispatch('product-added-to-cart', message: __('shop.added_to_cart'));
        } catch (\Throwable $e) {
            $this->dispatch('product-cart-error', message: $e->getMessage());
        }
    }

    /**
     * پروفایل علاقه کاربر: وزن هر دسته‌بندی و هر نوع محصول از محتویات سبد
     *
     * @return array{categories: array<int,int>, types: array<string,int>}
     */
    private function interestProfile(): array
    {
        $cart = app(CartService::class)->cart();

        $categories = [];
        $types = [];

        foreach ($cart?->items ?? [] as $item) {
            $product = $item->product;
            if (! $product) {
                continue;
            }

            if ($product->category_id) {
                $categories[$product->category_id] = ($categories[$product->category_id] ?? 0) + (int) $item->quantity;
            }

            $type = $product->type;
            if ($type) {
                $types[$type] = ($types[$type] ?? 0) + (int) $item->quantity;
            }
        }

        return ['categories' => $categories, 'types' => $types];
    }

    public function render()
    {
        $cartService = app(CartService::class);
        $cart = $cartService->cart();
        $cartProductIds = ($cart?->items ?? collect())->pluck('product_id')->all();

        $profile = $this->interestProfile();

        $query = Product::published()
            ->with('category')
            ->when($cartProductIds !== [], fn ($q) => $q->whereNotIn('id', $cartProductIds));

        // امتیاز علاقه: دسته‌بندی مشترک بیشترین وزن، نوع مشترک وزن کمتر، تخفیف‌دار بالاتر
        // ponytail: امتیازدهی در PHP — scale کوچک و قابل‌حمل؛ orderByRaw روی CASE
        // در لاراول ۱۳ به‌عنوان ستون تفسیر می‌شد (باگ «Unknown column in order clause»)
        if ($profile['categories'] !== [] || $profile['types'] !== []) {
            $recommendations = $query->get()
                ->sortByDesc(function (Product $p) use ($profile) {
                    $score = 0;
                    if ($p->category_id && isset($profile['categories'][$p->category_id])) {
                        $score += 2 * $profile['categories'][$p->category_id];
                    }
                    if ($p->type && isset($profile['types'][$p->type])) {
                        $score += $profile['types'][$p->type];
                    }
                    if ($p->has_sale) {
                        $score += 0.5;
                    }

                    return $score;
                })
                ->take(4)
                ->values();
        } else {
            // سبد خالی → محبوب‌ترین‌ها: تخفیف‌دارها و جدیدترین‌ها
            $recommendations = $query->orderByDesc('sale_price')->orderByDesc('created_at')->limit(4)->get();
        }

        // برچسب علایق شناسایی‌شده برای نمایش به کاربر
        $interestLabels = collect($profile['types'])
            ->sortByDesc(fn ($w) => $w)
            ->keys()
            ->take(2)
            ->map(fn ($type) => \Modules\Products\Enums\ProductType::tryFrom((string) $type)?->label())
            ->filter()
            ->values()
            ->all();

        return view('products::livewire.public.cart-recommendations', [
            'recommendations' => $recommendations,
            'interestLabels' => $interestLabels,
            'isEmptyCart' => $cartService->isEmpty(),
        ]);
    }
}
