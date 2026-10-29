<?php

namespace Modules\Core\Models;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BlogPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'admin_id', // نویسنده عضو تیم (وقتی مقاله از پنل ادمین نوشته می‌شود)
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'featured_image_alt',
        'status',
        'seo_title',
        'seo_description',
        'views',
        'published_at',
        // دو‌زبانه — محتوا
        'title_en',
        'excerpt_en',
        'content_en',
        // دو‌زبانه — سئو
        'seo_title_en',
        'seo_description_en',
    ];

    /**
     * L-1 پنتست: پاک‌سازی HTML مقاله هنگام ذخیره.
     *
     * محتوای مقاله با {!! !!} خروجی داده می‌شود؛ این هوک تگ‌های مجری،
     * رویدادهای on* و URIهای اسکریپتی را قبل از ذخیره حذف می‌کند تا
     * XSS ماندگار (مثلاً از طریق اکانت ادمین به‌خطرافتاده) ممکن نباشد.
     */
    protected static function booted(): void
    {
        static::saving(function (BlogPost $post) {
            foreach (['content', 'content_en'] as $field) {
                if (is_string($post->{$field}) && $post->{$field} !== '') {
                    $post->{$field} = static::sanitizeHtml($post->{$field});
                }
            }
        });
    }

    /**
     * حذف تگ‌های مجری، رویدادهای جاوااسکریپت و URIهای خطرناک از HTML.
     * تگ‌های مجاز فرمت‌دهی (p, a, img, h1..h6, ul, ...) دست‌نخورده می‌مانند.
     * منطق مشترک در هیلپر sanitize_rich_html زندگی می‌کند (مقاله و محصول هر دو از آن استفاده می‌کنند).
     */
    public static function sanitizeHtml(string $html): string
    {
        return sanitize_rich_html($html);
    }

    /**
     * عنوان مقاله بر اساس زبان فعال (با fallback به فارسی).
     */
    public function localizedTitle(): string
    {
        return localized_field($this, 'title') ?? $this->title;
    }

    /**
     * خلاصه مقاله بر اساس زبان فعال (با fallback).
     */
    public function localizedExcerpt(): ?string
    {
        return localized_field($this, 'excerpt');
    }

    /**
     * محتوای مقاله بر اساس زبان فعال (با fallback).
     */
    public function localizedContent(): ?string
    {
        return localized_field($this, 'content');
    }

    /**
     * عنوان سئو بر اساس زبان فعال (با fallback به عنوان فارسی/انگلیسی).
     */
    public function localizedSeoTitle(): string
    {
        return localized_field($this, 'seo_title')
            ?? localized_field($this, 'title')
            ?? $this->title;
    }

    /**
     * توضیح متای سئو بر اساس زبان فعال (با fallback).
     */
    public function localizedSeoDescription(): ?string
    {
        return localized_field($this, 'seo_description') ?? localized_field($this, 'excerpt');
    }

    /**
     * آیا نسخه انگلیسی این مقاله پر شده است؟
     */
    public function hasEnglishVersion(): bool
    {
        return has_en_version($this, 'title')
            || has_en_version($this, 'excerpt')
            || has_en_version($this, 'content');
    }

    protected $casts = [
        'published_at' => 'datetime',
        'views' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * نویسنده عضو تیم (admins) — وقتی مقاله در پنل ادمین نوشته شده باشد.
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'پیش‌نویس',
            'published' => 'منتشر شده',
            default => 'نامشخص',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'warning',
            'published' => 'success',
            default => 'secondary',
        };
    }
}
