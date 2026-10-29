<?php

namespace Modules\ProjectDev\Providers;

use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Modules\Core\Providers\BaseServiceProvider;
use Modules\ProjectDev\Livewire\Admin\ProjectCatalogManager;
use Modules\ProjectDev\Livewire\Admin\ProjectRequestsIndex;
use Modules\ProjectDev\Livewire\Public\ProjectRequestForm;
use Modules\ProjectDev\Models\ProjectRequest;
use Modules\ProjectDev\Models\ProjectType;
use Modules\ProjectDev\Policies\ProjectRequestPolicy;
use Modules\ProjectDev\Policies\ProjectTypePolicy;
use Modules\ProjectDev\Services\ProjectService;

class ProjectDevServiceProvider extends BaseServiceProvider
{
    protected function moduleName(): string
    {
        return 'ProjectDev';
    }

    public function boot(): void
    {
        parent::boot();

        // دیتابیس ماژول توسعه پروژه — خودکفا
        $this->loadMigrationsFrom(module_path('ProjectDev', 'Database'.DIRECTORY_SEPARATOR.'Migrations'));

        // روتینگ ماژول توسعه پروژه — خودکفا
        $this->loadRoutesFrom(module_route_path('ProjectDev', 'web.php'));

        // ثبت Policies (مدل‌های ماژول با کشف خودکار لاراول پیدا نمی‌شوند)
        Gate::policy(ProjectType::class, ProjectTypePolicy::class);
        Gate::policy(ProjectRequest::class, ProjectRequestPolicy::class);

        // ثبت کامپوننت‌های Livewire
        Livewire::component('admin.projectdev.catalog', ProjectCatalogManager::class);
        Livewire::component('admin.projectdev.requests', ProjectRequestsIndex::class);
        Livewire::component('public.projectdev.request-form', ProjectRequestForm::class);

        // پنل کاربر — پروژه‌های من (شل ریل‌تایم پنل، این کامپوننت را از ماژول ProjectDev فراخوانی می‌کند)
        Livewire::component('projectdev.my-projects', \Modules\ProjectDev\Livewire\UserPanel\MyProjects::class);
    }

    public function register(): void
    {
        parent::register();

        // اتصال به Services Hub — سرویس ماژول در دسترس سایر ماژول‌ها قرار می‌گیرد
        // و خودش هم سرویس‌های موردنیاز (SMS) را از Hub کشف می‌کند.
        $this->app->singleton(ProjectService::class, function ($app) {
            $service = new ProjectService($app->make(\Modules\SMS\Services\SmsService::class));

            $app->make(\Modules\Core\Services\ServicesHubService::class)
                ->register('projectdev', fn () => $service);

            return $service;
        });
    }
}
