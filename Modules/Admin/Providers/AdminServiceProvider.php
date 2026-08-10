<?php

namespace Modules\Admin\Providers;

use Modules\Core\Providers\BaseServiceProvider;

class AdminServiceProvider extends BaseServiceProvider
{
    /**
     * Module name.
     */
    protected function moduleName(): string
    {
        return 'Admin';
    }

    /**
     * Boot the module's services.
     */
    public function boot(): void
    {
        parent::boot();
        $this->loadRoutesFrom(module_route_path('Admin', 'web.php'));
    }

    /**
     * Register the module's services.
     */
    public function register(): void
    {
        parent::register();
    }
}