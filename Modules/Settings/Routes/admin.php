<?php

use Illuminate\Support\Facades\Route;
use Modules\Settings\Http\Controllers\SettingsController;

/*
|--------------------------------------------------------------------------
| روتینگ ماژول تنظیمات (خودکفا)
|--------------------------------------------------------------------------
| تمام مسیرهای تنظیمات اینجاست — چیزی در ماژول Admin تعریف نمی‌شود.
| ساختار URL: /admin/settings/*
| همه تب‌ها داخل یک کامپوننت Livewire هستند (جابه‌جایی بدون رفرش)؛
| URL گروه فقط مقدار اولیه تب فعال را تعیین می‌کند.
*/

Route::prefix('admin/settings')
    ->name('admin.settings.')
    ->middleware(['web', 'admin'])
    ->group(function () {

        // صفحه تنظیمات (پیش‌فرض: تب عمومی سایت)
        Route::get('/', [SettingsController::class, 'index'])->name('index');

        // ریدایرکت گروه قدیمی (general → site) — مقدار 'general' مستقیم به متد داده می‌شود
        Route::get('/general', [SettingsController::class, 'legacyRedirect'])->name('general');

        // تب فعال از طریق URL — ?tab= در کامپوننت هم پشتیبانی می‌شود
        Route::get('/{group}', [SettingsController::class, 'index'])
            ->name('group')
            ->whereIn('group', ['site', 'profile', 'appearance', 'security', 'smtp', 'payment', 'payment_extended', 'contact', 'sms', 'consultation', 'social']);
    });
