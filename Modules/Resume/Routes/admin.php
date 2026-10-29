<?php

use Illuminate\Support\Facades\Route;
use Modules\Resume\Http\Controllers\CategoryController;
use Modules\Resume\Http\Controllers\PortfolioController;
use Modules\Resume\Http\Controllers\ResumeSectionsController;

/*
|--------------------------------------------------------------------------
| روتینگ ماژول رزومه (خودکفا)
|--------------------------------------------------------------------------
| تمام مسیرهای رزومه اینجاست — چیزی در ماژول Admin تعریف نمی‌شود.
| ساختار URL: /admin/resume/*
*/

Route::prefix('admin/resume')
    ->name('admin.resume.')
    ->middleware(['web', 'admin'])
    ->group(function () {

        // هاب مرکزی «رزومه من»
        Route::get('/', [ResumeSectionsController::class, 'resume'])->name('index');

        // تحصیلات
        Route::get('/education', [ResumeSectionsController::class, 'education'])->name('education.index');

        // تجربیات
        Route::get('/experience', [ResumeSectionsController::class, 'experience'])->name('experience.index');

        // مهارت‌ها
        Route::get('/skills', [ResumeSectionsController::class, 'skills'])->name('skills.index');

        // زبان‌ها
        Route::get('/languages', [ResumeSectionsController::class, 'languages'])->name('languages.index');

        // نمونه کار (Portfolio — بخشی از رزومه)
        Route::get('/portfolio', [PortfolioController::class, 'index'])->name('portfolio.index');
        Route::get('/portfolio/create', [PortfolioController::class, 'create'])->name('portfolio.create');

        // دسته‌بندی‌های نمونه کار — زیرمنوی نمونه کار (حتماً قبل از /portfolio/{project})
        // ایجاد/ویرایش از طریق مودال در همین صفحه index انجام می‌شود
        Route::get('/portfolio/categories', [CategoryController::class, 'index'])->name('portfolio.categories.index');
        Route::post('/portfolio/categories', [CategoryController::class, 'store'])->name('portfolio.categories.store');
        Route::put('/portfolio/categories/{category}', [CategoryController::class, 'update'])->name('portfolio.categories.update');
        Route::delete('/portfolio/categories/{category}', [CategoryController::class, 'destroy'])->name('portfolio.categories.destroy');

        // نمونه کار — نمایش و ویرایش
        Route::get('/portfolio/{project}', [PortfolioController::class, 'show'])->name('portfolio.show');
        Route::get('/portfolio/{project}/edit', [PortfolioController::class, 'edit'])->name('portfolio.edit');
    });
