<?php

namespace Modules\Core\Services\Contracts;

interface SmsServiceInterface
{
    /**
     * Send an SMS message.
     *
     * @param string $phone The recipient phone number
     * @param string $message The message to send
     * @return bool Whether the SMS was sent successfully
     */
    public function send(string $phone, string $message): bool;

    /**
     * Send an OTP code via SMS.
     *
     * @param string $phone The recipient phone number
     * @param string $code The OTP code
     * @param string $type The type of OTP (login, register, password_reset, etc.)
     * @return bool Whether the SMS was sent successfully
     */
    public function sendOtp(string $phone, string $code, string $type): bool;

    /**
     * Check if the SMS service is configured and available.
     *
     * @return bool Whether the service is available
     */
    public function isAvailable(): bool;
}