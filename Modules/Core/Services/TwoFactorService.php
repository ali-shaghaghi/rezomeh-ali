<?php

namespace Modules\Core\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\Core\Models\OtpCode;
use App\Models\User;

class TwoFactorService
{
    protected OtpService $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    /**
     * Enable two-factor authentication for the user.
     */
    public function enable(User $user, string $otp): bool
    {
        // Verify the OTP
        if (!$this->otpService->verify($user->phone, $otp, 'two_factor_enable')) {
            return false;
        }

        // Generate recovery codes
        $recoveryCodes = $this->generateRecoveryCodes($user);

        // Enable 2FA
        $user->update([
            'two_factor_enabled' => true,
            'two_factor_recovery_codes' => json_encode($recoveryCodes),
        ]);

        return true;
    }

    /**
     * Disable two-factor authentication for the user.
     */
    public function disable(User $user, string $password): bool
    {
        // Verify password
        if (!Hash::check($password, $user->password)) {
            return false;
        }

        // Disable 2FA
        $user->update([
            'two_factor_enabled' => false,
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
        ]);

        return true;
    }

    /**
     * Verify the two-factor authentication code.
     */
    public function verifyCode(User $user, string $code): bool
    {
        return $this->otpService->verify($user->phone, $code, 'two_factor');
    }

    /**
     * Generate a new 2FA OTP for the user.
     */
    public function generateOtp(User $user): bool
    {
        $otp = $this->otpService->generate($user->phone, 'two_factor', 6, 2);

        return $otp !== null;
    }

    /**
     * Check if the user can request a new 2FA OTP.
     */
    public function canRequestOtp(User $user): bool
    {
        return $this->otpService->canRequest($user->phone, 'two_factor');
    }

    /**
     * Get the remaining cooldown time for 2FA OTP.
     */
    public function getCooldownRemaining(User $user): int
    {
        return $this->otpService->getCooldownRemaining($user->phone, 'two_factor');
    }

    /**
     * Generate recovery codes for 2FA.
     */
    public function generateRecoveryCodes(User $user): array
    {
        $codes = [];
        for ($i = 0; $i < 8; $i++) {
            $codes[] = Str::upper(Str::random(4) . '-' . Str::random(4));
        }

        // Hash the codes before storing
        $hashedCodes = array_map(fn($code) => Hash::make($code), $codes);

        $user->update([
            'two_factor_recovery_codes' => json_encode($hashedCodes),
        ]);

        return $codes;
    }

    /**
     * Verify a recovery code.
     */
    public function verifyRecoveryCode(User $user, string $code): bool
    {
        $recoveryCodes = json_decode($user->two_factor_recovery_codes, true) ?? [];

        foreach ($recoveryCodes as $index => $hashedCode) {
            if (Hash::check($code, $hashedCode)) {
                // Remove the used recovery code
                unset($recoveryCodes[$index]);
                $user->update([
                    'two_factor_recovery_codes' => json_encode(array_values($recoveryCodes)),
                ]);

                return true;
            }
        }

        return false;
    }

    /**
     * Get the masked phone number for 2FA display.
     */
    public function getMaskedPhone(User $user): string
    {
        return OtpCode::maskPhone($user->phone);
    }
}