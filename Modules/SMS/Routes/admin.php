<?php

use Illuminate\Support\Facades\Route;
use Modules\SMS\Http\Controllers\SmsController;

/*
|--------------------------------------------------------------------------
| روتینگ ماژول پیامک (خودکفا)
|--------------------------------------------------------------------------
| ساختار URL: /admin/sms/*
| دسترسی: ادمین + مجوز manage_sms
*/

Route::prefix('admin/sms')
    ->name('admin.sms.')
    ->middleware(['web', 'admin'])
    ->group(function () {

        // تنظیمات SMS
        Route::get('/', [SmsController::class, 'index'])->name('index');

        // لاگ پیامک‌های ارسالی
        Route::get('/logs', [SmsController::class, 'logs'])->name('logs');

        // مدیریت قالب‌ها
        Route::get('/templates', [SmsController::class, 'templates'])->name('templates');
    });