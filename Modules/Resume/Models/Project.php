<?php

namespace Modules\Resume\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\Core\Models\Category;

// استفاده از مدل مهارت در رابطه پروژه ↔ مهارت

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'short_description',
        'description',
        'thumbnail',
        'cover_image',
        'cover_image_en',
        'gallery',
        'category_id',
        'related_product_id',
        'client_name',
        'project_date',
        'github_url',
        'demo_url',
        'demo_type',
        'video_url',
        'status',
        'featured',
        'order',
        'seo_title',
        'seo_description',
        // دو‌زبانه
        'title_en',
        'slug_en',
        'short_description_en',
        'description_en',
        'seo_title_en',
        'seo_description_en',
    ];

    /**
     * عنوان پروژه بر اساس زبان فعال (با fallback به فارسی).
     */
    public function localizedTitle(): string
    {
        return localized_field($this, 'title') ?? $this->title;
    }

    /**
     * توضیحات کوتاه بر اساس زبان فعال (با fallback).
     */
    public function localizedShortDescription(): ?string
    {
        return localized_field($this, 'short_description');
    }

    /**
     * توضیحات کامل بر اساس زبان فعال (با fallback).
     */
    public function localizedDescription(): ?string
    {
        return localized_field($this, 'description');
    }

    /**
     * عنوان SEO بر اساس زبان فعال (با fallback به فارسی).
     */
    public function localizedSeoTitle(): string
    {
        return localized_field($this, 'seo_title') ?? $this->seo_title ?? $this->title;
    }

    /**
     * توضیحات SEO بر اساس زبان فعال (با fallback).
     */
    public function localizedSeoDescription(): ?string
    {
        return localized_field($this, 'seo_description') ?? $this->seo_description;
    }

    protected $casts = [
        'featured' => 'boolean',
        'status' => 'string',
        'gallery' => 'array',
        'project_date' => 'date',
        'order' => 'integer',
        'demo_type' => 'string',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * محصول مرتبط — مقصد دکمهٔ «جزئیات پروژه» در صفحه اصلی و نمونه‌کارها.
     */
    public function relatedProduct(): BelongsTo
    {
        return $this->belongsTo(\Modules\Products\Models\Product::class, 'related_product_id');
    }

    /**
     * کاور نمایشی بر اساس زبان فعال: نسخه انگلیسی (در صورت وجود) ← کاور اصلی ← تصویر کوچک قدیمی.
     */
    public function localizedCover(): ?string
    {
        if (current_locale() === 'en' && ! empty($this->cover_image_en)) {
            return $this->cover_image_en;
        }

        return $this->cover_image ?? $this->thumbnail;
    }

    public function technologies(): BelongsToMany
    {
        return $this->belongsToMany(Technology::class, 'project_technology');
    }

    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'project_skill');
    }

    // اسکوپ‌ها

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->latest('project_date');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status ?? 'draft') {
            'draft' => 'پیش‌نویس',
            'published' => 'منتشر شده',
            'archived' => 'آرشیو شده',
        };
    }

    public function getIsFeaturedAttribute(): bool
    {
        return $this->featured === true;
    }

    // نوع دمو با مقدار پیش‌فرض
    public function getDemoTypeAttribute(): string
    {
        return $this->attributes['demo_type'] ?? 'link';
    }

    // آدرس دموی نهایی پروژه (بر اساس نوع دمو)
    public function getDemoLinkAttribute(): ?string
    {
        $url = $this->demo_url;
        if (! $url) {
            return null;
        }

        // در حالت subdomain آینده، آدرس نسبی به دامنه اصلی متصل می‌شود
        if (($this->attributes['demo_type'] ?? 'link') === 'subdomain' && ! filter_var($url, FILTER_VALIDATE_URL)) {
            return 'https://'.$url;
        }

        return $url;
    }
}
