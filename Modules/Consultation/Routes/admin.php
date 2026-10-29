<?php

use Illuminate\Support\Facades\Route;
use Modules\Consultation\Http\Controllers\ConsultationAdminController;

/*
|--------------------------------------------------------------------------
| روتینگ ادمین ماژول مشاوره (خودکفا)
|--------------------------------------------------------------------------
| ساختار URL: /admin/consultations/*
| دسترسی: ادمین + مجوزهای manage_consultations / manage_consultants / manage_schedules
*/

Route::prefix('admin/consultations')
    ->name('admin.consultations.')
    ->middleware(['web', 'admin'])
    ->group(function () {

        // لیست رزروها + تایید/لغو/تغییر وضعیت
        Route::get('/', [ConsultationAdminController::class, 'index'])->name('index');

        // CRUD مشاوران
        Route::get('/consultants', [ConsultationAdminController::class, 'consultants'])->name('consultants');

        // زمان‌بندی مشاوران (روز/ساعت کاری)
        Route::get('/schedules', [ConsultationAdminController::class, 'schedules'])->name('schedules');
    });