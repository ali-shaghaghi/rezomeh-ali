<?php

namespace Modules\Resume\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResumeProfile extends Model
{
    use HasFactory;

    // پروفایل رزومه — singleton (فقط یک رکورد استفاده می‌شود)
    protected $fillable = [
        'name',
        'job_title',
        'location',
        'email',
        'github_url',
        'linkedin_url',
        'resume_file',
        'avatar',
        'typewriter_texts',
        'bio',
        'is_active',
        // فیلدهای دو‌زبانه (انگلیسی) — اختیاری؛ در صورت خالی بودن مقدار فارسی نمایش داده می‌شود
        'name_en',
        'job_title_en',
        'location_en',
        'bio_en',
        'typewriter_texts_en',
        // شبکه‌های اجتماعی (آیکون اختصاصی هر کدام در رزومه نمایش داده می‌شود)
        'telegram_url',
        'whatsapp_url',
        'instagram_url',
        'rubika_url',
        'bale_url',
        'eita_url',
        'phone',
    ];

    protected $casts = [
        'typewriter_texts' => 'array',
        'typewriter_texts_en' => 'array',
        'is_active' => 'boolean',
    ];

    // دریافت پروفایل فعال (singleton) — در صورت نبود، رکورد پیش‌فرض می‌سازد
    public static function current(): self
    {
        return static::where('is_active', true)->first()
            ?? static::create([
                'name' => 'علی شقاقی',
                'job_title' => 'توسعه‌دهنده وب',
                'typewriter_texts' => ['توسعه‌دهنده وب', 'متخصص Laravel'],
                'is_active' => true,
            ]);
    }

    /**
     * نام نمایشی بر اساس زبان فعال (با fallback به فارسی).
     */
    public function localizedName(): string
    {
        return localized_field($this, 'name') ?? '';
    }

    /**
     * عنوان شغلی بر اساس زبان فعال.
     */
    public function localizedJobTitle(): ?string
    {
        return localized_field($this, 'job_title');
    }

    /**
     * محل اقامت بر اساس زبان فعال.
     */
    public function localizedLocation(): ?string
    {
        return localized_field($this, 'location');
    }

    /**
     * توضیحات کوتاه (bio) بر اساس زبان فعال.
     */
    public function localizedBio(): ?string
    {
        return localized_field($this, 'bio');
    }

    /**
     * آیا نسخه انگلیسی پروفایل پر شده است؟
     * برای نمایش/مخفی کردن گزینه زبان بالای بخش رزومه استفاده می‌شود.
     */
    public function hasEnglishVersion(): bool
    {
        return has_en_version($this, 'name')
            || has_en_version($this, 'job_title')
            || has_en_version($this, 'bio');
    }
}
