<?php

use Illuminate\Support\Facades\Route;
use Modules\Orders\Http\Controllers\PaymentController;

/*
|--------------------------------------------------------------------------
| روتینگ پرداخت مشترک (خودکفا)
|--------------------------------------------------------------------------
| درگاه + callback عمومی برای هر سه ماژول Products / Consultation / ProjectDev.
* مسیرهای ادمین سفارشات در admin.php هستند.
*/

Route::middleware(['web'])->group(function () {

    // ===== درگاه پرداخت (شبیه‌ساز) =====
    Route::prefix('payment')->name('payment.')->group(function () {

        // صفحه درگاه — کاربر از اینجا پرداخت را انجام می‌دهد
        Route::get('/{payment}/gateway', [PaymentController::class, 'gateway'])->name('gateway');

        // تأیید موفق (دکمه شبیه‌ساز)
        Route::post('/{payment}/complete', [PaymentController::class, 'complete'])->name('complete');

        // ثبت عدم موفقیت
        Route::post('/{payment}/fail', [PaymentController::class, 'fail'])->name('fail');

        // callback عمومی درگاه — /payment/{provider}/callback?payment={id}&status={ok|fail}
        Route::get('/{provider}/callback', [PaymentController::class, 'callback'])->name('callback');
    });
});
