<?php

namespace Modules\ProjectDev\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Orders\Models\Order;

/**
 * قسط پرداخت (30/30/40) برای سفارش — وضعیت پرداخت هر مرحله
 */
class OrderInstallment extends Model
{
    protected $fillable = [
        'order_id', 'installment_number', 'title', 'amount', 'percent', 'due_date', 'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'percent' => 'integer',
        'due_date' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'paid' => 'پرداخت شده',
            'unpaid' => 'پرداخت نشده',
            'canceled' => 'لغو شده',
            default => 'نامشخص',
        };
    }

    public function getFormattedAmountAttribute(): string
    {
        return number_format((float) $this->amount);
    }
}
