<?php

namespace Modules\Products\Enums;

enum LicenseType: string
{
    case Single = 'single';
    case Multi = 'multi';
    case Lifetime = 'lifetime';

    public function label(): string
    {
        return match ($this) {
            self::Single => 'تک‌کاربره',
            self::Multi => 'چندکاربره',
            self::Lifetime => 'مادام‌العمر',
        };
    }

    /**
     * برچسب بر اساس زبان فعال (fallback روی فارسی).
     */
    public function localizedLabel(): string
    {
        return __('shop.licenses.'.$this->value);
    }

    public function color(): string
    {
        return match ($this) {
            self::Single => 'slate',
            self::Multi => 'sky',
            self::Lifetime => 'violet',
        };
    }

    /**
     * ضریب قیمت محصول بر اساس نوع لایسنس
     */
    public function multiplier(): float
    {
        return (float) (config('products.license.multipliers')[$this->value] ?? 1);
    }

    /**
     * حداکثر تعداد فعال‌سازی مجاز
     */
    public function maxActivations(): int
    {
        return (int) (config('products.license.max_activations')[$this->value] ?? 1);
    }

    /**
     * تاریخ انقضا — null یعنی نامحدود
     */
    public function validMonths(): ?int
    {
        $months = config('products.license.valid_months')[$this->value] ?? null;

        return $months === null ? null : (int) $months;
    }
}
