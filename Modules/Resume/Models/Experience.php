<?php

namespace Modules\Resume\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Experience extends Model
{
    use HasFactory;

    protected $fillable = [
        'company',
        'position',
        'employment_type',
        'location',
        'start_date',
        'end_date',
        'is_current',
        'description',
        'technologies',
        'company_url',
        'is_active',
        'order',
        'show_in_resume',
        // دو‌زبانه
        'company_en',
        'position_en',
        'description_en',
        'location_en',
    ];

    /**
     * نام شرکت بر اساس زبان فعال (با fallback).
     */
    public function localizedCompany(): string
    {
        return localized_field($this, 'company') ?? $this->company;
    }

    /**
     * عنوان شغلی بر اساس زبان فعال (با fallback).
     */
    public function localizedPosition(): string
    {
        return localized_field($this, 'position') ?? $this->position;
    }

    /**
     * توضیحات بر اساس زبان فعال (با fallback).
     */
    public function localizedDescription(): ?string
    {
        return localized_field($this, 'description');
    }

    /**
     * محل کار بر اساس زبان فعال.
     */
    public function localizedLocation(): ?string
    {
        return localized_field($this, 'location');
    }

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_current' => 'boolean',
        'technologies' => 'array',
        'is_active' => 'boolean',
        'order' => 'integer',
        'show_in_resume' => 'boolean',
    ];

    // اسکوپ‌ها

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInResume($query)
    {
        return $query->where('show_in_resume', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderByDesc('start_date');
    }

    // دسترسی‌های مقدار محاسبه‌شده

    public function getEmploymentTypeLabelAttribute(): string
    {
        return match ($this->employment_type) {
            'full-time' => 'تمام‌وقت',
            'part-time' => 'پاره‌وقت',
            'freelance' => 'فریلنسری',
            'remote' => 'دورکار',
            'internship' => 'کارآموزی',
            default => 'تمام‌وقت',
        };
    }
}
