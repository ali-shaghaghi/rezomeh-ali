<?php

namespace Modules\SMS\Drivers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\SMS\Contracts\SmsProviderInterface;

/**
 * درایور کاوه‌نگار (پیش‌فرض) — REST API
 *
 * ponytail: پیاده‌سازی استاندارد REST (Send/Balance). قابلیت «ارسال با قالب
 * ثابت کاوه‌نگار» (lookup) اضافه نشده — اگر بعداً نیاز شد، در همین کلاس متد
 * lookup() اضافه می‌شود بدون تغییر قرارداد.
 */
class KavenegarDriver implements SmsProviderInterface
{
    public function __construct(
        private readonly string $apiKey,
        private readonly ?string $sender = null,
    ) {}

    public function send(string $phone, string $message, ?string $sender = null): array
    {
        try {
            $response = Http::timeout(15)->get(
                "https://api.kavenegar.com/v1/{$this->apiKey}/sms/send.json",
                $this->payload($phone, $message, $sender)
            );

            $ok = $response->json('return.status') === 200;

            return [
                'success' => $ok,
                'response' => $response->body(),
                'cost' => (float) collect($response->json('entries') ?? [])->sum('cost') / 10, // ریال
            ];
        } catch (\Throwable $e) {
            Log::error('SMS Kavenegar error: '.$e->getMessage());

            return ['success' => false, 'response' => $e->getMessage(), 'cost' => 0.0];
        }
    }

    public function sendBulk(array $phones, string $message, ?string $sender = null): array
    {
        $sent = 0;
        $failed = 0;
        $totalCost = 0.0;
        $lastResponse = '';

        foreach (array_chunk($phones, 200) as $chunk) {
            try {
                $response = Http::timeout(15)->get(
                    "https://api.kavenegar.com/v1/{$this->apiKey}/sms/sendarray.json",
                    $this->payload(implode(',', $chunk), $message, $sender, receptors: true)
                );

                $ok = $response->json('return.status') === 200;

                if ($ok) {
                    $sent += count($chunk);
                } else {
                    $failed += count($chunk);
                }

                $totalCost += (float) collect($response->json('entries') ?? [])->sum('cost') / 10;
                $lastResponse = $response->body();
            } catch (\Throwable $e) {
                $failed += count($chunk);
                $lastResponse = $e->getMessage();
            }
        }

        return [
            'success' => $failed === 0,
            'response' => $lastResponse,
            'cost' => $totalCost,
            'sent' => $sent,
            'failed' => $failed,
        ];
    }

    /**
     * بدنه درخواست — پارامتر sender فقط وقتی مقدار دارد ارسال می‌شود.
     *
     * طبق مستندات کاوه‌نگار، «در صورت عدم ارسال پارامتر Sender، پیام با
     * ارسال‌کننده پیش‌فرض حساب ارسال می‌شود». مقدار خالی یا نامعتبر نباید
     * به‌صورت پارامتر فرستاده شود چون خطای 412 (ارسال کننده نامعتبر) می‌دهد.
     *
     * @return array<string, string>
     */
    private function payload(string $receptor, string $message, ?string $sender, bool $receptors = false): array
    {
        $params = [
            $receptors ? 'receptors' : 'receptor' => $receptor,
            'message' => $message,
        ];

        $originator = $sender ?? $this->sender;

        if (is_string($originator) && trim($originator) !== '') {
            $params['sender'] = $originator;
        }

        return $params;
    }

    public function getBalance(): ?float
    {
        try {
            $response = Http::timeout(15)->get(
                "https://api.kavenegar.com/v1/{$this->apiKey}/account/info.json"
            );

            return (float) ($response->json('entries.remaincredit') ?? 0) / 10;
        } catch (\Throwable) {
            return null;
        }
    }
}