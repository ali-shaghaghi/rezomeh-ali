<?php

namespace Modules\Tickets\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function replies(): HasMany
    {
        return $this->hasMany(TicketReply::class)->oldest();
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeUnresolved($query)
    {
        return $query->whereIn('status', ['open', 'in_progress', 'waiting']);
    }

    public function getStatusLabelAttribute(): string
    {
        return __('tickets.status_'.$this->status);
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'open' => 'success',
            'in_progress' => 'info',
            'waiting' => 'warning',
            'closed' => 'secondary',
            default => 'secondary',
        };
    }

    public function getPriorityLabelAttribute(): string
    {
        return __('tickets.priority_'.$this->priority);
    }
}
