<?php

use Illuminate\Support\Facades\Route;
use Modules\Notifications\Http\Controllers\NotificationsController;

/*
|--------------------------------------------------------------------------
| روتینگ ماژول اعلان‌ها — پنل کاربر (خودکفا)
|--------------------------------------------------------------------------
| ساختار URL: /dashboard/notifications/*
| نکته: بخش اعلان‌ها از پنل کاربر حذف شده است (ویژگی ادمین است) —
| مسیر قدیمی برای سازگاری لینک‌ها به داشبورد برمی‌گردد.
*/

Route::prefix('dashboard/notifications')
    ->name('user.notifications.')
    ->middleware(['web'])
    ->group(function () {

        // اعلان‌ها دیگر در پنل کاربر نیست — برگشت به داشبورد
        Route::get('/', fn () => redirect()->to('/dashboard', 301))->name('index');
    });
