<?php

namespace Modules\UserPanel\Providers;

use Livewire\Livewire;
use Modules\Core\Providers\BaseServiceProvider;
use Modules\UserPanel\Livewire\Dashboard;
use Modules\UserPanel\Livewire\PanelShell;

class UserPanelServiceProvider extends BaseServiceProvider
{
    protected function moduleName(): string
    {
        return 'UserPanel';
    }

    public function boot(): void
    {
        parent::boot();

        // روتینگ پنل کاربر — خودکفا (همه مسیرها زیر /dashboard)
        $this->loadRoutesFrom(module_route_path('UserPanel', 'web.php'));

        // ثبت کامپوننت‌های Livewire پنل کاربر — UserPanel فقط داشبورد + شل ریل‌تایم
        Livewire::component('userpanel.dashboard', Dashboard::class);
        Livewire::component('userpanel.panel-shell', PanelShell::class);
    }

    public function register(): void
    {
        parent::register();
    }
}
