<?php

namespace Modules\Auth\Services;

use App\Models\User;

/**
 * تصمیم‌گیری مرحله بعدی پس از تایید رمز عبور در جریان ورود.
 *
 * همه مراحل اختیاری‌اند و به‌صورت پیش‌فرض غیرفعال‌اند؛ کاربر از تنظیمات
 * امنیتی (کنار تغییر رمز عبور) خودش فعال می‌کند:
 *
 *   1. کد ورود یک‌بارمصرف (login_otp_enabled)  → مرحله OTP ایمیل
 *   2. ورود دو عاملی  (two_factor_enabled)     → مرحله کد دومرحله‌ای
 *   3. هیچ‌کدام فعال نیست                      → ورود مستقیم (رمز عبور کافی است)
 */
class LoginFlowService
{
    public const STEP_OTP = 'otp';

    public const STEP_TWO_FACTOR = 'two_factor';

    public const STEP_DIRECT = 'direct';

    public static function next(User $user): string
    {
        // کلید اضطراری هاست: وجود فایل storage/app/otp_off → ورود مستقیم بدون OTP و دو عاملی
        if (file_exists(storage_path('app/otp_off'))) {
            return self::STEP_DIRECT;
        }

        if ($user->login_otp_enabled) {
            return self::STEP_OTP;
        }

        if ($user->two_factor_enabled) {
            return self::STEP_TWO_FACTOR;
        }

        return self::STEP_DIRECT;
    }
}
