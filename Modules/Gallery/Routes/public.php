<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Gallery\Http\Controllers\PublicAssetController;

Route::prefix('gallery')
    ->name('gallery.')
    ->middleware(['web'])
    ->group(function () {
        // هش عمومی: رشتهٔ ۶۴کاراکتری hex از sha256 (قبلاً whereNumber اشتباه بود و همهٔ تصاویر 404 می‌شدند)
        Route::get('/{hash}', [PublicAssetController::class, 'show'])
            ->where('hash', '[0-9a-f]{64}')
            ->name('asset');
    });
