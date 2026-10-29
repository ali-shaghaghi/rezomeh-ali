<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * هدرهای امنیتی HTTP — رفع یافتهٔ M-4 گزارش پنتست
 *
 * گزارش: پاسخ‌ها هیچ هدر امنیتی نداشتند و X-Powered-By پلتفرم را افشا می‌کرد.
 * این میدل‌ور (سطح global) هدرها را به همهٔ پاسخ‌ها اضافه می‌کند.
 */
class SecurityHeaders
{
    /**
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // حذف X-Powered-By در سطح SAPI (فای‌موو/آپاچی) — زیر سرور built-in
        // پی‌اچ‌پی خودش هدر را اضافه می‌کند؛ در پروداکشن expose_php=Off یا
        // fastcgi_hide_header X-Powered-By در nginx قرار دهید.
        if (! headers_sent()) {
            @header_remove('X-Powered-By');
        }

        $response = $next($request);

        // افشای پلتفرم/نسخه PHP را حذف کن
        $response->headers->remove('X-Powered-By');

        // کلیک‌جکینگ (مرورگرهای قدیمی) + قاب‌گرفتن از دامنه دیگر
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // MIME-sniffing: مرورگر محتوا را با حدس خودش تایپ نکند
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // اطلاعات referer به سایت‌های دیگر فقط به‌صورت کامل در origin خودی
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // پشتیبان X-Frame-Options + محدودیت‌های بدون ریسک شکستن UI
        // (CSP عمداً فقط directiveهای امن اضافه دارد؛ sourceها باز گذاشته می‌شوند
        //  تا Livewire/Alpine و assetهای CDN نشکنند)
        $response->headers->set(
            'Content-Security-Policy',
            "frame-ancestors 'self'; base-uri 'self'; object-src 'none'; form-action 'self'"
        );

        // مجوزهای مرورگر: دوربین/میکروفون/گیک‌لوکیشن پیش‌فرض بسته
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        return $response;
    }
}
