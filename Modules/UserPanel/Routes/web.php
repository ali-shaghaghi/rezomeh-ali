<?php

use Illuminate\Support\Facades\Route;
use Modules\UserPanel\Http\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| روتینگ پنل کاربر (ماژول UserPanel — فقط داشبورد/شل)
|--------------------------------------------------------------------------
| UserPanel فقط شل ریل‌تایم داشبورد را نگه می‌دارد؛ هر بخش (سفارش‌ها/
| تیکت‌ها/محصولات/...) کامپوننت Livewire خودش را از ماژول خودش رندر می‌کند.
| سازگاری: /dashboard?tab=X → تب متناظر در همان صفحه.
| توجه: روت‌های عمومی /orders و /tickets قدیمی همچنان در ماژول‌های مربوطه
| (Orders/Tickets) به تب شل ریدایرکت 301 می‌شوند.
*/

Route::middleware('web')->group(function () {

    Route::middleware('auth')->prefix('dashboard')->name('user.')->group(function () {
        // داشبورد/شل ریل‌تایم (شامل سازگاری ?tab=X)
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // صفحه ایجاد تیکت جدید (Livewire مستقل — wire:navigate از لیست تیکت‌ها)
        Route::get('/tickets/new', [DashboardController::class, 'ticketCreate'])->name('tickets.create');
    });
});
