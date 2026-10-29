<?php

namespace Modules\Products\Enums;

enum ProductType: string
{
    case Template = 'template';
    case Crm = 'crm';
    case Script = 'script';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Template => 'قالب',
            self::Crm => 'CRM / نرم‌افزار',
            self::Script => 'اسکریپت',
            self::Other => 'سایر',
        };
    }

    /**
     * برچسب بر اساس زبان فعال (fallback روی فارسی).
     */
    public function localizedLabel(): string
    {
        return __('shop.types.'.$this->value);
    }

    public function color(): string
    {
        return match ($this) {
            self::Template => 'violet',
            self::Crm => 'sky',
            self::Script => 'amber',
            self::Other => 'slate',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Template => 'palette',
            self::Crm => 'products',
            self::Script => 'orders',
            self::Other => 'box',
        };
    }
}
