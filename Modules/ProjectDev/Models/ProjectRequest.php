<?php

namespace Modules\ProjectDev\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Orders\Models\Order;

/**
 * درخواست پروژه کاربر — پس از تایید ادمین (قیمت نهایی/ددلاین) به سفارش تبدیل می‌شود
 */
class ProjectRequest extends Model
{
    public const STATUSES = ['pending', 'approved', 'rejected', 'converted'];

    public const DIFFICULTIES = ['low', 'medium', 'high'];

    protected $fillable = [
        'user_id', 'project_type_id', 'project_type_name', 'title', 'description',
        'selected_options', 'custom_options', 'features_snapshot',
        'estimated_price', 'final_price', 'difficulty', 'estimated_days', 'deadline',
        'status', 'admin_note', 'order_id', 'reviewed_at',
    ];

    protected $casts = [
        'selected_options' => 'array',
        'custom_options' => 'array',
        'features_snapshot' => 'array',
        'estimated_price' => 'decimal:2',
        'final_price' => 'decimal:2',
        'deadline' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function projectType(): BelongsTo
    {
        return $this->belongsTo(ProjectType::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'در انتظار بررسی',
            'approved' => 'تایید شده',
            'rejected' => 'رد شده',
            'converted' => 'تبدیل به سفارش',
            default => 'نامشخص',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'warning',
            'approved' => 'info',
            'rejected' => 'danger',
            'converted' => 'success',
            default => 'secondary',
        };
    }

    public function getDifficultyLabelAttribute(): string
    {
        return match ($this->difficulty) {
            'low' => 'کم',
            'medium' => 'متوسط',
            'high' => 'زیاد',
            default => '—',
        };
    }

    /**
     * قیمت مؤثر — قیمت نهایی ادمین وگرنه تخمین سیستم
     */
    public function getEffectivePriceAttribute(): float
    {
        return (float) ($this->final_price ?? $this->estimated_price);
    }

    public function getFormattedEstimatedPriceAttribute(): string
    {
        return number_format((float) $this->estimated_price);
    }

    public function getFormattedFinalPriceAttribute(): string
    {
        return $this->final_price === null ? '—' : number_format((float) $this->final_price);
    }
}
