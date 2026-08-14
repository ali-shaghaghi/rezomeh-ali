<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'thumbnail',
        'cover_image',
        'category_id',
        'github_url',
        'demo_url',
        'video_url',
        'status',
        'featured',
        'seo_title',
        'seo_description',
    ];

    protected $casts = [
        'featured' => 'boolean',
        'status' => 'string',
    ];

    public function category(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'project_category');
    }

    public function technologies(): BelongsToMany
    {
        return $this->belongsToMany(Technology::class, 'project_technology');
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft' => 'پیش‌نویس',
            'published' => 'منتشر شده',
            'archived' => 'آرشیو شده',
        };
    }

    public function getIsFeaturedAttribute(): bool
    {
        return $this->featured === true;
    }
}