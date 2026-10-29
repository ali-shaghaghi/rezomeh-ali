<?php

use Illuminate\Support\Facades\Route;
use Modules\Products\Http\Controllers\ProductsAdminController;

/*
|--------------------------------------------------------------------------
| روتینگ ادمین ماژول محصولات (خودکفا)
|--------------------------------------------------------------------------
| مدیریت محصولات + دسته‌بندی‌ها. ساختار URL: /admin/products/*
*/

Route::prefix('admin/products')
    ->name('admin.products.')
    ->middleware(['web', 'admin'])
    ->group(function () {

        // لیست محصولات
        Route::get('/', [ProductsAdminController::class, 'index'])->name('index');

        // مدیریت دسته‌بندی‌ها
        Route::get('/categories', [ProductsAdminController::class, 'categories'])->name('categories');
    });
