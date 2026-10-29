<?php

namespace Modules\Tickets\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Modules\Tickets\Models\Ticket;

class TicketController extends Controller
{
    use AuthorizesRequests;

    /**
     * لیست تیکت‌ها (Livewire)
     */
    public function index()
    {
        $this->authorize('viewAny', Ticket::class);

        return view('tickets::index');
    }

    /**
     * تیکت‌های باز (همان Livewire با فیلتر پیش‌فرض)
     */
    public function open()
    {
        $this->authorize('viewAny', Ticket::class);

        return view('tickets::open');
    }

    /**
     * نمایش مستقل تیکت (Livewire)
     */
    public function show(Ticket $ticket)
    {
        $this->authorize('view', $ticket);

        return view('tickets::show', compact('ticket'));
    }
}
