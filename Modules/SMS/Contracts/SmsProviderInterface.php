<?php

namespace Modules\SMS\Contracts;

/**
 * قرارداد درایورهای پیامک — هر ارائه‌دهنده این قرارداد را پیاده می‌کند
 */
interface SmsProviderInterface
{
    /**
     * ارسال پیامک تکی
     *
     * @param  string  $phone  شماره موبایل مقصد
     * @param  string  $message  متن پیام
     * @param  string|null  $sender  فرستنده (nullable = پیش‌فرض درایور)
     * @return array{success: bool, response: string, cost: float}
     */
    public function send(string $phone, string $message, ?string $sender = null): array;

    /**
     * ارسال گروهی
     *
     * @param  array<int, string>  $phones
     * @return array{success: bool, response: string, cost: float, sent: int, failed: int}
     */
    public function sendBulk(array $phones, string $message, ?string $sender = null): array;

    /**
     * موجودی حساب (ریال) — null یعنی درایور از موجودی پشتیبانی نمی‌کند
     */
    public function getBalance(): ?float;
}