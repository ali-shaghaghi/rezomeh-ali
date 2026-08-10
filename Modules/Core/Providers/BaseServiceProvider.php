<?php

namespace Modules\Core\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\AliasLoader;

abstract class BaseServiceProvider extends ServiceProvider
{
    /**
     * Module name.
     */
    abstract protected function moduleName(): string;

    /**
     * Boot the module's services.
     */
    public function boot(): void
    {
        $this->registerViews();
        $this->registerTranslations();
        $this->registerConfig();
    }

    /**
     * Register the module's services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            module_config_path($this->moduleName(), 'config.php'),
            strtolower($this->moduleName())
        );
    }

    /**
     * Register views.
     */
    protected function registerViews(): void
    {
        $viewPath = module_view_path($this->moduleName());
        $this->loadViewsFrom($viewPath, strtolower($this->moduleName()));

        if ($this->app->runningInConsole()) {
            $this->publishes([
                $viewPath => resource_path('views/vendor/' . strtolower($this->moduleName())),
            ], strtolower($this->moduleName()) . '-views');
        }
    }

    /**
     * Register translations.
     */
    protected function registerTranslations(): void
    {
        $langPath = module_lang_path($this->moduleName());
        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, strtolower($this->moduleName()));
        }
    }

    /**
     * Register config.
     */
    protected function registerConfig(): void
    {
        $configPath = module_config_path($this->moduleName(), 'config.php');
        if (file_exists($configPath)) {
            $this->publishes([
                $configPath => config_path(strtolower($this->moduleName()) . '.php'),
            ], strtolower($this->moduleName()) . '-config');
        }
    }
}