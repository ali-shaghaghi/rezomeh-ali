<?php

namespace Modules\Tickets\Livewire\UserPanel;

use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\Tickets\Services\UserTicketService;

/**
 * تیکت‌های من — لیست زنده با فیلتر وضعیت (پنل کاربر، ماژول Tickets)
 */
class MyTickets extends Component
{
    use WithPagination;

    #[Url]
    public string $status = '';

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $user = auth()->user();
        $service = app(UserTicketService::class);

        return view('tickets::livewire.user-panel.my-tickets', [
            'tickets' => $service->tickets(
                $user,
                config('userpanel.tickets_per_page', 9),
                $this->status !== '' ? $this->status : null
            ),
        ]);
    }
}
