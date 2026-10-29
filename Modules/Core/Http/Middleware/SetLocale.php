<?php

namespace Modules\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

/**
 * تنظیم locale برنامه از روی سشن/کوکی کاربر.
 *
 * زبان پیش‌فرض فارسی (fa) است. وقتی کاربر زبان دیگری انتخاب کند،
 * مقدار آن در سشن با کلید 'locale' ذخیره می‌شود و این middleware
 * آن را روی هر درخواست اعمال می‌کند تا تمام ویوها و helper‌ها
 * (مثل __() و current_locale()) زبان درست را ببینند.
 */
class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $locale = session('locale')
            ?? $request->cookie('locale')
            ?? config('app.locale', 'fa');

        // فقط زبان‌های پشتیبانی‌شده را می‌پذیریم
        if (!in_array($locale, ['fa', 'en'], true)) {
            $locale = 'fa';
        }

        App::setLocale($locale);

        return $next($request);
    }
}
