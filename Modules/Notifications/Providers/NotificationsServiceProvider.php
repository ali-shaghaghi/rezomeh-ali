<?php

namespace Modules\Notifications\Providers;

use Livewire\Livewire;
use Modules\Core\Providers\BaseServiceProvider;

class NotificationsServiceProvider extends BaseServiceProvider
{
    protected function moduleName(): string
    {
        return 'Notifications';
    }

    public function boot(): void
    {
        parent::boot();

        // مایگریشن‌های ماژول اعلان‌ها — خودکفا
        $this->loadMigrationsFrom(module_path('Notifications', 'Database'.DIRECTORY_SEPARATOR.'Migrations'));

        // روتینگ ادمین
        $this->loadRoutesFrom(module_route_path('Notifications', 'admin.php'));

        // روتینگ پنل کاربر
        $this->loadRoutesFrom(module_route_path('Notifications', 'web.php'));

        // ثبت کامپوننت‌های Livewire
        Livewire::component('notifications.bell', \Modules\Notifications\Livewire\Admin\NotificationsBell::class);
        Livewire::component('notifications.list', \Modules\Notifications\Livewire\Admin\NotificationsList::class);
        Livewire::component('notifications.user', \Modules\Notifications\Livewire\User\UserNotifications::class);
    }

    public function register(): void
    {
        parent::register();

        // سرویس اعلان‌ها به‌صورت Singleton
        $this->app->singleton(\Modules\Notifications\Services\NotificationService::class);
    }
}
