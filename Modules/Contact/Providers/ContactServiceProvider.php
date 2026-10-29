<?php

namespace Modules\Contact\Providers;

use Livewire\Livewire;
use Modules\Contact\Livewire\Admin\ChatManager;
use Modules\Contact\Livewire\FloatingContactWidget;
use Modules\Core\Providers\BaseServiceProvider;

class ContactServiceProvider extends BaseServiceProvider
{
    protected function moduleName(): string
    {
        return 'Contact';
    }

    public function boot(): void
    {
        parent::boot();

        // دیتابیس ماژول تماس — خودکفا
        $this->loadMigrationsFrom(module_path('Contact', 'Database'.DIRECTORY_SEPARATOR.'Migrations'));

        // روتینگ ماژول تماس — خودکفا
        $this->loadRoutesFrom(module_route_path('Contact', 'web.php'));

        // کامپوننت Livewire ویجت شناور (در سه لایوت رندر می‌شود)
        Livewire::component('contact.floating-widget', FloatingContactWidget::class);

        // پنل مدیریت چت آنلاین (#۲۱)
        Livewire::component('contact.admin.chat-manager', ChatManager::class);
    }

    public function register(): void
    {
        parent::register();
    }
}
