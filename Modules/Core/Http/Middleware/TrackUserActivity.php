<?php

namespace Modules\Core\Http\Middleware;

use App\Models\Admin;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Core\Models\UserActivity;
use Symfony\Component\HttpFoundation\Response;

class TrackUserActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        // ادمین‌ها در جدول admins هستند نه users — ردیابی فعالیت فقط برای کاربران عادی
        // (درج رکورد با شناسه ادمین در user_activities با خطای FOREIGN KEY می‌شکند)
        if (Auth::check() && ! Auth::user() instanceof Admin) {
            $user = Auth::user();

            // بهینه‌سازی: فقط درخواست‌های «صفحه» ردیابی می‌شوند — نه درخواست‌های Livewire/axios
            // (هر اکشن wire:click یک درخواست network است؛ ردیابی آن فقط دیتابیس را الکت می‌کند)
            $isLivewireRequest = $request->is('livewire/*') || $request->is('livewire/update')
                || str_contains((string) $request->header('Referer'), 'livewire')
                || $request->hasHeader('X-Livewire');

            if (! $isLivewireRequest) {
                // بهینه‌سازی: از ثبات session استفاده می‌کنیم نه getId() (که هر بار session را استارت می‌کند)
                UserActivity::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'session_id' => $request->session()->getId(),
                    ],
                    [
                        'page' => $request->path(),
                        'ip_address' => $request->ip(),
                        'user_agent' => substr((string) $request->userAgent(), 0, 255),
                        'last_active_at' => now(),
                    ]
                );
            }
        }

        return $next($request);
    }
}
