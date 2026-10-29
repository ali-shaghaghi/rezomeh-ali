<?php

use Illuminate\Support\Facades\Route;
use Modules\Public\Http\Controllers\BlogController;
use Modules\Public\Http\Controllers\HomeController;
use Modules\Public\Http\Controllers\PortfolioController;

/*
|--------------------------------------------------------------------------
| روتینگ فرانت‌اند عمومی (ماژول Public)
|--------------------------------------------------------------------------
| تک‌پیج اصلی رزومه + وبلاگ عمومی. بدون احراز هویت — همه قابل مشاهده‌اند.
| نکته: پنل کاربر (/dashboard/*) به ماژول UserPanel منتقل شده است.
| نکته: مطابق کانونشن پروژه تمام گروه‌ها باید middleware 'web'
| داشته باشند تا Session / CSRF / متغیر $errors در ویوها موجود باشد.
*/

/*
|--------------------------------------------------------------------------
| احراز هویت — به ماژول اختصاصی Auth منتقل شده است (Modules/Auth)
|--------------------------------------------------------------------------
| ورود (/login) / ثبت‌نام (/register) / بازیابی رمز (/password/reset و
| /forgot-password) / تایید OTP (/otp/verify) / 2FA / خروج (/logout)
| همه با نام‌های auth.* در ماژول Auth تعریف شده‌اند.
*/

// صفحه اصلی — تک‌پیج رزومه (Hero / مهارت‌ها / سوابق / نمونه‌کارها / تماس)
Route::middleware('web')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('public.home');

    // تعویض زبان سایت (فارسی/انگلیسی) — در سشن ذخیره و به صفحه قبلی برمی‌گردد
    Route::get('/locale/{locale}', function (string $locale) {
        if (!array_key_exists($locale, available_locales())) {
            abort(404);
        }

        session(['locale' => $locale]);
        cookie()->queue(cookie('locale', $locale, 60 * 24 * 365));

        return redirect()->back();
    })->name('public.locale');

    // نمونه‌کارهای عمومی
    Route::get('/portfolio', [PortfolioController::class, 'index'])->name('public.portfolio.index');

    // دانلود فایل رزومه (PDF) — همیشه Force-Download می‌شود تا مرورگر آن را باز نکند (#۲۰)
    Route::get('/resume/download', function () {
        $profile = \Modules\Resume\Models\ResumeProfile::current();

        if (! $profile->resume_file || ! \Illuminate\Support\Facades\Storage::disk('public')->exists($profile->resume_file)) {
            abort(404);
        }

        $name = trim(($profile->name ?? 'resume')).'.pdf';

        return \Illuminate\Support\Facades\Storage::disk('public')
            ->download($profile->resume_file, $name, [
                'Cache-Control' => 'no-store, no-cache, must-revalidate',
            ]);
    })->name('public.resume.download');

    // وبلاگ عمومی
    Route::get('/blog', [BlogController::class, 'index'])->name('public.blog.index');
    Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('public.blog.show');
    Route::post('/blog/{slug}/comment', [BlogController::class, 'comment'])->name('public.blog.comment');
});