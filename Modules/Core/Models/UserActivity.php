<?php

namespace Modules\Core\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
        'page',
        'ip_address',
        'user_agent',
        'last_active_at',
    ];

    protected $casts = [
        'last_active_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function isOnline(int $userId, int $minutes = 5): bool
    {
        return static::where('user_id', $userId)
            ->where('last_active_at', '>=', now()->subMinutes($minutes))
            ->exists();
    }

    public static function getOnlineUsers(int $minutes = 5)
    {
        return static::where('last_active_at', '>=', now()->subMinutes($minutes))
            ->with('user')
            ->get()
            ->unique('user_id')
            ->pluck('user')
            ->filter();
    }
}