<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class OtpCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'phone',
        'code',
        'type',
        'is_used',
        'expires_at',
        'used_at',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'is_used' => 'boolean',
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    /**
     * Get the user that owns the OTP code.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the OTP code is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if the OTP code is valid (not used and not expired).
     */
    public function isValid(): bool
    {
        return !$this->is_used && !$this->isExpired();
    }

    /**
     * Generate a new OTP code.
     */
    public static function generate(string $phone, string $type, int $length = 4, int $expiryMinutes = 2): self
    {
        // Invalidate any existing OTP codes for this phone and type
        static::where('phone', $phone)
            ->where('type', $type)
            ->where('is_used', false)
            ->update(['is_used' => true]);

        // Generate the code
        $code = str_pad(random_int(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);

        return static::create([
            'phone' => $phone,
            'code' => $code,
            'type' => $type,
            'is_used' => false,
            'expires_at' => now()->addMinutes($expiryMinutes),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Verify an OTP code.
     */
    public static function verify(string $phone, string $code, string $type): ?self
    {
        $otp = static::where('phone', $phone)
            ->where('code', $code)
            ->where('type', $type)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->first();

        if ($otp) {
            $otp->update([
                'is_used' => true,
                'used_at' => now(),
            ]);
        }

        return $otp;
    }

    /**
     * Check if the user can request a new OTP (rate limiting).
     */
    public static function canRequest(string $phone, string $type, int $cooldownSeconds = 60): bool
    {
        $lastOtp = static::where('phone', $phone)
            ->where('type', $type)
            ->latest()
            ->first();

        if (!$lastOtp) {
            return true;
        }

        return $lastOtp->created_at->addSeconds($cooldownSeconds)->isPast();
    }

    /**
     * Get the remaining cooldown time in seconds.
     */
    public static function getCooldownRemaining(string $phone, string $type, int $cooldownSeconds = 60): int
    {
        $lastOtp = static::where('phone', $phone)
            ->where('type', $type)
            ->latest()
            ->first();

        if (!$lastOtp) {
            return 0;
        }

        $remaining = $lastOtp->created_at->addSeconds($cooldownSeconds)->diffInSeconds(now());

        return max(0, $remaining);
    }

    /**
     * Mask the phone number for display.
     */
    public static function maskPhone(string $phone): string
    {
        $length = strlen($phone);
        if ($length <= 4) {
            return $phone;
        }

        return substr($phone, 0, 3) . str_repeat('*', $length - 5) . substr($phone, -2);
    }
}