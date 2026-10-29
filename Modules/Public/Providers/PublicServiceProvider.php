<?php

namespace Modules\Public\Providers;

use Livewire\Livewire;
use Modules\Core\Providers\BaseServiceProvider;
use Modules\Public\Livewire\ContactForm;

class PublicServiceProvider extends BaseServiceProvider
{
    protected function moduleName(): string
    {
        return 'Public';
    }

    public function boot(): void
    {
        parent::boot();

        // روت‌های عمومی سایت — بدون middleware ادمین (فرانت‌اند)
        // نکته: پنل کاربر به ماژول UserPanel منتقل شد (Modules/UserPanel)
        $this->loadRoutesFrom(module_route_path('Public', 'web.php'));

        // کامپوننت Livewire فرم تماس
        Livewire::component('public.contact-form', ContactForm::class);
    }

    public function register(): void
    {
        parent::register();
    }
}
