<?php

namespace Modules\Consultation\Models;

use Illuminate\Database\Eloquent\Model;

class ConsultationType extends Model
{
    protected $fillable = [
        'name',
        'duration_minutes',
        'mode',
        'price',
        'description',
        'is_active',
        // نسخه انگلیسی (اختیاری — fallback روی فارسی)
        'name_en',
        'description_en',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function localizedName(?string $localeOverride = null): ?string
    {
        return localized_field($this, "name", $localeOverride);
    }

    public function localizedDescription(?string $localeOverride = null): ?string
    {
        return localized_field($this, "description", $localeOverride);
    }
}
