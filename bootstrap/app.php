<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // هدرهای امنیتی HTTP — رفع یافتهٔ M-4 گزارش پنتست (سطح global)
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);

        $middleware->alias([
            'admin' => \Modules\Core\Http\Middleware\AdminMiddleware::class,
            'role' => \Modules\Core\Http\Middleware\RoleMiddleware::class,
            'permission' => \Modules\Core\Http\Middleware\PermissionMiddleware::class,
            'track-activity' => \Modules\Core\Http\Middleware\TrackUserActivity::class,
        ]);

        // ریدایرکت مهمان‌ها به صفحه ورود فرانت (روت با نام auth.login تعریف شده — روت 'login' در پروژه وجود ندارد)
        $middleware->redirectGuestsTo('/login');

        $middleware->web([
            \Modules\Core\Http\Middleware\SetLocale::class,
            \Modules\Core\Http\Middleware\TrackUserActivity::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        /*
        |----------------------------------------------------------------------
        | قانون پروژه: فقط صفحه «یافت نشد» (404)
        |----------------------------------------------------------------------
        | کاربری که به جایی دسترسی ندارد (403 Forbidden) یا مسیر ناموجودی را
        | می‌زند (404/405) هرگز نباید صفحه ارور مجزا ببیند؛ همیشه صفحه
        | «یافت نشد» با دکمه‌های «بازگشت به صفحه قبلی» و «بازگشت به صفحه اصلی»
        | نمایش داده می‌شود. (ویو: Modules/Core/Resources/Views/minimal)
        */
        $exceptions->render(function (Throwable $e, Request $request) {
            // مهمان‌ها باید به صفحه ورود ریدایرکت شوند (رفتار پیش‌فرض لاراول) — دست نزنید
            if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                return null;
            }

            $status = match (true) {
                method_exists($e, 'getStatusCode') => $e->getStatusCode(),
                $e instanceof \Illuminate\Auth\Access\AuthorizationException => 403,
                $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException => 404,
                $e instanceof \Illuminate\Session\TokenMismatchException => 419,
                default => 500,
            };

            // فقط خطاهای دسترسی/مسیر — خطاهای واقعی سرور (500) دست‌نخورده می‌مانند
            if (! in_array($status, [401, 403, 404, 405, 408, 410, 419, 429], true)) {
                return null;
            }

            // درخواست‌های JSON/API و فرگمنت‌های Livewire باید بدون ویو برگردند
            $wantsJson = $request->expectsJson()
                || $request->is('api/*')
                || $request->is('livewire/*')
                || $request->header('X-Livewire') === 'true';

            $messages = [
                401 => 'برای دسترسی به این صفحه ابتدا وارد حساب کاربری خود شوید.',
                403 => 'چنین صفحه‌ای در سیستم وجود ندارد.',
                404 => 'چنین صفحه‌ای در سیستم وجود ندارد.',
                405 => 'چنین صفحه‌ای در سیستم وجود ندارد.',
                408 => 'پاسخی از سرور دریافت نشد — دوباره تلاش کنید.',
                410 => 'چنین صفحه‌ای در سیستم وجود ندارد.',
                419 => 'نشست شما منقضی شده است — صفحه را دوباره بارگذاری کنید.',
                429 => 'تعداد درخواست‌های شما بیش از حد مجاز است — کمی صبر کنید.',
            ];

            if ($wantsJson) {
                return response()->json([
                    'message' => $messages[$status] ?? 'چنین صفحه‌ای در سیستم وجود ندارد.',
                ], in_array($status, [403, 404, 405, 410], true) ? 404 : $status);
            }

            return response()->view('core::minimal', [
                'statusCode' => in_array($status, [403, 404, 405, 410], true) ? 404 : $status,
                'message' => $messages[$status] ?? 'چنین صفحه‌ای در سیستم وجود ندارد.',
            ], in_array($status, [403, 404, 405, 410], true) ? 404 : $status);
        });
    })->create();
