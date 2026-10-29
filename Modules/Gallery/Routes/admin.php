<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::prefix('admin/gallery')
    ->name('admin.gallery.')
    ->middleware(['web', 'admin'])
    ->group(function () {
        // صفحهٔ مدیریت گالری — با لایوت پنل ادمین
        Route::get('/', fn () => view('gallery::admin.index'))->name('index');
    });
