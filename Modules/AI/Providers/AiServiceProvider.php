<?php

namespace Modules\AI\Providers;

use Livewire\Livewire;
use Modules\AI\Livewire\Admin\ArticleWriter;
use Modules\AI\Livewire\Admin\ProjectAssistant;
use Modules\AI\Livewire\Admin\TelegramPublisher;
use Modules\Core\Providers\BaseServiceProvider;

/**
 * ماژول AI — دستیار هوشمند ادمین (#فاز AI).
 *
 * سه بخش:
 *  ۱) ArticleWriter   → تولید مقاله دو‌زبانه بر اساس قالب سایت
 *  ۲) ProjectAssistant → مدیریت/پیشنهاد برای پروژه‌ها و سفارش‌ها
 *  ۳) TelegramPublisher → اتصال تلگرام + انتشار خودکار مقالات/محصولات جدید + کمپین
 */
class AiServiceProvider extends BaseServiceProvider
{
    protected function moduleName(): string
    {
        return 'AI';
    }

    public function boot(): void
    {
        parent::boot();

        $this->loadMigrationsFrom(module_path('AI', 'Database'.DIRECTORY_SEPARATOR.'Migrations'));
        $this->loadRoutesFrom(module_route_path('AI', 'admin.php'));

        Livewire::component('ai.article-writer', ArticleWriter::class);
        Livewire::component('ai.project-assistant', ProjectAssistant::class);
        Livewire::component('ai.telegram-publisher', TelegramPublisher::class);
    }
}
