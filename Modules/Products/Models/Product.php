<?php

namespace Modules\Products\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Products\Enums\ProductType;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'short_description',
        // دو‌زبانه (اختیاری — fallback روی فارسی)
        'name_en',
        'description_en',
        'short_description_en',
        'price',
        'sale_price',
        'type',
        'file_path',
        'demo_url',
        'version',
        'is_published',
        'show_in_resume',
        'cover_image',
        'order',
    ];

    /**
     * پاک‌سازی HTML توضیحات هنگام ذخیره — همان هوک امنیتی BlogPost:
     * محتوای ویرایشگر غنی (توضیحات کامل/انگلیسی) نباید اجازه اجرای اسکریپت بدهد.
     */
    protected static function booted(): void
    {
        static::saving(function (Product $product): void {
            foreach (['description', 'description_en'] as $field) {
                if (is_string($product->{$field}) && $product->{$field} !== '') {
                    $product->{$field} = sanitize_rich_html($product->{$field});
                }
            }
        });
    }

    /**
     * نام محصول بر اساس زبان فعال (با fallback به فارسی).
     */
    public function localizedName(): string
    {
        return localized_field($this, 'name') ?? $this->name;
    }

    /**
     * توضیحات کوتاه بر اساس زبان فعال.
     */
    public function localizedShortDescription(): ?string
    {
        return localized_field($this, 'short_description');
    }

    /**
     * توضیحات کامل بر اساس زبان فعال.
     */
    public function localizedDescription(): ?string
    {
        return localized_field($this, 'description');
    }

    /**
     * آیا نسخه انگلیسی این محصول پر شده است؟
     */
    public function hasEnglishVersion(): bool
    {
        return ! empty($this->name_en) || ! empty($this->description_en);
    }

    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'is_published' => 'boolean',
        'show_in_resume' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('order');
    }

    public function licenses(): HasMany
    {
        return $this->hasMany(License::class);
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeShowInResume($query)
    {
        return $query->where('show_in_resume', true)->published();
    }

    /**
     * نوع محصول به‌صورت Enum
     */
    public function typeEnum(): ProductType
    {
        return ProductType::tryFrom($this->type) ?? ProductType::Other;
    }

    public function getTypeLabelAttribute(): string
    {
        return $this->typeEnum()->localizedLabel();
    }

    /**
     * قیمت نهایی — اگر تخفیف (sale_price) تعریف شده باشد از آن استفاده می‌شود
     */
    public function getFinalPriceAttribute(): float
    {
        if ($this->sale_price !== null && $this->sale_price > 0 && $this->sale_price < $this->price) {
            return (float) $this->sale_price;
        }

        return (float) $this->price;
    }

    /**
     * آیا محصول در تخفیف است؟
     */
    public function getHasSaleAttribute(): bool
    {
        return $this->sale_price !== null
            && $this->sale_price > 0
            && $this->sale_price < $this->price;
    }

    /**
     * درصد تخفیف
     */
    public function getDiscountPercentAttribute(): int
    {
        if (! $this->has_sale || (float) $this->price <= 0) {
            return 0;
        }

        return (int) round(100 - ($this->final_price / (float) $this->price) * 100);
    }

    /**
     * قیمت بر اساس نوع لایسنس (ضریب لایسنس اعمال می‌شود)
     */
    public function priceForLicense(string $licenseType): float
    {
        $multiplier = \Modules\Products\Enums\LicenseType::tryFrom($licenseType)?->multiplier() ?? 1;

        return round($this->final_price * $multiplier);
    }

    /**
     * فایل دانلودی موجود است؟
     */
    public function getIsDownloadableAttribute(): bool
    {
        return ! empty($this->file_path);
    }

    public function getCoverUrlAttribute(): string
    {
        if (! empty($this->cover_image)) {
            return asset('storage/'.$this->cover_image);
        }

        return asset('img/placeholder.svg');
    }
}
