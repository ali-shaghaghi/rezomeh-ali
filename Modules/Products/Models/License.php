<?php

namespace Modules\Products\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Products\Enums\LicenseType;

class License extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'order_item_id',
        'license_key',
        'type',
        'max_activations',
        'activations_count',
        'expires_at',
        'activated_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'activated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function typeEnum(): LicenseType
    {
        return LicenseType::tryFrom($this->type) ?? LicenseType::Single;
    }

    public function getTypeLabelAttribute(): string
    {
        return $this->typeEnum()->label();
    }

    /**
     * آیا لایسنس هنوز معتبر است (منقضی نشده و سقف فعال‌سازی پر نشده)؟
     */
    public function getIsValidAttribute(): bool
    {
        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return $this->activations_count < $this->max_activations;
    }

    public function scopeValid($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
        })->whereColumn('activations_count', '<', 'max_activations');
    }
}
