<?php

use Illuminate\Support\Facades\Route;
use Modules\Products\Http\Controllers\CartController;
use Modules\Products\Http\Controllers\CheckoutController;
use Modules\Products\Http\Controllers\DownloadController;
use Modules\Products\Http\Controllers\MyProductsController;
use Modules\Products\Http\Controllers\ProductController;

/*
|--------------------------------------------------------------------------
| روتینگ عمومی ماژول محصولات (خودکفا)
|--------------------------------------------------------------------------
| محصولات + سبد خرید + checkout ۴ مرحله + محصولات من.
| checkout و محصولات من نیاز به احراز هویت دارند (برای صدور لایسنس).
| مطابق کانونشن پروژه: middleware 'web' برای session/CSRF.
*/

Route::middleware('web')->group(function () {

    // ===== محصولات (عمومی) =====
    Route::get('/products', [ProductController::class, 'index'])->name('public.products.index');
    Route::get('/products/{slug}', [ProductController::class, 'show'])->name('public.products.show');

    // ===== سبد خرید =====
    Route::get('/cart', [CartController::class, 'index'])->name('public.cart.index');

    // ===== Checkout ۴ مرحله (نیاز به ورود) =====
    Route::middleware('auth')->prefix('checkout')->name('checkout.')->group(function () {
        Route::get('/step-1', [CheckoutController::class, 'step1'])->name('step1');
        Route::get('/step-2', [CheckoutController::class, 'step2'])->name('step2');
        Route::get('/step-3', [CheckoutController::class, 'step3'])->name('step3');
        Route::get('/step-4', [CheckoutController::class, 'step4'])->name('step4');
    });
});

// ===== محصولات من + دانلود (نیاز به ورود) =====
Route::middleware(['web', 'auth'])->prefix('dashboard')->name('user.')->group(function () {
    Route::get('/products', [MyProductsController::class, 'index'])->name('products.index');

    // دانلود با لینک امقاشده (مهمان‌ها با لینک معتبر هم می‌توانند دانلود کنند)
    Route::get('/products/{slug}/download', [DownloadController::class, 'download'])
        ->name('products.download')
        ->withoutMiddleware(['auth']);
});
