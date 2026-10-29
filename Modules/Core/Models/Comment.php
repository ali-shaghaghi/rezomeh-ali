<?php

namespace Modules\Core\Models;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'admin_id',
        'parent_id',
        'blog_post_id',
        'content',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** نویسنده ادمین (پاسخ ثبت‌شده در پنل مدیریت) */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    /** نظر والد (ریشه‌ای که این نظر به آن پاسخ می‌دهد) */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /** پاسخ‌های مستقیم این نظر — مرتب برای نمایش */
    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('created_at')->orderBy('id');
    }

    public function blogPost(): BelongsTo
    {
        return $this->belongsTo(BlogPost::class);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /** نام نویسنده — ادمین یا کاربر */
    public function authorName(): string
    {
        return $this->admin?->name ?? $this->user?->name ?? 'کاربر حذف‌شده';
    }

    /** آیا نویسنده عضو تیم (ادمین) است؟ */
    public function isAdminAuthored(): bool
    {
        return $this->admin_id !== null;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'در انتظار تایید',
            'approved' => 'تایید شده',
            'rejected' => 'رد شده',
            default => 'نامشخص',
        };
    }
}
