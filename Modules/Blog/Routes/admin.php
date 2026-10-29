<?php

use Illuminate\Support\Facades\Route;
use Modules\Blog\Http\Controllers\BlogCategoryController;
use Modules\Blog\Http\Controllers\BlogController;

/*
|--------------------------------------------------------------------------
| روتینگ ماژول وبلاگ (خودکفا)
|--------------------------------------------------------------------------
| تمام مسیرهای وبلاگ اینجاست — چیزی در ماژول Admin تعریف نمی‌شود.
| ساختار URL: /admin/blog/*
*/

Route::prefix('admin/blog')
    ->name('admin.blog.')
    ->middleware(['web', 'admin'])
    ->group(function () {

        // مقالات
        Route::get('/', [BlogController::class, 'index'])->name('index');
        Route::get('/create', [BlogController::class, 'create'])->name('create');
        Route::get('/{post}', [BlogController::class, 'show'])->name('show')->whereNumber('post');
        Route::get('/{post}/edit', [BlogController::class, 'edit'])->name('edit')->whereNumber('post');

        // نظرات
        Route::get('/comments', [BlogController::class, 'comments'])->name('comments.index');

        // دسته‌بندی‌های وبلاگ — حتماً قبل از /{post}
        Route::get('/categories', [BlogCategoryController::class, 'index'])->name('categories.index');
        Route::post('/categories', [BlogCategoryController::class, 'store'])->name('categories.store');
        Route::put('/categories/{category}', [BlogCategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{category}', [BlogCategoryController::class, 'destroy'])->name('categories.destroy');

        // برچسب‌ها (مدیریت ریاکتیو Livewire)
        Route::get('/tags', fn () => view('blog::tags.index'))->name('tags.index');
    });
