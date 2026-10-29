<?php

use Illuminate\Support\Facades\Route;
use Modules\SEO\Http\Controllers\SitemapController;

/*
|--------------------------------------------------------------------------
| روتینگ ماژول SEO — عمومی
|--------------------------------------------------------------------------
*/

Route::middleware('web')->group(function () {
    Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('seo.sitemap');
    Route::get('/sitemap-{section}.xml', [SitemapController::class, 'section'])
        ->name('seo.sitemap.section')
        ->whereIn('section', ['products', 'blog', 'portfolio', 'pages']);
    Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('seo.robots');
});
