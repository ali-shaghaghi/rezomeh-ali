<?php

namespace Modules\ProjectDev\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * نوع پروژه — کاتالوگ قیمت‌گذاری ادمین (مثل «طراحی سایت فروشگاهی با لاراول»)
 */
class ProjectType extends Model
{
    protected $fillable = [
        'name', 'slug', 'description', 'base_price', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function options(): HasMany
    {
        return $this->hasMany(ProjectOption::class);
    }

    public function activeOptions(): HasMany
    {
        return $this->options()->where('is_active', true)->orderBy('sort_order')->orderBy('id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function getFormattedBasePriceAttribute(): string
    {
        return number_format((float) $this->base_price);
    }
}
