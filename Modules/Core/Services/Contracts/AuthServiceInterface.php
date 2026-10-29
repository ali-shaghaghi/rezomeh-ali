<?php

namespace Modules\Core\Services\Contracts;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

interface AuthServiceInterface
{
    /**
     * Attempt to authenticate a user with email/phone and password.
     *
     * @param Request $request
     * @return User|null The authenticated user or null
     */
    public function attemptLogin(Request $request): ?User;

    /**
     * Check if 2FA is required for the user.
     *
     * @param User $user
     * @return bool
     */
    public function requiresTwoFactor(User $user): bool;

    /**
     * Complete the login process after 2FA verification.
     *
     * @param User $user
     * @param Request $request
     * @return void
     */
    public function completeLogin(User $user, Request $request): void;

    /**
     * Log the user out.
     *
     * @param Request $request
     * @return void
     */
    public function logout(Request $request): void;

    /**
     * Send a password reset link to the user's email.
     *
     * @param string $email
     * @return bool
     */
    public function sendPasswordResetLink(string $email): bool;

    /**
     * Reset the user's password.
     *
     * @param Request $request
     * @return bool
     */
    public function resetPassword(Request $request): bool;

    /**
     * Verify the user's email address.
     *
     * @param User $user
     * @param string $code
     * @return bool
     */
    public function verifyEmail(User $user, string $code): bool;

    /**
     * Send email verification notification.
     *
     * @param User $user
     * @return bool
     */
    public function sendEmailVerificationNotification(User $user): bool;

    /**
     * Verify the user's phone number.
     *
     * @param User $user
     * @param string $otp
     * @return bool
     */
    public function verifyPhone(User $user, string $otp): bool;

    /**
     * Send phone verification OTP.
     *
     * @param User $user
     * @return bool
     */
    public function sendPhoneVerificationOtp(User $user): bool;

    /**
     * Enable two-factor authentication for the user.
     *
     * @param User $user
     * @param string $otp
     * @return bool
     */
    public function enableTwoFactor(User $user, string $otp): bool;

    /**
     * Disable two-factor authentication for the user.
     *
     * @param User $user
     * @param string $password
     * @return bool
     */
    public function disableTwoFactor(User $user, string $password): bool;

    /**
     * Verify the two-factor authentication code.
     *
     * @param User $user
     * @param string $code
     * @return bool
     */
    public function verifyTwoFactorCode(User $user, string $code): bool;

    /**
     * Generate recovery codes for 2FA.
     *
     * @param User $user
     * @return array
     */
    public function generateRecoveryCodes(User $user): array;

    /**
     * Verify a recovery code.
     *
     * @param User $user
     * @param string $code
     * @return bool
     */
    public function verifyRecoveryCode(User $user, string $code): bool;

    /**
     * Get the user by email.
     *
     * @param string $email
     * @return User|null
     */
    public function getUserByEmail(string $email): ?User;

    /**
     * Get the user by phone.
     *
     * @param string $phone
     * @return User|null
     */
    public function getUserByPhone(string $phone): ?User;
}