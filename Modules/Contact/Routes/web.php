<?php

use Illuminate\Support\Facades\Route;
use Modules\Contact\Http\Controllers\Admin\ChatController;
use Modules\Contact\Http\Controllers\ContactController;

/*
|--------------------------------------------------------------------------
| روتینگ ماژول تماس (خودکفا)
|--------------------------------------------------------------------------
| ویجت شناور در همه لایوت‌ها رندر می‌شود (livewire)؛ این روت فقط
| fallback برای ارسال بدون Livewire (مثلاً JS خام) است.
*/

Route::middleware('web')->group(function () {
    Route::post('/contact/message', [ContactController::class, 'store'])->name('public.contact.message');
});

// ===== پنل ادمین: مدیریت چت آنلاین (#۲۱) =====
Route::middleware(['web', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    });
