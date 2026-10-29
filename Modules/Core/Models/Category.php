<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Resume\Models\Project;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        // نسخه انگلیسی (اختیاری — fallback روی فارسی)
        'name_en',
        'description_en',
    ];

    public function localizedName(?string $localeOverride = null): ?string
    {
        return localized_field($this, 'name', $localeOverride);
    }

    public function localizedDescription(?string $localeOverride = null): ?string
    {
        return localized_field($this, 'description', $localeOverride);
    }

    public function blogPosts(): BelongsToMany
    {
        return $this->belongsToMany(BlogPost::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }
}
