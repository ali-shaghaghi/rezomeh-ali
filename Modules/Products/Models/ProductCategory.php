<?php

namespace Modules\Products\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ProductCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'description_en',
        'name_en',
        'parent_id',
        'is_active',
        'order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::saving(function (self $category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('order');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id')->published();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function localizedName(?string $localeOverride = null): ?string
    {
        return localized_field($this, "name", $localeOverride);
    }

    public function localizedDescription(?string $localeOverride = null): ?string
    {
        return localized_field($this, "description", $localeOverride);
    }
}
