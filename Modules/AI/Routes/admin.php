<?php

use Modules\AI\Http\Controllers\AiController;

/*
|--------------------------------------------------------------------------
| روتینگ ادمین ماژول AI
|--------------------------------------------------------------------------
*/

Route::prefix('admin/ai')
    ->name('admin.ai.')
    ->middleware(['web', 'admin'])
    ->group(function () {
        Route::get('/', [AiController::class, 'index'])->name('index');
    });
