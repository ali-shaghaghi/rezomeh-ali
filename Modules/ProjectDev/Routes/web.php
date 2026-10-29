<?php

use Illuminate\Support\Facades\Route;
use Modules\ProjectDev\Http\Controllers\ProjectAdminController;
use Modules\ProjectDev\Http\Controllers\ProjectPaymentController;
use Modules\ProjectDev\Http\Controllers\ProjectRequestController;

/*
|--------------------------------------------------------------------------
| روتینگ ماژول ProjectDev (خودکفا)
|--------------------------------------------------------------------------
| - ادمین: کاتالوگ قیمت‌گذاری (انواع + ویژگی‌ها) و بررسی درخواست‌های پروژه
| - کاربر: فرم ثبت درخواست پروژه + پرداخت قسط‌های 30/30/40
| - URLهای /admin/projects و /projects/request حفظ شده‌اند (CHECKPOINT ۸).
*/

Route::prefix('admin/projects')
    ->name('admin.projectdev.')
    ->middleware(['web', 'admin'])
    ->group(function () {

        // انواع پروژه + قیمت‌گذاری (کاتالوگ)
        Route::get('/types', [ProjectAdminController::class, 'types'])->name('types');

        // درخواست‌های پروژه
        Route::get('/requests', [ProjectAdminController::class, 'requests'])->name('requests');
    });

/*
| پنل کاربر — ثبت درخواست پروژه (مثل ثبت تیکت جدید)
*/
Route::prefix('projects')
    ->name('user.projectdev.')
    ->middleware(['web', 'auth'])
    ->group(function () {
        Route::get('/request', [ProjectRequestController::class, 'create'])->name('create');
        Route::post('/request', [ProjectRequestController::class, 'store'])->name('store');
    });

/*
| پرداخت قسط — درگاه‌های تنظیم‌شده در پنل ادمین (Settings → درگاه پرداخت)
*/
Route::prefix('payment')
    ->name('user.projectdev.payment.')
    ->middleware(['web', 'auth'])
    ->group(function () {
        Route::get('/{installment}/pay', [ProjectPaymentController::class, 'pay'])->name('pay');
        Route::get('/gateway/{payment}', [ProjectPaymentController::class, 'gateway'])->name('gateway');
        Route::post('/gateway/{payment}/complete', [ProjectPaymentController::class, 'complete'])->name('complete');
        Route::post('/gateway/{payment}/failed', [ProjectPaymentController::class, 'failed'])->name('failed');
    });
