<?php

use Illuminate\Support\Facades\Route;
use Modules\Tickets\Http\Controllers\UserPanelTicketController;

/*
|--------------------------------------------------------------------------
| روتینگ پنل کاربر ماژول تیکت‌ها (خودکفا)
|--------------------------------------------------------------------------
| صفحات جزئیات تیکت/گفتگو زیر /dashboard/* — لیست تیکت‌ها داخل شل
| ریل‌تایم داشبورد (کامپوننت tickets.my-tickets) رندر می‌شود.
| سازگاری: /tickets و /tickets/{id} قدیمی → ریدایرکت 301 به تب شل.
*/

Route::middleware(['web', 'auth'])->prefix('dashboard/tickets')->name('user.tickets.')->group(function () {
    // گفتگوی زنده تیکت من
    Route::get('/{ticketId}', [UserPanelTicketController::class, 'show'])->name('show');
});

// سازگاری URLهای قدیمی /tickets — ریدایرکت 301 به تب تیکت‌های شل داشبورد
Route::middleware('web')->group(function () {
    Route::redirect('/tickets', '/dashboard?tab=tickets', 301);
    Route::redirect('/tickets/{ticketId}', '/dashboard/tickets/{ticketId}', 301);
});
