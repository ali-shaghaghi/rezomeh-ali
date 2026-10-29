<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoginAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'email',
        'phone',
        'ip_address',
        'user_agent',
        'is_successful',
        'failure_reason',
    ];

    protected $casts = [
        'is_successful' => 'boolean',
    ];

    /**
     * Get the user that owns the login attempt.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the IP address has exceeded the maximum login attempts.
     */
    public static function hasExceededMaxAttempts(string $ipAddress, int $maxAttempts = 5, int $timeWindowMinutes = 15): bool
    {
        return static::where('ip_address', $ipAddress)
            ->where('is_successful', false)
            ->where('created_at', '>', now()->subMinutes($timeWindowMinutes))
            ->count() >= $maxAttempts;
    }

    /**
     * Check if the email has exceeded the maximum login attempts.
     */
    public static function hasExceededMaxAttemptsByEmail(string $email, int $maxAttempts = 5, int $timeWindowMinutes = 15): bool
    {
        return static::where('email', $email)
            ->where('is_successful', false)
            ->where('created_at', '>', now()->subMinutes($timeWindowMinutes))
            ->count() >= $maxAttempts;
    }

    /**
     * Check if the phone has exceeded the maximum login attempts.
     */
    public static function hasExceededMaxAttemptsByPhone(string $phone, int $maxAttempts = 5, int $timeWindowMinutes = 15): bool
    {
        return static::where('phone', $phone)
            ->where('is_successful', false)
            ->where('created_at', '>', now()->subMinutes($timeWindowMinutes))
            ->count() >= $maxAttempts;
    }

    /**
     * Log a login attempt.
     */
    public static function log(array $data): self
    {
        return static::create(array_merge($data, [
            'ip_address' => $data['ip_address'] ?? request()->ip(),
            'user_agent' => $data['user_agent'] ?? request()->userAgent(),
        ]));
    }
}