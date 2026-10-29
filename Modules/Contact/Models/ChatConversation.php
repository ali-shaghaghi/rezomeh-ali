<?php

namespace Modules\Contact\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatConversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'session_id',
        'user_id',
        'guest_name',
        'last_activity_at',
        'status',
        'bot_enabled',
    ];

    protected $casts = [
        'last_activity_at' => 'datetime',
        'bot_enabled' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'conversation_id')->oldest();
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    /**
     * نام نمایشی گفتگو برای لیست ادمین
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->user) {
            return $this->user->name;
        }

        return $this->guest_name ?: 'کاربر مهمان';
    }

    /**
     * آخرین پیام خلاصه برای لیست
     */
    public function getLastMessageAttribute(): ?ChatMessage
    {
        return $this->relationLoaded('messages')
            ? $this->messages->last()
            : $this->messages()->latest('id')->first();
    }
}
