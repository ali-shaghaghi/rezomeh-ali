<?php

namespace Modules\Settings\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Setting extends Model
{
    use HasFactory;

    /**
     * کلیدهایی که مقدارشان باید در دیتابیس رمزنگاری شود (یافتهٔ L-3 پنتست).
     * خواندن شفاف انجام می‌شود؛ نوشتن خودکار رمزنگاری می‌شود.
     */
    protected const SECRET_KEYS = ['sms_api_key'];

    protected $fillable = [
        'group',
        'key',
        'value',
        'is_public',
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    // دریافت مقدار یک تنظیم (با کش)
    public static function get(string $key, $default = null)
    {
        $settings = static::allCached();

        return $settings[$key]['value'] ?? $default;
    }

    // تنظیم/به‌روزرسانی یک تنظیم
    public static function set(string $key, $value, string $group = 'general', bool $isPublic = false): void
    {
        // L-3: کلیدهای محرمانه قبل از ذخیره رمزنگاری می‌شوند (جلوگیری از دوباره‌رمزنگاری)
        if (static::isSecret($key) && is_string($value) && $value !== '' && ! static::looksEncrypted($value)) {
            $value = Crypt::encryptString($value);
        }

        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group, 'is_public' => $isPublic]
        );

        static::flushCache();
    }

    /** آیا این کلید باید محرمانه رمزگشایی شود؟ */
    protected static function isSecret(string $key): bool
    {
        return in_array($key, self::SECRET_KEYS, true);
    }

    /** رمزگشایی شفاف — مقدار قدیمی رمزنگاری‌نشده همان‌طور برمی‌گردد */
    protected static function decryptSecret(string $key, $value)
    {
        if (! static::isSecret($key) || ! is_string($value) || $value === '') {
            return $value;
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Throwable) {
            return $value;
        }
    }

    /** آیا مقدار از قبل رمزنگاری شده است؟ (برای جلوگیری از double-encrypt هنگام re-save) */
    protected static function looksEncrypted(string $value): bool
    {
        try {
            Crypt::decryptString($value);

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    // دریافت همه تنظیمات یک گروه (با کش)
    public static function group(string $group): array
    {
        return collect(static::allCached())
            ->filter(fn (array $setting) => $setting['group'] === $group)
            ->map(fn (array $setting) => $setting['value'])
            ->toArray();
    }

    // کش همه تنظیمات — فقط آرایه ساده، نه آبجکت مدل (سازگار با serializable_classes در Laravel 11.35+)
    public static function allCached(): array
    {
        // اگر کش قدیمی شامل آبجکت مدل باشد unserialize خطا می‌دهد — کش خراب نادیده گرفته می‌شود
        try {
            $cached = cache()->get('settings.all');
        } catch (\Throwable) {
            $cached = null;
        }

        if (is_array($cached)) {
            return $cached;
        }

        $fresh = static::all()
            ->keyBy('key')
            ->map(fn (self $setting) => [
                // کلیدهای محرمانه اینجا رمزگشایی می‌شوند تا همهٔ خوانندگان شفاف ببینند
                'value' => static::decryptSecret($setting->key, $setting->value),
                'group' => $setting->group,
            ])
            ->toArray();

        cache()->put('settings.all', $fresh, now()->addHours(12));

        return $fresh;
    }

    // پاک کردن کش
    public static function flushCache(): void
    {
        cache()->forget('settings.all');
    }

    // مقادیر بولین
    public static function getBool(string $key, bool $default = false): bool
    {
        return filter_var(static::get($key, $default), FILTER_VALIDATE_BOOLEAN);
    }
}
