<?php

namespace Modules\SMS\Drivers;

use Illuminate\Support\Facades\Log;
use Modules\SMS\Contracts\SmsProviderInterface;

/**
 * درایور لاگ — بدون ارسال واقعی؛ فقط در storage/logs/laravel.log ثبت می‌کند
 * (برای تست و توسعه — پیش‌فرض وقتی api_key خالی است)
 */
class LogDriver implements SmsProviderInterface
{
    public function send(string $phone, string $message, ?string $sender = null): array
    {
        Log::info('SMS [LogDriver] To: '.$phone.' | Sender: '.($sender ?? 'default').' | Message: '.$message);

        return [
            'success' => true,
            'response' => 'Logged to laravel.log',
            'cost' => 0.0,
        ];
    }

    public function sendBulk(array $phones, string $message, ?string $sender = null): array
    {
        foreach ($phones as $phone) {
            $this->send($phone, $message, $sender);
        }

        return [
            'success' => true,
            'response' => 'Logged '.count($phones).' messages to laravel.log',
            'cost' => 0.0,
            'sent' => count($phones),
            'failed' => 0,
        ];
    }

    public function getBalance(): ?float
    {
        return null; // درایور لاگ موجودی ندارد
    }
}