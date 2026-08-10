<?php

namespace Modules\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('admin.login');
        }

        if (!auth()->user()->is_admin) {
            abort(403, 'شما دسترسی به پنل ادمین را ندارید.');
        }

        if (!auth()->user()->is_active) {
            auth()->logout();
            return redirect()->route('admin.login')->with('error', 'حساب کاربری شما غیرفعال است.');
        }

        return $next($request);
    }
}