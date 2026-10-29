<?php

namespace Modules\Tickets\Http\Controllers;

use App\Http\Controllers\Controller;

class UserPanelTicketController extends Controller
{
    /**
     * گفتگوی تیکت من — صفحه میزبان کامپوننت Livewire ماژول Tickets
     * (خودِ داده و دسترسی داخل TicketConversation با UserTicketService چک می‌شود).
     */
    public function show(string $ticketId)
    {
        return view('userpanel::tickets.show', ['ticketId' => $ticketId]);
    }
}
