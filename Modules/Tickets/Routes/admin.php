<?php

use Illuminate\Support\Facades\Route;
use Modules\Tickets\Http\Controllers\TicketController;

/*
|--------------------------------------------------------------------------
| روتینگ ماژول تیکت‌ها (خودکفا)
|--------------------------------------------------------------------------
| تمام مسیرهای تیکت‌ها اینجاست — چیزی در ماژول Admin تعریف نمی‌شود.
| ساختار URL: /admin/tickets/*
*/

Route::prefix('admin/tickets')
    ->name('admin.tickets.')
    ->middleware(['web', 'admin'])
    ->group(function () {

        // لیست تیکت‌ها
        Route::get('/', [TicketController::class, 'index'])->name('index');

        // تیکت‌های باز (فیلتر پیش‌فرض)
        Route::get('/open', [TicketController::class, 'open'])->name('open');

        // نمایش مستقل تیکت (گفتگو + پاسخ)
        Route::get('/{ticket}', [TicketController::class, 'show'])->name('show');
    });
