<?php

namespace Modules\Core\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'deadline' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'در انتظار بررسی',
            'review' => 'در حال بررسی',
            'accepted' => 'پذیرفته شده',
            'development' => 'در حال توسعه',
            'completed' => 'تکمیل شده',
            'cancelled' => 'لغو شده',
            default => 'نامشخص',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'warning',
            'review' => 'info',
            'accepted' => 'primary',
            'development' => 'violet',
            'completed' => 'success',
            'cancelled' => 'danger',
            default => 'secondary',
        };
    }
}