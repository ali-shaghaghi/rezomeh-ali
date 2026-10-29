<?php

namespace Modules\Products\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'user_id',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * جمع کل سبد (بر اساس قیمت ثبت‌شده در آیتم‌ها)
     */
    public function getSubtotalAttribute(): float
    {
        return (float) $this->items->sum(fn (CartItem $item) => $item->unit_price * $item->quantity);
    }

    public function getItemsCountAttribute(): int
    {
        return (int) $this->items->sum('quantity');
    }
}
