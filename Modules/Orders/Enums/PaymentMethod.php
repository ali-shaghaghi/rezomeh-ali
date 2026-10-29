<?php

namespace Modules\Orders\Enums;

/**
 * روش‌های پرداخت مشترک بین ماژول‌ها (Products / Consultation / ProjectDev)
 */
enum PaymentMethod: string
{
    case Gateway = 'gateway';         // درگاه آنلاین (زرین‌پال / آی‌دی‌پی / شبیه‌ساز)
    case Installment = 'installment'; // اقساطی (۳۰/۳۰/۴۰)
    case Manual = 'manual';           // کارت به کارت (تأیید دستی ادمین)

    /**
     * برچسب فارسی روش پرداخت
     */
    public function label(): string
    {
        return match ($this) {
            self::Gateway => 'درگاه آنلاین',
            self::Installment => 'پرداخت اقساطی',
            self::Manual => 'کارت به کارت (دستی)',
        };
    }

    /**
     * توضیح کوتاه هر روش
     */
    public function description(): string
    {
        return match ($this) {
            self::Gateway => 'پرداخت آنی از طریق درگاه پرداخت',
            self::Installment => 'پرداخت در چند قسط (۳۰/۳۰/۴۰)',
            self::Manual => 'واریز دستی — پس از تأیید ادمین فعال می‌شود',
        };
    }

    /**
     * آیا این روش نیاز به تأیید دستی ادمین دارد؟
     */
    public function requiresAdminApproval(): bool
    {
        return $this === self::Manual;
    }

    /**
     * ساخت مقدار enum از رشته (با fallback به درگاه)
     */
    public static function fromValue(?string $value): self
    {
        return self::tryFrom((string) $value) ?? self::Gateway;
    }
}
