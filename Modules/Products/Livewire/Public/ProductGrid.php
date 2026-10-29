<?php

namespace Modules\Products\Livewire\Public;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\Products\Enums\ProductType;
use Modules\Products\Models\ProductCategory;
use Modules\Products\Services\ProductService;

/**
 * گرید محصولات عمومی — جستجو + فیلتر دسته/نوع بدون reload
 */
class ProductGrid extends Component
{
    use WithPagination;

    public string $search = '';

    public string $category = '';

    public string $type = '';

    public string $sort = 'newest';

    protected $queryString = [
        'search' => ['except' => ''],
        'category' => ['except' => ''],
        'type' => ['except' => ''],
        'sort' => ['except' => 'newest'],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingCategory(): void
    {
        $this->resetPage();
    }

    public function updatingType(): void
    {
        $this->resetPage();
    }

    /**
     * افزودن محصول به سبد — رویداد برای toast + badge هدر
     */
    public function addToCart(int $productId, string $licenseType = 'single'): void
    {
        try {
            app(\Modules\Products\Services\CartService::class)
                ->add($productId, $licenseType);

            $this->dispatch('product-added-to-cart', message: __('shop.added_to_cart'));
        } catch (\Throwable $e) {
            $this->dispatch('product-cart-error', message: $e->getMessage());
        }
    }

    public function render()
    {
        $products = app(ProductService::class)->publishedList([
            'search' => $this->search,
            'category' => $this->category,
            'type' => $this->type,
            'sort' => $this->sort,
        ]);

        return view('products::livewire.public.product-grid', [
            'products' => $products,
            'categories' => ProductCategory::active()->orderBy('order')->get(),
            'types' => ProductType::cases(),
        ]);
    }
}
