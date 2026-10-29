<?php

namespace Modules\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * حفاظت مسیرهای پنل ادمین.
     *
     * فقط گارد `admin` (جدول admins) اجازه ورود دارد:
     *   - مهمان → ریدایرکت به /admin/login (بدون توکن → 404؛ مسیر ورود ادمین
     *     هش‌شده است و نباید از ریدایرکت‌ها لو برود)
     *   - کاربر عادی واردشده (گارد web) → قانون پروژه: صفحه «یافت نشد» (404)
     *
     * @param  \Closure( \Illuminate\Http\Request): ( \Symfony\Component\HttpFoundation\Response )  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::guard('admin')->check()) {
            // کاربر عادی واردشده نباید وجود پنل ادمین را لو بدهد → فقط صفحه 404
            if (Auth::guard('web')->check()) {
                abort(404, 'چنین صفحه‌ای در سیستم وجود ندارد.');
            }

            // مسیر ورود ادمین توکن هش‌شده دارد — ریدایرکت بدون توکن عمداً 404 می‌شود
            return redirect('/admin/login');
        }

        // تمام auth()/Auth::user() داخل این درخواست (ویو، Livewire، سرویس‌ها)
        // به ادمین فعلی اشاره کند — نه کاربر جدول users
        Auth::shouldUse('admin');

        $admin = Auth::guard('admin')->user();

        if (! $admin->is_active) {
            Auth::guard('admin')->logout();

            return redirect('/admin/login')->with('error', 'حساب کاربری شما غیرفعال است.');
        }

        return $next($request);
    }
}
