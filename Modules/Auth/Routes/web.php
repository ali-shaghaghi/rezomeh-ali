<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Controllers\EmailVerificationController;
use Modules\Auth\Http\Controllers\LoginController;
use Modules\Auth\Http\Controllers\LogoutController;
use Modules\Auth\Http\Controllers\PasswordResetController;
use Modules\Auth\Http\Controllers\RegisterController;
use Modules\Auth\Http\Controllers\SocialiteController;
use Modules\Auth\Http\Controllers\TwoFactorController;

/*
|--------------------------------------------------------------------------
| روتینگ ماژول Auth — احراز هویت یکپارچه (ادمین + کاربر عادی)
|--------------------------------------------------------------------------
| تمام جریان‌های ورود/ثبت‌نام/OTP/2FA/بازیابی رمز/ورود اجتماعی/خروج
| از ماژول‌های Admin و Public به این ماژول منتقل شده‌اند.
|
| تشخیص نقش: پس از ورود موفق، RedirectService کاربر را بر اساس نقش هدایت می‌کند:
| ادمین (super-admin/admin/editor/support) → پنل ادمین، کاربر عادی → پنل کاربر.
|
| نکته: نام روت 'auth.login' و 'auth.logout' توسط ویوها و میدلورها استفاده می‌شود؛
| نام‌های قدیمی (admin.login / auth.forgot-password) حفظ یا هم‌آدرس شده‌اند.
*/

// صفحات مهمان (Guest only)
Route::middleware(['web', 'guest'])->group(function () {
    // ورود — throttle لایهٔ دوم (قفل اصلی داخل کنترلر است؛ یافتهٔ L-2)
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('auth.login');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:30,1')->name('auth.post.login');

    // ثبت‌نام
    Route::get('/register', [RegisterController::class, 'showRegisterForm'])->name('auth.register');
    Route::post('/register', [RegisterController::class, 'register'])->middleware('throttle:10,1')->name('auth.post.register');

    // بازیابی رمز عبور (دو آدرس برای سازگاری با نام‌های قدیمی ماژول Public)
    Route::get('/password/reset', [PasswordResetController::class, 'showForm'])->name('auth.password.request');
    Route::post('/password/reset', [PasswordResetController::class, 'reset'])->middleware('throttle:10,1')->name('auth.password.update');
    Route::get('/forgot-password', [PasswordResetController::class, 'showForm'])->name('auth.forgot-password');
    Route::post('/forgot-password', [PasswordResetController::class, 'reset'])->middleware('throttle:10,1')->name('auth.password.email');

    // ورود اجتماعی (Google / GitHub)
    Route::get('/auth/{provider}', [SocialiteController::class, 'redirect'])->name('auth.socialite.redirect');
    Route::get('/auth/{provider}/callback', [SocialiteController::class, 'callback'])->name('auth.socialite.callback');
});

// تایید OTP و احراز هویت دومرحله‌ای (نیازمند سشن در جریان ورود)
Route::middleware(['web'])->group(function () {
    Route::get('/otp/verify', fn () => view('auth::auth.otp'))->name('auth.otp.verify');
    Route::get('/two-factor/verify', [TwoFactorController::class, 'showForm'])->name('auth.two-factor.verify');
    Route::post('/two-factor/verify', [TwoFactorController::class, 'verify'])->middleware('throttle:20,1')->name('auth.two-factor.verify.post');
});

// تایید ایمیل — قابلیت اختیاری کاربر (پیش‌فرض غیرفعال)
Route::middleware(['web'])->group(function () {
    // صفحه اعلان (مهمان هم می‌تواند ببیند؛ کنترلر مهمان را به ورود می‌فرستد)
    Route::get('/email/verify', [EmailVerificationController::class, 'notice'])
        ->name('verification.notice');

    // لینک امضاشده در ایمیل — بدون middleware auth تا کاربر از دسکتاپ دیگر هم بتواند تایید کند
    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware(['signed'])
        ->name('verification.verify');

    // ارسال مجدد — فقط کاربر واردشده
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])
        ->middleware(['auth'])
        ->name('verification.send');
});

// خروج (کاربر واردشده — ادمین و کاربر عادی)
Route::middleware(['web', 'auth'])->group(function () {
    Route::post('/logout', [LogoutController::class, 'logout'])->name('auth.logout');
});

// تغییر رمز عبور (همان آدرس قبلی پنل ادمین — فقط ادمین)
Route::middleware(['web', 'admin'])->group(function () {
    Route::get('/admin/change-password', fn () => view('auth::auth.change-password'))->name('auth.change-password');
});
