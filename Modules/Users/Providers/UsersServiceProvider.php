<?php

namespace Modules\Users\Providers;

use Modules\Core\Providers\BaseServiceProvider;

class UsersServiceProvider extends BaseServiceProvider
{
    protected function moduleName(): string
    {
        return 'Users';
    }

    public function boot(): void
    {
        parent::boot();

        // روتینگ ماژول کاربران — خودکفا (ادمین با پیشوند admin/users)
        $this->loadRoutesFrom(module_route_path('Users', 'admin.php'));
    }

    public function register(): void
    {
        parent::register();
    }
}