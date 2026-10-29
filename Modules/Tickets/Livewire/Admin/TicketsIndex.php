<?php

namespace Modules\Tickets\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\Tickets\Models\Ticket;
use Modules\Tickets\Services\TicketService;

class TicketsIndex extends Component
{
    use WithPagination;

    // فیلترها
    public string $search = '';

    public string $status = '';

    public string $priority = '';

    // مودال حذف
    public bool $confirmingDelete = false;

    public $deleteId = null;

    // نوتیفیکیشن
    public bool $successToast = false;

    public string $successMessage = '';

    // آغاز با فیلتر وضعیت (برای صفحه «باز»)
    public function mount(string $initialStatus = ''): void
    {
        $this->status = $initialStatus;
    }

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'priority' => ['except' => ''],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingPriority(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $service = app(TicketService::class);

        $tickets = $service->list([
            'search' => $this->search,
            'status' => $this->status,
            'priority' => $this->priority,
        ]);

        return view('tickets::livewire.admin.tickets-index', [
            'tickets' => $tickets,
            'stats' => $service->stats(),
        ]);
    }

    // تغییر سریع وضعیت (ریاکتیو)
    public function quickStatus(int $id, string $status): void
    {
        $ticket = Ticket::findOrFail($id);
        $this->authorize('update', $ticket);

        app(TicketService::class)->setStatus($ticket, $status);
        $this->notifySuccess('وضعیت تیکت «'.$ticket->subject.'» به «'.$ticket->status_label.'» تغییر کرد.');
    }

    // تغییر سریع اولویت (ریاکتیو)
    public function quickPriority(int $id, string $priority): void
    {
        $ticket = Ticket::findOrFail($id);
        $this->authorize('update', $ticket);

        app(TicketService::class)->setPriority($ticket, $priority);
        $this->notifySuccess('اولویت تیکت «'.$ticket->subject.'» به «'.$ticket->priority_label.'» تغییر کرد.');
    }

    public function confirmDelete($id): void
    {
        $this->deleteId = $id;
        $this->confirmingDelete = true;
    }

    public function deleteTicket(): void
    {
        $ticket = Ticket::findOrFail($this->deleteId);
        $this->authorize('delete', $ticket);

        app(TicketService::class)->delete($ticket);

        $this->confirmingDelete = false;
        $this->deleteId = null;
        $this->notifySuccess('تیکت «'.$ticket->subject.'» با موفقیت حذف شد.');
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'status', 'priority']);
        $this->resetPage();
    }

    // نمایش نوتیفیکیشن موفقیت
    private function notifySuccess(string $message): void
    {
        $this->successMessage = $message;
        $this->successToast = true;
    }
}
