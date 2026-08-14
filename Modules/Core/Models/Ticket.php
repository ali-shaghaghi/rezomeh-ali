<?php

namespace Modules\Core\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subject',
        'message',
        'status',
        'priority',
        'category',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'open' => 'باز',
            'in_progress' => 'در حال بررسی',
            'waiting' => 'در انتظار پاسخ',
            'closed' => 'بسته شده',
            default => 'نامشخص',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'open' => 'success',
            'in_progress' => 'info',
            'waiting' => 'warning',
            'closed' => 'secondary',
            default => 'secondary',
        };
    }

    public function getPriorityLabelAttribute(): string
    {
        return match($this->priority) {
            'low' => 'پایین',
            'medium' => 'متوسط',
            'high' => 'بالا',
            'urgent' => 'فوری',
            default => 'متوسط',
        };
    }
}