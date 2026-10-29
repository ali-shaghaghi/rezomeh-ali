<?php

/*
|--------------------------------------------------------------------------
| مسیرهای ادمین SEO
|--------------------------------------------------------------------------
*/

Route::prefix('admin/seo')
    ->name('admin.seo.')
    ->middleware(['web', 'admin'])
    ->group(function () {
        Route::get('/', fn () => view('seo::admin.index'))->name('index');
    });
