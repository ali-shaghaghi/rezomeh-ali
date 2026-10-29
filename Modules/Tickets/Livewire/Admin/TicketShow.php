<?php

namespace Modules\Tickets\Livewire\Admin;

use Livewire\Component;
use App\Models\Admin;
use Modules\Tickets\Models\Ticket;
use Modules\Tickets\Services\TicketService;

class TicketShow extends Component
{
    public Ticket $ticket;

    // فرم پاسخ
    public string $replyMessage = '';

    public bool $replying = false;

    // تغییر وضعیت سریع
    public string $newStatus = 'open';

    // نوتیفیکیشن
    public bool $successToast = false;

    public string $successMessage = '';

    public function mount(Ticket $ticket): void
    {
        $this->ticket = $ticket->load(['user', 'replies.user']);
        $this->newStatus = $ticket->status ?? 'open';
    }

    // ثبت پاسخ کارشناس
    public function sendReply(): void
    {
        $this->authorize('update', $this->ticket);

        $validated = $this->validate([
            'replyMessage' => 'required|string|min:2|max:2000',
        ]);

        // پاسخ کارشناس: شناسه ادمین در admin_id ثبت می‌شود (هرگز در جدول users)
        $identity = auth()->user();
        $isAdmin = $identity instanceof Admin;

        app(TicketService::class)->addReply(
            $this->ticket,
            $isAdmin ? null : auth()->id(),
            $validated['replyMessage'],
            true, // پاسخ کارشناس
            $isAdmin ? $identity->id : null
        );

        $this->replyMessage = '';
        $this->ticket->refresh();
        $this->newStatus = $this->ticket->status;

        $this->notifySuccess('پاسخ شما ثبت شد و وضعیت تیکت به «در انتظار پاسخ» تغییر کرد.');
    }

    // تغییر وضعیت تیکت
    public function changeStatus(): void
    {
        $this->authorize('update', $this->ticket);

        $validated = $this->validate([
            'newStatus' => 'required|in:open,in_progress,waiting,closed',
        ]);

        app(TicketService::class)->setStatus($this->ticket, $validated['newStatus']);
        $this->ticket->refresh();

        $this->notifySuccess('وضعیت تیکت به «'.$this->ticket->status_label.'» تغییر کرد.');
    }

    // نمایش نوتیفیکیشن موفقیت
    private function notifySuccess(string $message): void
    {
        $this->successMessage = $message;
        $this->successToast = true;
    }

    public function render()
    {
        return view('tickets::livewire.admin.ticket-show');
    }
}
