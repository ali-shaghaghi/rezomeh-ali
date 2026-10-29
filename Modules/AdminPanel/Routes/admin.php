<?php

use Illuminate\Support\Facades\Route;
use Modules\AdminPanel\Http\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| روتینگ پنل ادمین (Shell خالص)
|--------------------------------------------------------------------------
| تمام جریان‌های احراز هویت به ماژول Auth منتقل شده‌اند (Modules/Auth).
| مدیریت کاربران به ماژول Users منتقل شده است (Modules/Users).
| روت‌های رزومه در Modules/Resume/Routes/admin.php هستند.
| این ماژول فقط Layout + داشبورد + بخش‌های Stub را نگه می‌دارد.
*/

// Protected Routes (Admin required)
Route::prefix('admin')->name('admin.')->middleware(['web', 'admin'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Blog → روت‌های وبلاگ در Modules/Blog/Routes/admin.php هستند (ماژول خودکفا)

    // Users → روت‌های کاربران در Modules/Users/Routes/admin.php هستند (ماژول خودکفا)

    // Orders → روت‌های سفارشات در Modules/Orders/Routes/admin.php هستند (ماژول خودکفا)

    // Tickets → روت‌های تیکت‌ها در Modules/Tickets/Routes/admin.php هستند (ماژول خودکفا)

    // Analytics → روت‌های گزارش‌ها در Modules/Analytics/Routes/admin.php هستند (ماژول خودکفا)

    // Settings → روت‌های تنظیمات در Modules/Settings/Routes/admin.php هستند (ماژول خودکفا)
    // صفحه پروفایل شخصی (پروفایل/آواتار/امنیت) در /admin/settings/security باقی است
});
