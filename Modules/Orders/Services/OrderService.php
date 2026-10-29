<?php

namespace Modules\Orders\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Modules\Orders\Models\Order;
use Modules\Orders\Models\Payment;

class OrderService
{
    /**
     * لیست سفارش‌ها با جستجو، فیلتر، مرتب‌سازی ددلاین و صفحه‌بندی
     */
    public function list(array $filters = [], int $perPage = 9): LengthAwarePaginator
    {
        $query = Order::with(['user', 'payments', 'tasks']);

        // جستجو در شماره سفارش، عنوان و نام کاربر
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
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

        // مرتب‌سازی — پیش‌فرض: نزدیک‌ترین ددلاین (برنامه کاری)
        $sort = $filters['sort'] ?? 'deadline';
        $query->when($sort === 'deadline', fn ($q) => $q->orderByRaw('deadline IS NULL ASC')->orderBy('deadline', 'asc'))
            ->when($sort === 'deadline_desc', fn ($q) => $q->orderByRaw('deadline IS NULL ASC')->orderBy('deadline', 'desc'))
            ->when($sort === 'newest', fn ($q) => $q->latest())
            ->when($sort === 'amount', fn ($q) => $q->orderByDesc('amount'))
            ->when($sort === 'status', fn ($q) => $q->orderByRaw("FIELD(status, 'development', 'accepted', 'review', 'pending', 'completed', 'cancelled')"));

        return $query->paginate($perPage);
    }

    /**
     * تولید شماره سفارش یکتا
     */
    public function generateOrderNumber(): string
    {
        do {
            $number = 'ORD-'.strtoupper(Str::random(8));
        } while (Order::where('order_number', $number)->exists());

        return $number;
    }

    /**
     * ذخیره سفارش (ایجاد یا ویرایش)
     */
    public function save(Order $order, array $data): Order
    {
        $isNew = ! $order->exists;

        // برای سفارش جدید شماره تولید می‌شود
        if ($isNew && empty($data['order_number'])) {
            $data['order_number'] = $this->generateOrderNumber();
        }

        $order->fill($data);
        $order->save();

        $order = $order->refresh();

        // اعلان «سفارش جدید» برای ادمین‌ها (ماژول Notifications)
        if ($isNew) {
            try {
                app(\Modules\Notifications\Services\NotificationService::class)
                    ->notifyUsersWithPermission(
                        'view_notifications',
                        \Modules\Notifications\Notifications\NewOrderNotification::class,
                        [
                            'order_id' => $order->id,
                            'order_number' => $order->order_number,
                            'order_title' => $order->title,
                            'amount' => (float) $order->amount,
                            'user_name' => $order->user?->name,
                        ]
                    );
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Notification failed: '.$e->getMessage());
            }
        }

        return $order;
    }

    /**
     * حذف امن سفارش (همراه روابط)
     */
    public function delete(Order $order): bool
    {
        $order->payments()->delete();

        return (bool) $order->delete();
    }

    /**
     * تغییر وضعیت سفارش
     */
    public function setStatus(Order $order, string $status): Order
    {
        $order->update(['status' => $status]);

        return $order;
    }

    /**
     * تغییر اولویت سفارش
     */
    public function setPriority(Order $order, string $priority): Order
    {
        $order->update(['priority' => $priority]);

        return $order;
    }

    /**
     * ثبت پرداخت برای سفارش + به‌روزرسانی مبلغ پرداخت‌شده
     */
    public function addPayment(Order $order, array $data): Payment
    {
        $payment = Payment::create([
            'user_id' => $data['user_id'] ?? $order->user_id,
            'order_id' => $order->id,
            'amount' => $data['amount'],
            'status' => $data['status'] ?? 'completed',
            'payment_method' => $data['payment_method'] ?? null,
            'transaction_id' => $data['transaction_id'] ?? null,
            'description' => $data['description'] ?? null,
            'paid_at' => ($data['status'] ?? 'completed') === 'completed' ? now() : null,
        ]);

        // به‌روزرسانی مبلغ پرداخت‌شده سفارش (فقط پرداخت‌های موفق)
        if ($payment->status === 'completed') {
            $order->paid_amount = $order->payments()->completed()->sum('amount');
            $order->save();

            // اعلان «پرداخت دریافت شد» (ماژول Notifications)
            try {
                app(\Modules\Notifications\Services\NotificationService::class)
                    ->notifyUsersWithPermission(
                        'view_notifications',
                        \Modules\Notifications\Notifications\PaymentReceivedNotification::class,
                        [
                            'order_id' => $order->id,
                            'order_number' => $order->order_number,
                            'amount' => (float) $payment->amount,
                        ]
                    );
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Notification failed: '.$e->getMessage());
            }
        }

        return $payment;
    }

    /**
     * آمار کلی سفارش‌ها برای کارت‌های داشبورد ماژول
     */
    public function stats(): array
    {
        return [
            'total' => Order::count(),
            'pending' => Order::where('status', 'pending')->count(),
            'development' => Order::where('status', 'development')->count(),
            'completed' => Order::where('status', 'completed')->count(),
            'cancelled' => Order::where('status', 'cancelled')->count(),
            'total_revenue' => (float) Payment::completed()->sum('amount'),
        ];
    }
}
