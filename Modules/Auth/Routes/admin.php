<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Controllers\AdminLoginController;

/*
|--------------------------------------------------------------------------
| روتینگ ورود/خروج ادمین‌ها — کاملاً جدا از کاربران
|--------------------------------------------------------------------------
| ورود: موبایل + رمز عبور → کد پیامکی (اجباری) → ۲FA (در صورت فعال بودن)
| این مسیرها هرگز از جدول users یا گارد web استفاده نمی‌کنند.
|
| مسیر ورود هش‌شده است: /admin/login/{token}
|   - توکن سراسری: settings.admin_login_token (هرگز در هدر/لینک عمومی نمایش داده نمی‌شود)
|   - توکن اختصاصی هر ادمین: admins.login_route_token (روت اختصاصی در اطلاعات ادمین)
|   - توکن نامعتبر یا مسیر بدون توکن (/admin/login) → 404 — وجود صفحه لو نمی‌رود.
*/

// جریان ورود (فقط مهمان وب — ادمین واردشده به داشبورد می‌رود)
Route::middleware(['web', 'guest'])->group(function () {
    Route::get('/admin/login/{token}', [AdminLoginController::class, 'showLoginForm'])
        ->name('admin.login')
        ->where('token', '[A-Za-z0-9]+');

    Route::post('/admin/login/{token}', [AdminLoginController::class, 'login'])
        ->middleware('throttle:30,1')
        ->name('admin.post.login')
        ->where('token', '[A-Za-z0-9]+');

    // تایید کد پیامکی / کد دومرحله‌ای
    Route::get('/admin/login/{token}/verify', [AdminLoginController::class, 'showVerify'])
        ->name('admin.login.verify')
        ->where('token', '[A-Za-z0-9]+');

    Route::post('/admin/login/{token}/verify', [AdminLoginController::class, 'verify'])
        ->middleware('throttle:20,1')
        ->name('admin.login.verify.post')
        ->where('token', '[A-Za-z0-9]+');

    Route::post('/admin/login/{token}/resend', [AdminLoginController::class, 'resend'])
        ->middleware('throttle:10,1')
        ->name('admin.login.resend')
        ->where('token', '[A-Za-z0-9]+');
});

// خروج (نیازمند ورود ادمین)
Route::middleware(['web', 'admin'])->group(function () {
    Route::post('/admin/logout', [AdminLoginController::class, 'logout'])->name('admin.logout');
});
