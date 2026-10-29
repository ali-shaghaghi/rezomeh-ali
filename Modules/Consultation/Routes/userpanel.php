<?php

use Illuminate\Support\Facades\Route;
use Modules\Consultation\Http\Controllers\MyConsultationsController;

/*
|--------------------------------------------------------------------------
| روتینگ پنل کاربر ماژول مشاوره (خودکفا)
|--------------------------------------------------------------------------
| مشاوره‌های من داخل شل ریل‌تایم داشبورد (کامپوننت
| consultation.my-consultations) رندر می‌شود — فقط سازگاری URL اینجاست.
*/

Route::middleware(['web', 'auth'])->prefix('dashboard')->name('user.')->group(function () {
    // سازگاری: /dashboard/consultations → تب مشاوره‌های شل داشبورد (بدون شکستن لینک‌های قدیمی)
    Route::get('/consultations', fn () => redirect()->to('/dashboard?tab=consultations', 301))
        ->name('consultations.index');

    // درخواست مشاوره (ویزارد مرحله‌ای + زمان‌بندی زنده) — صفحه مستقل (کامپوننت Livewire سنگین)
    Route::get('/consultations/book', [MyConsultationsController::class, 'create'])->name('consultations.create');
});
