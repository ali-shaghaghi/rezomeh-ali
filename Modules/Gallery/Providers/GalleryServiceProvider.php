<?php

declare(strict_types=1);

namespace Modules\Gallery\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Modules\Gallery\Livewire\Admin\AssetGrid;
use Modules\Gallery\Services\GalleryService;

class GalleryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(GalleryService::class, fn () => new GalleryService());
    }

    public function boot(): void
    {
        $configPath = __DIR__ . '/../config/gallery.php';

        if (file_exists($configPath)) {
            $this->mergeConfigFrom($configPath, 'gallery');
        }

        $this->loadRoutesFrom(__DIR__ . '/../Routes/admin.php');
        $this->loadRoutesFrom(__DIR__ . '/../Routes/public.php');

        $this->loadViewsFrom(__DIR__ . '/../Resources/Views', 'gallery');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        Livewire::component('admin.asset-grid', AssetGrid::class);
    }
}
