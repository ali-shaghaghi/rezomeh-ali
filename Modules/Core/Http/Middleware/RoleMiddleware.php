<?php

namespace Modules\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('auth.login');
        }

        if (!auth()->user()->hasAnyRole($roles)) {
            // قانون پروژه: به‌جای 403 فقط صفحه «یافت نشد» نمایش داده می‌شود
            abort(404, 'چنین صفحه‌ای در سیستم وجود ندارد.');
        }

        return $next($request);
    }
}