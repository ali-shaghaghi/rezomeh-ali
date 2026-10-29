<?php

use Illuminate\Support\Facades\Route;
use Modules\Analytics\Http\Controllers\AnalyticsController;

/*
|--------------------------------------------------------------------------
| روتینگ ماژول گزارش‌ها (خودکفا)
|--------------------------------------------------------------------------
| ساختار URL: /admin/analytics/*
| دسترسی: ادمین + مجوز view_reports
*/

Route::prefix('admin/analytics')
    ->name('admin.analytics.')
    ->middleware(['web', 'admin'])
    ->group(function () {

        // داشبورد گزارش‌ها (نمودارها + آمار کلی)
        Route::get('/', [AnalyticsController::class, 'overview'])->name('index');

        // گزارش فروش (پرداخت‌ها + محصولات پرفروش)
        Route::get('/sales', [AnalyticsController::class, 'sales'])->name('sales');

        // گزارش ترافیک کاربران (آنلاین + فعالیت + صفحات)
        Route::get('/traffic', [AnalyticsController::class, 'traffic'])->name('traffic');

        // گزارش محتوا (مقالات + محصولات + مشاوره)
        Route::get('/content', [AnalyticsController::class, 'content'])->name('content');
    });
