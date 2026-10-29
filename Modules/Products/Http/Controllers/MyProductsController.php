<?php

namespace Modules\Products\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class MyProductsController extends Controller
{
    use AuthorizesRequests;

    /**
     * محصولات من — لیست محصولات خریداری‌شده + کلید + دانلود (Livewire)
     */
    public function index()
    {
        return view('products::user.my-products');
    }
}
