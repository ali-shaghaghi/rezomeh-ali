<?php

namespace Modules\AdminPanel\Providers;

use Illuminate\Support\Facades\View;
use Modules\Core\Providers\BaseServiceProvider;

class AdminPanelServiceProvider extends BaseServiceProvider
{
    protected function moduleName(): string
    {
        return 'AdminPanel';
    }

    public function boot(): void
    {
        parent::boot();

        // روتینگ پنل ادمین — خودکفا (کاربران در ماژول Users، رزومه در ماژول Resume و...)
        $this->loadRoutesFrom(module_route_path('AdminPanel', 'admin.php'));

        // Alias سازگاری موقت: admin:: → adminpanel:: (تا همه ارجاع‌ها به‌روزرسانی شوند)
        View::addNamespace('admin', module_view_path('AdminPanel'));
    }

    public function register(): void
    {
        parent::register();
    }
}
