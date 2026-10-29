<?php

namespace Modules\Tickets\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Modules\Tickets\Models\Ticket;
use Modules\Tickets\Models\TicketReply;

/**
 * سرویس تیکت‌های «پنل کاربر» — فقط داده‌های خودِ کاربر لاگین‌شده
 * (منطق از UserPanelService به ماژول Tickets منتقل شد).
 */
class UserTicketService
{
    public function __construct(
        private readonly TicketService $ticketService,
    ) {}

    /**
     * تیکت‌های کاربر — صفحه‌بندی‌شده + فیلتر وضعیت
     */
    public function tickets(User $user, int $perPage = 9, ?string $status = null): LengthAwarePaginator
    {
        $query = Ticket::where('user_id', $user->id)->withCount('replies');

        if ($status) {
            $query->where('status', $status);
        }

        return $query->latest()->latest('id')->paginate($perPage)->withQueryString();
    }

    /**
     * تیکتِ خودِ کاربر یا null
     */
    public function ticket(User $user, string $ticketId): ?Ticket
    {
        if (! ctype_digit($ticketId)) {
            return null;
        }

        return Ticket::where('user_id', $user->id)
            ->where('id', $ticketId)
            ->with(['user', 'replies.user'])
            ->first();
    }

    /**
     * ایجاد تیکت جدید توسط کاربر
     */
    public function createTicket(User $user, array $data): Ticket
    {
        $ticket = new Ticket;
        $ticket->fill([
            'user_id' => $user->id,
            'subject' => $data['subject'],
            'message' => $data['message'],
            'priority' => $data['priority'] ?? 'medium',
            'category' => $data['category'] ?? 'general',
            'status' => 'open',
        ]);
        $ticket->save();

        return $ticket->refresh();
    }

    /**
     * پاسخ کاربر روی تیکت خودش (کارشناس نیست)
     */
    public function replyTicket(User $user, Ticket $ticket, string $message): TicketReply
    {
        return DB::transaction(function () use ($user, $ticket, $message) {
            $ownedTicket = Ticket::where('user_id', $user->id)->lockForUpdate()->findOrFail($ticket->id);

            abort_if($ownedTicket->status === 'closed', 404);

            return $this->ticketService->addReply($ownedTicket, $user->id, $message, false);
        });
    }

    /**
     * بستن تیکت توسط خود کاربر
     */
    public function closeTicket(User $user, Ticket $ticket): Ticket
    {
        return DB::transaction(function () use ($user, $ticket) {
            $ownedTicket = Ticket::where('user_id', $user->id)->lockForUpdate()->findOrFail($ticket->id);

            return $this->ticketService->setStatus($ownedTicket, 'closed');
        });
    }
}
