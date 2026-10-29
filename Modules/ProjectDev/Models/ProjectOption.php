<?php

namespace Modules\ProjectDev\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ویژگی/امکانات قیمت‌گذاری‌شده (مثل «طراحی درگاه پرداخت»)
 * project_type_id = null یعنی عمومی (برای همه انواع پروژه)
 */
class ProjectOption extends Model
{
    protected $fillable = [
        'project_type_id', 'name', 'description', 'price', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'project_type_id' => 'integer',
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function projectType(): BelongsTo
    {
        return $this->belongsTo(ProjectType::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function getFormattedPriceAttribute(): string
    {
        return number_format((float) $this->price);
    }
}
