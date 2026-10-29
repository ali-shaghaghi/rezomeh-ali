<?php

namespace Modules\SEO\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * متای SEO هر صفحه — کلید (route_name, locale) یکتاست.
 */
class SeoMeta extends Model
{
    protected $fillable = [
        'route_name',
        'locale',
        'title',
        'description',
        'keywords',
        'og_image',
        'schema_type',
        'faq_json',
        'noindex',
    ];

    protected $casts = [
        'noindex' => 'boolean',
        'faq_json' => 'array',
    ];

    /**
     * متای یک مسیر — یا null (سریع، با کش).
     * ponytail: آرایه کش می‌شود نه مدل — کش‌کردن شیء Eloquent با تغییر
     * namespace/کلاس خراب می‌شد (__PHP_Incomplete_Class).
     */
    public static function for(string $routeName, ?string $locale = null): ?self
    {
        $locale = $locale ?? current_locale();
        $key = "seo_meta_{$routeName}_{$locale}";

        $cached = cache()->get($key);

        // سازگاری با کش‌های قدیمی (شیء سریال‌شده ناسازگار) → دور ریخته می‌شود
        if ($cached instanceof \__PHP_Incomplete_Class) {
            cache()->forget($key);
            $cached = null;
        }

        if (is_array($cached)) {
            return $cached['id'] ? static::find($cached['id']) : null;
        }

        $meta = static::where('route_name', $routeName)->where('locale', $locale)->first();

        cache()->put($key, $meta ? ['id' => $meta->id] : ['id' => null], now()->addHours(6));

        return $meta;
    }

    /**
     * پاک کردن کش هنگام ذخیره
     */
    protected static function booted(): void
    {
        static::saved(fn (self $meta) => cache()->forget("seo_meta_{$meta->route_name}_{$meta->locale}"));
        static::deleted(fn (self $meta) => cache()->forget("seo_meta_{$meta->route_name}_{$meta->locale}"));
    }
}
