<?php

use Illuminate\Support\Facades\Route;
use Modules\Orders\Http\Controllers\OrderController;

/*
|--------------------------------------------------------------------------
| روتینگ ماژول سفارشات (خودکفا)
|--------------------------------------------------------------------------
| تمام مسیرهای سفارشات اینجاست — چیزی در ماژول Admin تعریف نمی‌شود.
| ساختار URL: /admin/orders/*
*/

Route::prefix('admin/orders')
    ->name('admin.orders.')
    ->middleware(['web', 'admin'])
    ->group(function () {

        // لیست سفارش‌ها
        Route::get('/', [OrderController::class, 'index'])->name('index');

        // سفارش‌های در انتظار (فیلتر پیش‌فرض)
        Route::get('/pending', [OrderController::class, 'pending'])->name('pending');

        // نمایش مستقل سفارش
        Route::get('/{order}', [OrderController::class, 'show'])->name('show');
    });
