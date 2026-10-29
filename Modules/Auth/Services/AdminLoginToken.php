<?php

namespace Modules\Auth\Services;

use App\Models\Admin;
use Illuminate\Support\Str;
use Modules\Settings\Models\Setting;

/**
 * توکن مسیر ورود ادمین — مسیر هش‌شده تا کسی نتواند صفحه لاگین ادمین را پیدا کند.
 *
 * دو نوع توکن پذیرفته می‌شود:
 *  1) توکن سراسری (settings.admin_login_token) — مسیر مشترک همه ادمین‌ها.
 *  2) توکن اختصاصی هر ادمین (admins.login_route_token) — «روت اختصاصی» که در
 *     بخش اطلاعات ادمین ذخیره می‌شود و فقط توسط صاحب سایت/سوپر ادمین قابل
 *     تولید مجدد است. ورود با توکن اختصاصی فقط روی همان ادمین قفل می‌شود.
 *
 * نکته: توکن سراسری هرگز از طریق ریدایرکت‌ها لو نمی‌رود — مهمان‌ها به
 * /admin/login (بدون توکن → 404) هدایت می‌شوند.
 */
class AdminLoginToken
{
    /** طول توکن سراسری */
    public const GLOBAL_LENGTH = 48;

    /** طول توکن اختصاصی هر ادمین */
    public const PRIVATE_LENGTH = 40;

    /**
     * توکن سراسری ورود ادمین — اگر تنظیم نشده باشد یکبار تولید و ذخیره می‌شود.
     */
    public static function global(): string
    {
        $token = Setting::get('admin_login_token');

        if (! is_string($token) || $token === '') {
            $token = self::generateGlobal();
            Setting::set('admin_login_token', $token, 'security');
        }

        return $token;
    }

    /**
     * URL مسیر سراسری ورود ادمین (فقط برای استفاده‌های داخلی معتبر).
     */
    public static function globalUrl(): string
    {
        return url('/admin/login/'.self::global());
    }

    /**
     * URL روت اختصاصی یک ادمین (برای نمایش در بخش اطلاعات او).
     */
    public static function urlFor(Admin $admin): string
    {
        $token = $admin->login_route_token ?: self::global();

        return url('/admin/login/'.$token);
    }

    /**
     * آیا توکن داده‌شده معتبر است؟ (سراسری یا اختصاصی یک ادمین)
     */
    public static function isValid(string $token): bool
    {
        if ($token === '') {
            return false;
        }

        if (hash_equals(self::global(), $token)) {
            return true;
        }

        return self::adminFor($token) !== null;
    }

    /**
     * ادمینی که توکن اختصاصی با او تطابق دارد — null یعنی توکن سراسری یا نامعتبر.
     */
    public static function adminFor(string $token): ?Admin
    {
        if ($token === '') {
            return null;
        }

        return Admin::where('login_route_token', $token)->first();
    }

    /**
     * تولید یک توکن اختصاصی جدید (تولید تصادفی / بازتولید هنگام شک به لو رفتن).
     */
    public static function generatePrivate(): string
    {
        return Str::random(self::PRIVATE_LENGTH);
    }

    /**
     * تولید توکن سراسری جدید.
     */
    public static function generateGlobal(): string
    {
        return Str::random(self::GLOBAL_LENGTH);
    }
}
