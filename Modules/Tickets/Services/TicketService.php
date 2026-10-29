<?php

namespace Modules\Tickets\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Tickets\Models\Ticket;
use Modules\Tickets\Models\TicketReply;

class TicketService
{
    /**
     * لیست تیکت‌ها با جستجو، فیلتر و صفحه‌بندی
     */
    public function list(array $filters = [], int $perPage = 9): LengthAwarePaginator
    {
        $query = Ticket::with(['user', 'replies']);

        // جستجو در موضوع، متن و نام کاربر
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%"));
            });
        }

        // فیلتر وضعیت
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // فیلتر اولویت
        if (! empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        // فیلتر دسته
        if (! empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * ذخیره تیکت (ایجاد یا ویرایش)
     */
    public function save(Ticket $ticket, array $data): Ticket
    {
        $isNew = ! $ticket->exists;

        $ticket->fill($data);
        $ticket->save();

        $ticket = $ticket->refresh();

        // اعلان «تیکت جدید» برای ادمین‌ها (ماژول Notifications)
        if ($isNew) {
            try {
                app(\Modules\Notifications\Services\NotificationService::class)
                    ->notifyUsersWithPermission(
                        'view_notifications',
                        \Modules\Notifications\Notifications\NewTicketNotification::class,
                        [
                            'ticket_id' => $ticket->id,
                            'ticket_subject' => $ticket->subject,
                            'user_name' => $ticket->user?->name,
                        ]
                    );
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Notification failed: '.$e->getMessage());
            }
        }

        return $ticket;
    }

    /**
     * حذف امن تیکت (همراه پاسخ‌ها)
     */
    public function delete(Ticket $ticket): bool
    {
        $ticket->replies()->delete();

        return (bool) $ticket->delete();
    }

    /**
     * تغییر وضعیت تیکت
     */
    public function setStatus(Ticket $ticket, string $status): Ticket
    {
        $ticket->update(['status' => $status]);

        return $ticket;
    }

    /**
     * تغییر اولویت تیکت
     */
    public function setPriority(Ticket $ticket, string $priority): Ticket
    {
        $ticket->update(['priority' => $priority]);

        return $ticket;
    }

    /**
     * ثبت پاسخ جدید روی تیکت + به‌روزرسانی خودکار وضعیت
     */
    public function addReply(Ticket $ticket, ?int $userId, string $message, bool $isStaff, ?int $adminId = null): TicketReply
    {
        $reply = TicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id' => $userId,
            'admin_id' => $adminId,
            'message' => $message,
            'is_staff_reply' => $isStaff,
        ]);

        // منطق وضعیت: پاسخ کارمند → در انتظار پاسخ کاربر | پاسخ کاربر → در حال بررسی کارمند
        $ticket->update(['status' => $isStaff ? 'waiting' : 'in_progress']);

        // اعلان «پاسخ جدید به تیکت» (ماژول Notifications)
        try {
            app(\Modules\Notifications\Services\NotificationService::class)
                ->notifyUsersWithPermission(
                    'view_notifications',
                    \Modules\Notifications\Notifications\TicketReplyNotification::class,
                    [
                        'ticket_id' => $ticket->id,
                        'ticket_subject' => $ticket->subject,
                        'responder_name' => $reply->user?->name ?? $reply->admin?->name ?? ($isStaff ? 'کارشناس' : 'کاربر'),
                    ]
                );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Notification failed: '.$e->getMessage());
        }

        return $reply;
    }

    /**
     * آمار کلی تیکت‌ها برای کارت‌های داشبورد ماژول
     */
    public function stats(): array
    {
        return [
            'total' => Ticket::count(),
            'open' => Ticket::where('status', 'open')->count(),
            'in_progress' => Ticket::where('status', 'in_progress')->count(),
            'waiting' => Ticket::where('status', 'waiting')->count(),
            'closed' => Ticket::where('status', 'closed')->count(),
        ];
    }
}
