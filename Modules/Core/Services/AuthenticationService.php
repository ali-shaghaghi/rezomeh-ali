<?php

namespace Modules\Core\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Modules\Core\Models\LoginAttempt;
use Modules\Core\Models\OtpCode;
use App\Models\User;
use Modules\Core\Services\Contracts\AuthServiceInterface;
use Modules\Core\Services\OtpService;

class AuthenticationService implements AuthServiceInterface
{
    protected OtpService $otpService;
    protected TwoFactorService $twoFactorService;

    public function __construct(
        OtpService $otpService,
        TwoFactorService $twoFactorService
    ) {
        $this->otpService = $otpService;
        $this->twoFactorService = $twoFactorService;
    }

    /**
     * Attempt to authenticate a user with email/phone and password.
     */
    public function attemptLogin(Request $request): ?User
    {
        $credential = $request->input('credential');
        $password = $request->input('password');
        $loginType = $request->input('login_type', 'email');

        // Determine if credential is email or phone
        $user = $this->resolveUser($credential, $loginType);

        if (!$user) {
            $this->logAttempt(null, $credential, $request, false, 'user_not_found');
            throw ValidationException::withMessages([
                'credential' => 'کاربری با این مشخصات یافت نشد.',
            ]);
        }

        // Check if user is active
        if (!$user->is_active) {
            $this->logAttempt($user, $credential, $request, false, 'account_disabled');
            throw ValidationException::withMessages([
                'credential' => 'حساب کاربری شما غیرفعال است.',
            ]);
        }

        // Check if account is locked due to too many failed attempts
        if (LoginAttempt::hasExceededMaxAttemptsByEmail($user->email, 5, 15)) {
            $this->logAttempt($user, $credential, $request, false, 'too_many_attempts');
            throw ValidationException::withMessages([
                'credential' => 'تعداد تلاش‌های ناموفق بیش از حد مجاز است. لطفاً ۱۵ دقیقه صبر کنید.',
            ]);
        }

        // Verify password
        if (!Hash::check($password, $user->password)) {
            $this->logAttempt($user, $credential, $request, false, 'invalid_password');
            throw ValidationException::withMessages([
                'credential' => 'رمز عبور صحیح نیست.',
            ]);
        }

        // Log successful attempt
        $this->logAttempt($user, $credential, $request, true);

        return $user;
    }

    /**
     * Check if 2FA is required for the user.
     */
    public function requiresTwoFactor(User $user): bool
    {
        return $user->two_factor_enabled;
    }

    /**
     * Complete the login process after 2FA verification.
     */
    public function completeLogin(User $user, Request $request): void
    {
        // Regenerate session
        $request->session()->regenerate();

        // Login the user
        Auth::login($user, $request->boolean('remember'));

        // Update last login info
        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);
    }

    /**
     * Log the user out.
     */
    public function logout(Request $request): void
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    /**
     * Send a password reset link to the user's email.
     */
    public function sendPasswordResetLink(string $email): bool
    {
        $user = $this->getUserByEmail($email);

        if (!$user) {
            // Return true even if user doesn't exist to prevent email enumeration
            return true;
        }

        // Generate OTP for password reset
        $otp = $this->otpService->generate($user->phone, 'password_reset', 6, 15);

        return $otp !== null;
    }

    /**
     * Reset the user's password.
     */
    public function resetPassword(Request $request): bool
    {
        $request->validate([
            'phone' => 'required|string',
            'otp' => 'required|string|size:6',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Verify OTP
        if (!$this->otpService->verify($request->phone, $request->otp, 'password_reset')) {
            throw ValidationException::withMessages([
                'otp' => 'کد بازیابی صحیح نیست یا منقضی شده است.',
            ]);
        }

        // Find user and update password
        $user = $this->getUserByPhone($request->phone);

        if (!$user) {
            return false;
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return true;
    }

    /**
     * Verify the user's email address.
     */
    public function verifyEmail(User $user, string $code): bool
    {
        // For now, we'll use a simple verification code
        // In production, you might want to use Laravel's built-in email verification
        if ($user->email_verified_at) {
            return true;
        }

        // Generate and verify OTP
        return $this->otpService->verify($user->phone, $code, 'email_verify');
    }

    /**
     * Send email verification notification.
     */
    public function sendEmailVerificationNotification(User $user): bool
    {
        if ($user->email_verified_at) {
            return true;
        }

        // Send OTP to phone for email verification
        $otp = $this->otpService->generate($user->phone, 'email_verify', 6, 15);

        return $otp !== null;
    }

    /**
     * Verify the user's phone number.
     */
    public function verifyPhone(User $user, string $otp): bool
    {
        if ($user->phone_verified_at) {
            return true;
        }

        return $this->otpService->verify($user->phone, $otp, 'phone_verify');
    }

    /**
     * Send phone verification OTP.
     */
    public function sendPhoneVerificationOtp(User $user): bool
    {
        if ($user->phone_verified_at) {
            return true;
        }

        $otp = $this->otpService->generate($user->phone, 'phone_verify', 6, 15);

        return $otp !== null;
    }

    /**
     * Enable two-factor authentication for the user.
     */
    public function enableTwoFactor(User $user, string $otp): bool
    {
        return $this->twoFactorService->enable($user, $otp);
    }

    /**
     * Disable two-factor authentication for the user.
     */
    public function disableTwoFactor(User $user, string $password): bool
    {
        return $this->twoFactorService->disable($user, $password);
    }

    /**
     * Verify the two-factor authentication code.
     */
    public function verifyTwoFactorCode(User $user, string $code): bool
    {
        return $this->twoFactorService->verifyCode($user, $code);
    }

    /**
     * Generate recovery codes for 2FA.
     */
    public function generateRecoveryCodes(User $user): array
    {
        return $this->twoFactorService->generateRecoveryCodes($user);
    }

    /**
     * Verify a recovery code.
     */
    public function verifyRecoveryCode(User $user, string $code): bool
    {
        return $this->twoFactorService->verifyRecoveryCode($user, $code);
    }

    /**
     * Get the user by email.
     */
    public function getUserByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    /**
     * Get the user by phone.
     */
    public function getUserByPhone(string $phone): ?User
    {
        return User::where('phone', $phone)->first();
    }

    /**
     * Resolve user from credential.
     */
    protected function resolveUser(string $credential, string $loginType): ?User
    {
        return match ($loginType) {
            'phone' => $this->getUserByPhone($credential),
            default => $this->getUserByEmail($credential),
        };
    }

    /**
     * Log a login attempt.
     */
    protected function logAttempt(?User $user, string $credential, Request $request, bool $isSuccessful, string $failureReason = null): void
    {
        LoginAttempt::log([
            'user_id' => $user?->id,
            'email' => $user?->email,
            'phone' => $user?->phone,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'is_successful' => $isSuccessful,
            'failure_reason' => $failureReason,
        ]);
    }
}