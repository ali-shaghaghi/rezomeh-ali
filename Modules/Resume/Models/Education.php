<?php

namespace Modules\Resume\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Education extends Model
{
    use HasFactory;

    // نام صریح جدول (جمع لاراول برای education اشتباه است)
    protected $table = 'educations';

    protected $fillable = [
        'institution',
        'degree',
        'field_of_study',
        'grade',
        'start_date',
        'end_date',
        'is_current',
        'description',
        'is_active',
        'order',
        'show_in_resume',
        // دو‌زبانه
        'institution_en',
        'degree_en',
        'field_of_study_en',
        'description_en',
    ];

    /**
     * نام دانشگاه/موسسه بر اساس زبان فعال (با fallback).
     */
    public function localizedInstitution(): string
    {
        return localized_field($this, 'institution') ?? $this->institution;
    }

    /**
     * مقطع تحصیلی بر اساس زبان فعال (با fallback).
     */
    public function localizedDegree(): string
    {
        return localized_field($this, 'degree') ?? $this->degree;
    }

    /**
     * رشته تحصیلی بر اساس زبان فعال (با fallback).
     */
    public function localizedFieldOfStudy(): string
    {
        return localized_field($this, 'field_of_study') ?? $this->field_of_study;
    }

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_current' => 'boolean',
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
}
