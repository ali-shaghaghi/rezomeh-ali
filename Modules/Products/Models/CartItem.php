<?php

namespace Modules\Products\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Products\Enums\LicenseType;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'cart_id',
        'product_id',
        'license_type',
        'quantity',
        'unit_price',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
    ];

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function licenseTypeEnum(): LicenseType
    {
        return LicenseType::tryFrom($this->license_type) ?? LicenseType::Single;
    }

    public function getLicenseLabelAttribute(): string
    {
        return $this->licenseTypeEnum()->label();
    }

    public function getTotalPriceAttribute(): float
    {
        return (float) $this->unit_price * (int) $this->quantity;
    }
}
