<?php

namespace Modules\Orders\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderTask extends Model
{
    protected $fillable = [
        'order_id',
        'title',
        'description',
        'status',
        'start_date',
        'due_date',
        'progress',
        'sort_order',
    ];

    protected $casts = [
        'start_date' => 'date',
        'due_date' => 'date',
        'progress' => 'integer',
        'sort_order' => 'integer',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'todo' => 'انجام نشده',
            'in_progress' => 'در حال انجام',
            'done' => 'انجام شده',
            default => 'نامشخص',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'todo' => 'secondary',
            'in_progress' => 'primary',
            'done' => 'success',
            default => 'secondary',
        };
    }

    /**
     * وضعیت خودکار از روی درصد پیشرفت
     */
    public function syncStatusFromProgress(): void
    {
        $this->status = match (true) {
            $this->progress >= 100 => 'done',
            $this->progress > 0 => 'in_progress',
            default => 'todo',
        };
        $this->save();
    }
}
