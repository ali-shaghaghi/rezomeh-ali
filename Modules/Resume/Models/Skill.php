<?php

namespace Modules\Resume\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Skill extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'category',
        'proficiency',
        'icon',
        'is_active',
        'order',
        'show_in_resume',
        // دو‌زبانه
        'name_en',
    ];

    /**
     * نام مهارت بر اساس زبان فعال (با fallback به فارسی).
     */
    public function localizedName(): string
    {
        return localized_field($this, 'name') ?? $this->name;
    }

    protected $casts = [
        'proficiency' => 'integer',
        'is_active' => 'boolean',
        'order' => 'integer',
        'show_in_resume' => 'boolean',
    ];

    // روابط

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_skill');
    }    // اسکوپ‌ها

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

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'frontend' => 'فرانت‌اند',
            'backend' => 'بک‌اند',
            'devops' => 'دوآپس',
            'database' => 'دیتابیس',
            'mobile' => 'موبایل',
            'soft-skills' => 'مهارت نرم',
            default => 'سایر',
        };
    }
}
