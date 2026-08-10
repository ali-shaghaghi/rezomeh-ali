<?php

namespace Modules\Core\Services\Contracts;

use Modules\Core\Models\OtpCode;

interface OtpServiceInterface
{
    /**
     * Generate and send an OTP code.
     *
     * @param string $phone The recipient phone number
     * @param string $type The type of OTP
     * @param int $length The length of the OTP code
     * @param int $expiryMinutes The expiry time in minutes
     * @return OtpCode|null The generated OTP code or null if rate limited
     */
    public function generate(string $phone, string $type, int $length = 4, int $expiryMinutes = 2): ?OtpCode;

    /**
     * Verify an OTP code.
     *
     * @param string $phone The phone number
     * @param string $code The OTP code to verify
     * @param string $type The type of OTP
     * @return bool Whether the OTP is valid
     */
    public function verify(string $phone, string $code, string $type): bool;

    /**
     * Check if the user can request a new OTP (rate limiting).
     *
     * @param string $phone The phone number
     * @param string $type The type of OTP
     * @return bool Whether the user can request a new OTP
     */
    public function canRequest(string $phone, string $type): bool;

    /**
     * Get the remaining cooldown time in seconds.
     *
     * @param string $phone The phone number
     * @param string $type The type of OTP
     * @return int The remaining cooldown time in seconds
     */
    public function getCooldownRemaining(string $phone, string $type): int;

    /**
     * Mask the phone number for display.
     *
     * @param string $phone The phone number to mask
     * @return string The masked phone number
     */
    public function maskPhone(string $phone): string;
}