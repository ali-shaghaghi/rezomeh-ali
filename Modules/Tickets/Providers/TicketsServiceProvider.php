<?php

namespace Modules\Tickets\Providers;

use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Modules\Core\Providers\BaseServiceProvider;
use Modules\Tickets\Livewire\Admin\TicketShow;
use Modules\Tickets\Livewire\Admin\TicketsIndex;
use Modules\Tickets\Models\Ticket;
use Modules\Tickets\Policies\TicketPolicy;

class TicketsServiceProvider extends BaseServiceProvider
{
    protected function moduleName(): string
    {
        return 'Tickets';
    }

    public function boot(): void
    {
        parent::boot();

        // دیتابیس ماژول تیکت‌ها — خودکفا (مایگریشن‌های tickets/ticket_replies داخل ماژول)
        $this->loadMigrationsFrom(module_path('Tickets', 'Database'.DIRECTORY_SEPARATOR.'Migrations'));

        // روتینگ ماژول تیکت‌ها — خودکفا (ادمین با پیشوند admin/tickets)
        $this->loadRoutesFrom(module_route_path('Tickets', 'admin.php'));

        // روتینگ پنل کاربر — گفتگوی تیکت زیر /dashboard + ریدایرکت‌های 301 قدیمی
        $this->loadRoutesFrom(module_route_path('Tickets', 'web.php'));

        // ثبت Policies (مدل‌های ماژول با کشف خودکار لاراول پیدا نمی‌شوند)
        Gate::policy(Ticket::class, TicketPolicy::class);

        // ثبت کامپوننت‌های Livewire
        Livewire::component('admin.tickets.index', TicketsIndex::class);
        Livewire::component('admin.tickets.show', TicketShow::class);

        // پنل کاربر — تیکت‌های من (شل ریل‌تایم پنل، این کامپوننت‌ها را از ماژول Tickets فراخوانی می‌کند)
        Livewire::component('tickets.my-tickets', \Modules\Tickets\Livewire\UserPanel\MyTickets::class);
        Livewire::component('tickets.ticket-conversation', \Modules\Tickets\Livewire\UserPanel\TicketConversation::class);
    }

    public function register(): void
    {
        parent::register();
    }
}
