<?php

declare(strict_types=1);

namespace Modules\Gallery\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

/**
 * تصویر/ویدئوی ذخیره‌شده در گالری — همهٔ تصاویر سایت از این جدول عبور می‌کنند.
 *
 * - فایل روی دیسک خصوصی (upload_private) ذخیره می‌شود؛
 * - نمایش عمومی فقط از مسیر هش‌شده: /gallery/{public_hash} (URL سئو‌پسند و غیرقابل‌حدس).
 */
class Asset extends Model
{
    use HasFactory;

    public const TABLE = 'gallery_assets';

    public const PUBLIC_DISK = 'gallery_public';

    public const UPLOAD_DISK = 'upload_private';

    protected $table = self::TABLE;

    protected $fillable = [
        'slug',
        'alt',
        'meta_title',
        'meta_description',
        'public_hash',
        'uploader_path',
        'original_name',
        'mime_type',
        'size_bytes',
        'width',
        'height',
        'order_column',
        'duration_ms',
        'is_video',
        'is_processed',
        'uploaded_by',
        'source_id',
        'source_type',
    ];

    protected $casts = [
        'size_bytes' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'order_column' => 'integer',
        'duration_ms' => 'integer',
        'is_video' => 'boolean',
        'is_processed' => 'boolean',
        'uploaded_by' => 'integer',
        'source_id' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(static function (self $asset): void {
            foreach (['alt', 'meta_title', 'meta_description'] as $field) {
                if (is_string($asset->{$field})) {
                    $asset->{$field} = trim(strip_tags($asset->{$field}));
                }
            }

            if (empty($asset->slug)) {
                $asset->slug = Str::slug($asset->alt ?: ($asset->original_name ?: 'image'))
                    ?: ('image-' . Str::lower(Str::random(8)));
            }
        });
    }

    public function scopePublic(Builder $query): Builder
    {
        return $query->whereNotNull('public_hash');
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_processed', true);
    }

    public function scopeForSource(Builder $query, string $sourceType, ?int $sourceId = null): Builder
    {
        return $query
            ->where('source_type', $sourceType)
            ->when($sourceId !== null, fn ($q) => $q->where('source_id', $sourceId));
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('order_column', 'asc');
    }

    public function scopeRecent(Builder $query): Builder
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * آدرس عمومی هش‌شده تصویر — برای نمایش در سایت و ویرایشگر.
     */
    public function srcAttribute(): ?string
    {
        if ($this->public_hash === null) {
            return null;
        }

        return URL::to('/gallery/' . $this->public_hash);
    }

    public function urlAttribute(): ?string
    {
        return $this->srcAttribute();
    }

    public function publicUrl(): ?string
    {
        return $this->srcAttribute();
    }

    /**
     * بندانگشتی — برای تصاویر همان آدرس عمومی (فایل جداگانه‌ای ساخته نمی‌شود).
     */
    public function thumbAttribute(): ?string
    {
        if ($this->is_video) {
            return null;
        }

        return $this->srcAttribute();
    }

    /**
     * تگ HTML سئو‌پسند برای درج در محتوا: alt، title و ابعاد (جلوگیری از پرش چیدمان).
     */
    public function htmlAttribute(): string
    {
        $src = $this->srcAttribute();

        if ($src === null) {
            return '';
        }

        $alt = e($this->alt ?? $this->slug ?? '');
        $title = e($this->meta_title ?? ($this->alt ?? ''));

        $dims = ($this->width && $this->height)
            ? ' width="' . (int) $this->width . '" height="' . (int) $this->height . '"'
            : '';

        return '<img src="' . e($src) . '" alt="' . $alt . '" title="' . $title . '"'
            . $dims . ' loading="lazy">';
    }

    public function metaDescriptionForSeo(): ?string
    {
        return $this->meta_description ?? $this->alt ?? null;
    }

    public function metaTitleForSeo(): ?string
    {
        return $this->meta_title ?? $this->alt ?? null;
    }

    public function toArrayForApi(): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'alt' => $this->alt,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'src' => $this->srcAttribute(),
            'thumb' => $this->thumbAttribute(),
            'width' => $this->width,
            'height' => $this->height,
            'size_bytes' => $this->size_bytes,
            'mime_type' => $this->mime_type,
            'is_video' => $this->is_video,
            'is_processed' => $this->is_processed,
            'uploaded_by' => $this->uploaded_by,
        ];
    }
}
