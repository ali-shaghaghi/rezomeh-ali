<?php

namespace Modules\Products\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Modules\Products\Models\Product;

class ProductsAdminController extends Controller
{
    use AuthorizesRequests;

    /**
     * لیست محصولات (Livewire ProductsIndex)
     */
    public function index()
    {
        $this->authorize('viewAny', Product::class);

        return view('products::admin.index');
    }

    /**
     * مدیریت دسته‌بندی‌ها (Livewire CategoriesManager)
     */
    public function categories()
    {
        $this->authorize('viewAny', Product::class);

        return view('products::admin.categories');
    }
}
