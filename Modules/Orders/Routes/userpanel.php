<?php

use Illuminate\Support\Facades\Route;
use Modules\Orders\Http\Controllers\UserPanelOrderController;

/*
|--------------------------------------------------------------------------
| روتینگ پنل کاربر ماژول سفارشات (خودکفا)
|--------------------------------------------------------------------------
| صفحه جزئیات سفارش زیر /dashboard/* — لیست سفارش‌ها داخل شل
| ریل‌تایم داشبورد (کامپوننت orders.my-orders) رندر می‌شود.
| سازگاری: /orders و /orders/{no} قدیمی → ریدایرکت 301.
*/

Route::middleware(['web', 'auth'])->prefix('dashboard/orders')->name('user.orders.')->group(function () {
    // جزئیات سفارش من
    Route::get('/{orderNumber}', [UserPanelOrderController::class, 'show'])->name('show');
});

// سازگاری URLهای قدیمی /orders — ریدایرکت 301 به تب سفارش‌های شل داشبورد
Route::middleware('web')->group(function () {
    Route::redirect('/orders', '/dashboard?tab=orders', 301);
    Route::redirect('/orders/{orderNumber}', '/dashboard/orders/{orderNumber}', 301);
});
