<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->registerModuleViews();
        $this->registerAnonymousComponentPaths();
        $this->registerLivewirePersistentMiddleware();
    }

    protected function registerLivewirePersistentMiddleware(): void
    {
        // درخواست‌های به‌روزرسانی Livewire (POST /livewire/update) مسیر اصلی کامپوننت را
        // دوباره اجرا می‌کنند؛ بدون ثبت این میدلورها فقط GET اولیه محافظت می‌شد.
        // عضویت در این لیست یعنی میدلور روی هر درخواست Livewire هم اعمال شود.
        if (class_exists(\Livewire\Livewire::class)) {
            \Livewire::addPersistentMiddleware([
                \Modules\Core\Http\Middleware\AdminMiddleware::class,
                \Modules\Core\Http\Middleware\RoleMiddleware::class,
                \Modules\Core\Http\Middleware\PermissionMiddleware::class,
            ]);
        }
    }

    protected function registerModuleViews(): void
    {
        // نکته: ماژول‌های AdminPanel و Resume ویوهای خود را از طریق
        // BaseServiceProvider (loadViewsFrom) با namespace اختصاصی ثبت می‌کنند؛
        // این لیست فقط برای ماژول‌های اسکلتی است که Provider ندارند.
        $modules = ['Core', 'Orders', 'Blog', 'Tickets', 'Settings', 'Analytics', 'AI', 'Notifications', 'Payments', 'Public'];

        foreach ($modules as $module) {
            $viewPath = base_path("Modules/{$module}/Resources/Views");
            if (is_dir($viewPath)) {
                View::addNamespace(strtolower($module), $viewPath);
            }
        }
    }

    protected function registerAnonymousComponentPaths(): void
    {
        // مسیر کامپوننت‌های anonymous — در Laravel 13 به‌صورت خودکار ثبت نمی‌شود
        // و برای پیدا شدن <x-*> ها (مثل <x-public.layout>) باید صریح ثبت شود
        Blade::anonymousComponentPath(resource_path('views/components'));
    }
}