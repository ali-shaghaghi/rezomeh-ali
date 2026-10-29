<?php

use Illuminate\Support\Facades\Route;
use Modules\Notifications\Http\Controllers\NotificationsController;

/*
|--------------------------------------------------------------------------
| روتینگ ماژول اعلان‌ها — پنل ادمین (خودکفا)
|--------------------------------------------------------------------------
| ساختار URL: /admin/notifications/*
| دسترسی: ادمین + مجوز view_notifications
*/

Route::prefix('admin/notifications')
    ->name('admin.notifications.')
    ->middleware(['web', 'admin'])
    ->group(function () {

        // صفحه کامل اعلان‌ها
        Route::get('/', [NotificationsController::class, 'index'])->name('index');
    });
