<?php

namespace Modules\Consultation\Models;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Consultant extends Model
{
    protected $fillable = [
        'user_id',
        'admin_id',
        'specialty',
        'bio',
        'avatar',
        'hourly_rate',
        'rating',
        'is_active',
    ];

    protected $casts = [
        'hourly_rate' => 'decimal:2',
        'rating' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** عضو تیم (ادمین) که این مشاوریت را دارد — منبع اصلی نام مشاوران */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    /** نام نمایشی مشاور — ادمین ← کاربر */
    public function personName(): string
    {
        return $this->admin?->name ?? $this->user?->name ?? '—';
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(ConsultantSchedule::class);
    }

    public function slots(): HasMany
    {
        return $this->hasMany(ConsultationSlot::class);
    }

    public function consultations(): HasMany
    {
        return $this->hasMany(Consultation::class);
    }
}