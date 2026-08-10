<?php

namespace Modules\Core\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class OtpService
{
    /**
     * Generate OTP code for user.
     */
    public function generate(User $user, string $type = 'login'): string
    {
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Store OTP in cache with expiration (store as ISO string, not Carbon object)
        $key = "otp:{$user->id}:{$type}";
        Cache::put($key, [
            'code' => Hash::make($code),
            'expires_at' => now()->addMinutes(5)->toIso8601String(),
            'attempts' => 0,
        ], now()->addMinutes(5));

        return $code;
    }

    /**
     * Verify OTP code.
     */
    public function verify(User $user, string $code, string $type = 'login'): bool
    {
        $key = "otp:{$user->id}:{$type}";
        $otp = Cache::get($key);

        if (!$otp) {
            return false;
        }

        // Check expiration (parse ISO string back to Carbon)
        $expiresAt = \Carbon\Carbon::parse($otp['expires_at']);
        if (now()->isAfter($expiresAt)) {
            Cache::forget($key);
            return false;
        }

        // Check max attempts (5)
        if ($otp['attempts'] >= 5) {
            Cache::forget($key);
            return false;
        }

        // Increment attempts
        $otp['attempts']++;
        Cache::put($key, $otp, now()->addMinutes(5));

        // Verify code
        if (Hash::check($code, $otp['code'])) {
            Cache::forget($key);
            return true;
        }

        return false;
    }

    /**
     * Check if user can request new OTP (rate limiting).
     */
    public function canRequest(User $user, string $type = 'login'): bool
    {
        $key = "otp:cooldown:{$user->id}:{$type}";
        return !Cache::has($key);
    }

    /**
     * Set cooldown for OTP request.
     */
    public function setCooldown(User $user, string $type = 'login', int $seconds = 60): void
    {
        $key = "otp:cooldown:{$user->id}:{$type}";
        $timeKey = "otp:cooldown_time:{$user->id}:{$type}";
        Cache::put($key, true, now()->addSeconds($seconds));
        Cache::put($timeKey, now()->timestamp, now()->addSeconds($seconds));
    }

    /**
     * Get remaining cooldown time in seconds.
     */
    public function getCooldownRemaining(User $user, string $type = 'login'): int
    {
        $key = "otp:cooldown:{$user->id}:{$type}";
        if (!Cache::has($key)) {
            return 0;
        }
        // Store creation time to calculate remaining
        $timeKey = "otp:cooldown_time:{$user->id}:{$type}";
        $startTime = Cache::get($timeKey, now()->subSeconds(60)->timestamp);
        $elapsed = now()->timestamp - $startTime;
        return max(0, 60 - $elapsed);
    }

    /**
     * Mask phone number for display.
     */
    public function maskPhone(string $phone): string
    {
        if (strlen($phone) < 7) {
            return $phone;
        }
        return substr($phone, 0, 3) . '****' . substr($phone, -2);
    }

    /**
     * Mask email for display.
     */
    public function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return $email;
        }
        $name = $parts[0];
        $domain = $parts[1];
        $masked = substr($name, 0, 2) . str_repeat('*', max(0, strlen($name) - 2));
        return $masked . '@' . $domain;
    }
}