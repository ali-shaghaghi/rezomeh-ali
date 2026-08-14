<?php

namespace Modules\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Core\Models\UserActivity;
use Symfony\Component\HttpFoundation\Response;

class TrackUserActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();

            UserActivity::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'session_id' => $request->session()->getId(),
                ],
                [
                    'page' => $request->path(),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'last_active_at' => now(),
                ]
            );
        }

        return $next($request);
    }
}