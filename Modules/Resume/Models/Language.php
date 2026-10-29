<?php

namespace Modules\Resume\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    use HasFactory;

    // زبان‌های خارجی رزومه — سطح تسلط + نمایش در رزومه

    protected $fillable = [
        'name',
        'code',
        'level',
        'proficiency',
        'description',
        'description_en',
        'name_en',
        'is_active',
        'order',
        'show_in_resume',
    ];

    protected $casts = [
        'proficiency' => 'integer',
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
        return $query->orderBy('order')->orderBy('name');
    }

    // دسترسی‌های مقدار محاسبه‌شده

    public function getLevelLabelAttribute(): string
    {
        return match ($this->level) {
            'beginner' => 'مقدماتی',
            'intermediate' => 'متوسط',
            'advanced' => 'پیشرفته',
            'fluent' => 'روان',
            'native' => 'زبان مادری',
            default => 'متوسط',
        };
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
