<?php

namespace Modules\Contact\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'subject',
        'message',
        'ip_address',
        'user_agent',
        'user_id',
        'status',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ===== Scopes =====

    public function scopeUnread($query)
    {
        return $query->where('status', 'new');
    }

    // ===== Accessors =====

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'new' => 'جدید',
            'read' => 'خوانده‌شده',
            'replied' => 'پاسخ داده‌شده',
            'archived' => 'بایگانی‌شده',
            default => 'نامشخص',
        };
    }

    public function getIsUnreadAttribute(): bool
    {
        return $this->status === 'new';
    }

    /**
     * خلاصه پیام برای نمایش در لیست
     */
    public function getExcerptAttribute(): string
    {
        return \Illuminate\Support\Str::limit($this->message, 80);
    }
}
