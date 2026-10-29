<?php

namespace Modules\Tickets\Livewire\UserPanel;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\Tickets\Services\UserTicketService;

/**
 * جزئیات تیکت من + گفتگوی زنده — ایجاد تیکت، پاسخ و بستن (پنل کاربر، ماژول Tickets)
 */
class TicketConversation extends Component
{
    use WithPagination;

    public \Modules\Tickets\Models\Ticket $ticket;

    public string $newReply = '';

    // ---------- فرم تیکت جدید (فقط در حالت create) ----------
    public bool $isCreate = false;

    public bool $formOpen = false;

    public string $subject = '';

    public string $message = '';

    public string $priority = 'medium';

    public string $category = 'general';

    public function mount(string $ticketId = '', bool $create = false): void
    {
        $this->isCreate = $create;

        if ($create) {
            $this->formOpen = true;

            return;
        }

        $ticket = app(UserTicketService::class)->ticket(auth()->user(), $ticketId);

        abort_unless($ticket, 404);

        $this->ticket = $ticket;
    }

    public function toggleForm(): void
    {
        $this->formOpen = ! $this->formOpen;
    }

    public function saveTicket(): void
    {
        $validated = $this->validate([
            'subject' => ['required', 'string', 'min:3', 'max:190'],
            'message' => ['required', 'string', 'min:10', 'max:3000'],
            'priority' => ['nullable', 'in:low,medium,high,urgent'],
            'category' => ['nullable', 'in:general,order,billing,technical,suggestion'],
        ], [
            'subject.required' => 'موضوع تیکت الزامی است.',
            'subject.min' => 'موضوع باید حداقل ۳ کاراکتر باشد.',
            'message.required' => 'متن تیکت الزامی است.',
            'message.min' => 'متن تیکت باید حداقل ۱۰ کاراکتر باشد.',
        ]);

        $ticket = app(UserTicketService::class)->createTicket(auth()->user(), $validated);

        // رفتن به گفتگوی تیکت تازه — بدون رفرش کامل صفحه
        $this->redirect(route('user.tickets.show', $ticket->id), navigate: true);
    }

    public function reply(): void
    {
        $validated = $this->validate([
            'newReply' => ['required', 'string', 'min:2', 'max:3000'],
        ], [
            'newReply.required' => 'متن پاسخ الزامی است.',
            'newReply.min' => 'متن پاسخ باید حداقل ۲ کاراکتر باشد.',
        ]);

        app(UserTicketService::class)->replyTicket(auth()->user(), $this->ticket, $validated['newReply']);

        $this->newReply = '';
        $this->ticket->refresh();
        $this->dispatch('$refresh');
    }

    public function closeTicket(): void
    {
        app(UserTicketService::class)->closeTicket(auth()->user(), $this->ticket);

        $this->ticket->refresh();
        $this->dispatch('$refresh');
    }

    public function render()
    {
        // در حالت create فقط فرم ثبت رندر می‌شود
        if ($this->isCreate) {
            return view('tickets::livewire.user-panel.ticket-create');
        }

        $replies = $this->ticket->replies()->with('user')->oldest()->paginate(5);

        return view('tickets::livewire.user-panel.ticket-conversation', [
            'replies' => $replies,
        ]);
    }
}
