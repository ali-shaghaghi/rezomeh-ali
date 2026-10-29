<?php

namespace Modules\Products\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Products\Enums\ProductType;
use Modules\Products\Models\ProductCategory;
use Modules\Products\Services\ProductService;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductService $productService,
    ) {}

    /**
     * گرید محصولات عمومی + فیلتر دسته/نوع
     */
    public function index()
    {
        $products = $this->productService->publishedList(request()->only(['search', 'category', 'type', 'sort']));

        $categories = ProductCategory::active()->orderBy('order')->get();

        return view('products::public.index', [
            'products' => $products,
            'categories' => $categories,
            'types' => ProductType::cases(),
        ]);
    }

    /**
     * نمایش یک محصول
     */
    public function show(string $slug)
    {
        $product = $this->productService->findBySlug($slug);

        $related = ProductCategory::find($product->category_id)
            ?->products()
            ->whereKeyNot($product->id)
            ->limit(4)
            ->get() ?? collect();

        return view('products::public.show', [
            'product' => $product,
            'related' => $related,
        ]);
    }
}
