<?php

use Illuminate\Support\Facades\Route;
use Modules\Users\Http\Controllers\AdminStaffController;
use Modules\Users\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| روتینگ ماژول کاربران (خودکفا)
|--------------------------------------------------------------------------
| مدیریت کاربران از ماژول AdminPanel به اینجا منتقل شده است.
| ساختار URL: /admin/users/*
*/

Route::prefix('admin/users')
    ->name('admin.users.')
    ->middleware(['web', 'admin'])
    ->group(function () {

        // تب ۱: کاربران عادی (فقط جدول users — بدون هیچ ارتقای سطحی)
        Route::get('/', [UserController::class, 'index'])->name('index');

        // نقش‌ها (قبل از روت {user} تا تداخل نکنند)
        Route::get('/roles', [UserController::class, 'roles'])->name('roles');
        Route::post('/roles', [UserController::class, 'storeRole'])->name('roles.store');
        Route::put('/roles/{role}', [UserController::class, 'updateRole'])->name('roles.update');
        Route::delete('/roles/{role}', [UserController::class, 'destroyRole'])->name('roles.destroy');

        // کاربران آنلاین
        Route::get('/online', [UserController::class, 'onlineUsers'])->name('online');

        // خروجی/ورودی اکسل
        Route::get('/export', [UserController::class, 'export'])->name('export');
        Route::post('/import', [UserController::class, 'import'])->name('import');

        // پروفایل کاربر (بدون تغییر نقش — کاربران نقش ثابت «کاربر» دارند)
        Route::get('/{user}', [UserController::class, 'show'])->name('show');
        Route::patch('/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('toggle-status');
    });

/*
|--------------------------------------------------------------------------
| تب ۲: ادمین‌ها (کادر فنی) — CRUD فقط از داخل پنل ادمین
|--------------------------------------------------------------------------
*/
Route::prefix('admin/admins')
    ->name('admin.admins.')
    ->middleware(['web', 'admin'])
    ->group(function () {
        Route::get('/', [AdminStaffController::class, 'index'])->name('index');
        Route::post('/', [AdminStaffController::class, 'store'])->name('store');

        // مسیرهای ورود هش‌شده (کپی/بازتولید) — بازتولید فقط برای سوپر ادمین/صاحب سایت
        Route::patch('/route-token/global', [AdminStaffController::class, 'regenerateGlobalRouteToken'])->name('route-token.global');
        Route::patch('/{admin}/route-token', [AdminStaffController::class, 'regenerateRouteToken'])->name('route-token.regenerate');

        Route::put('/{admin}', [AdminStaffController::class, 'update'])->name('update');
        Route::delete('/{admin}', [AdminStaffController::class, 'destroy'])->name('destroy');
        Route::patch('/{admin}/toggle-status', [AdminStaffController::class, 'toggleStatus'])->name('toggle-status');
        Route::put('/{admin}/role', [AdminStaffController::class, 'updateRole'])->name('role');
    });