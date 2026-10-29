<?php

namespace Modules\Orders\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\ProjectDev\Models\OrderInstallment;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_number',
        'title',
        'description',
        'status',
        'amount',
        'paid_amount',
        'priority',
        'deadline',
        'start_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'deadline' => 'date',
        'start_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(OrderTask::class)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * آیتم‌های محصول این سفارش (ماژول Products)
     */
    public function items(): HasMany
    {
        return $this->hasMany(\Modules\Products\Models\OrderItem::class);
    }

    /**
     * قسط‌های پرداخت 30/30/40 (ماژول Projects)
     */
    public function installments(): HasMany
    {
        return $this->hasMany(OrderInstallment::class)->orderBy('installment_number');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function getStatusLabelAttribute(): string
    {
        return __('orders.status_'.$this->status);
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'warning',
            'review' => 'info',
            'accepted' => 'primary',
            'development' => 'violet',
            'completed' => 'success',
            'cancelled' => 'danger',
            default => 'secondary',
        };
    }

    public function getPriorityLabelAttribute(): string
    {
        return match ($this->priority) {
            'low' => 'کم',
            'medium' => 'متوسط',
            'high' => 'زیاد',
            'urgent' => 'فوری',
            default => 'متوسط',
        };
    }

    public function getIsPaidAttribute(): bool
    {
        return (float) $this->paid_amount >= (float) $this->amount && (float) $this->amount > 0;
    }

    /**
     * درصد پیشرفت پروژه (تسک‌محور) — میانگین وزنی پیشرفت تسک‌ها؛
     * بدون تسک: از روی وضعیت (completed=100, development/accepted=50, ...=0)
     */
    public function getProgressPercentAttribute(): int
    {
        $tasks = $this->tasks;

        if ($tasks->isNotEmpty()) {
            return (int) round($tasks->avg('progress'));
        }

        return match ($this->status) {
            'completed' => 100,
            'development' => 60,
            'accepted' => 30,
            'review' => 10,
            default => 0,
        };
    }

    /**
     * تعداد تسک‌های انجام‌شده / کل
     */
    public function getDoneTasksCountAttribute(): int
    {
        return $this->tasks->where('status', 'done')->count();
    }

    /**
     * تعداد روز مانده تا ددلاین (منفی = گذشته)
     */
    public function getDaysRemainingAttribute(): ?int
    {
        if (! $this->deadline) {
            return null;
        }

        return (int) ceil(now()->startOfDay()->diffInDays($this->deadline, false));
    }

    /**
     * برچسب وضعیت ددلاین برای UI
     */
    public function getDeadlineStatusAttribute(): string
    {
        $days = $this->days_remaining;

        if ($days === null) {
            return 'none';
        }

        return match (true) {
            $days < 0 => 'overdue',
            $days <= 3 => 'urgent',
            $days <= 10 => 'soon',
            default => 'ok',
        };
    }

    /**
     * مرتب‌سازی ددلاین: نزدیک‌ترین ددلاین اول (بعد از تسک‌های در حال توسعه)
     */
    public function scopeOrderByDeadline($query, string $direction = 'asc')
    {
        return $query->orderByRaw('ISNULL(deadline) ' . ($direction === 'asc' ? 'ASC' : 'DESC'))
            ->orderBy('deadline', $direction);
    }
}
