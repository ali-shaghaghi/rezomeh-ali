<?php

namespace Modules\SEO\Providers;

use Illuminate\Support\Facades\Route;
use Modules\Core\Providers\BaseServiceProvider;

/**
 * ماژول SEO — خودکفا و قابل استفاده مجدد (#ماژول سئو).
 *
 * قابلیت‌ها:
 *  - متا تگ‌های داینامیک (title, description, canonical, OG, Twitter)
 *  - JSON-LD Schema (Organization, Product, Service, Article, FAQ, Breadcrumb)
 *  - sitemap.xml داینامیک (محصولات، مقالات، صفحات، مشاوره، نمونه‌کار)
 *  - robots.txt پویا + مسیرهای هوش مصنوعی (GPTBot, ClaudeBot, ...)
 *  - مدیریت متا per-page در پنل ادمین (فارسی + انگلیسی)
 *  - کد تأیید سرچ کنسول گوگل
 *
 * قابل استفاده در: فروشگاهی، سازمانی، ویزیت، مشاوره — فقط schema تنظیم شود.
 */
class SeoServiceProvider extends BaseServiceProvider
{
    protected function moduleName(): string
    {
        return 'SEO';
    }

    public function boot(): void
    {
        parent::boot();

        $this->loadMigrationsFrom(module_path('SEO', 'Database'.DIRECTORY_SEPARATOR.'Migrations'));
        $this->loadRoutesFrom(module_route_path('SEO', 'web.php'));
        $this->loadRoutesFrom(module_route_path('SEO', 'admin.php'));

        // کامپوننت‌های Livewire پنل ادمین
        \Livewire\Livewire::component('seo.meta-manager', \Modules\SEO\Livewire\Admin\MetaManager::class);
    }

    public function register(): void
    {
        parent::register();

        // merge config — با module_path (سازگار ویندوز/لینوکس)
        $this->mergeConfigFrom(module_path('SEO', 'config'.DIRECTORY_SEPARATOR.'seo.php'), 'seo');
    }
}
