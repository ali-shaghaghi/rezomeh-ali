<?php

use Illuminate\Support\Facades\Route;
use Modules\Consultation\Http\Controllers\ConsultationPublicController;

/*
|--------------------------------------------------------------------------
| روتینگ عمومی ماژول مشاوره
|--------------------------------------------------------------------------
| ساختار URL: /consultation و /consultation/book
*/

Route::middleware('web')->group(function () {
    // لیست مشاوران + ویزارد رزرو
    Route::get('/consultation', [ConsultationPublicController::class, 'index'])->name('public.consultation.index');

    // رزرو (نیاز به ورود — مرحله پرداخت با حساب کاربر است)
    Route::get('/consultation/book', [ConsultationPublicController::class, 'book'])->name('public.consultation.book')->middleware('auth');
});