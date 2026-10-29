<?php

namespace Modules\Products\Http\Controllers;

use App\Http\Controllers\Controller;

class CartController extends Controller
{
    /**
     * صفحه سبد خرید (Livewire CartDrawer روی همین صفحه)
     */
    public function index()
    {
        return view('products::cart.index');
    }
}
