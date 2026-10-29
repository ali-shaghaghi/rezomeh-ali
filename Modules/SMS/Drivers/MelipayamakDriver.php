<?php

namespace Modules\SMS\Drivers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\SMS\Contracts\SmsProviderInterface;

/**
 * درایور ملی‌پیامک — REST API
 */
class MelipayamakDriver implements SmsProviderInterface
{
    public function __construct(
        private readonly string $apiKey,
        private readonly ?string $sender = null,
    ) {}

    public function send(string $phone, string $message, ?string $sender = null): array
    {
        try {
            $response = Http::timeout(15)->asJson()->post(
                'https://rest.payamak-panel.com/api/SendSMS/SendSMS',
                [
                    'username' => $this->apiKey,
                    'password' => config('sms.melipayamak_password', ''),
                    'to' => $phone,
                    'from' => $sender ?? $this->sender,
                    'text' => $message,
                    'isFlash' => false,
                ]
            );

            $ok = (string) $response->json('Value') === '1' || (int) $response->json('RetStatus') === 1;

            return [
                'success' => $ok,
                'response' => $response->body(),
                'cost' => 0.0,
            ];
        } catch (\Throwable $e) {
            Log::error('SMS Melipayamak error: '.$e->getMessage());

            return ['success' => false, 'response' => $e->getMessage(), 'cost' => 0.0];
        }
    }

    public function sendBulk(array $phones, string $message, ?string $sender = null): array
    {
        $sent = 0;
        $failed = 0;
        $lastResponse = '';

        foreach ($phones as $phone) {
            $result = $this->send($phone, $message, $sender);

            $result['success'] ? $sent++ : $failed++;
            $lastResponse = $result['response'];
        }

        return [
            'success' => $failed === 0,
            'response' => $lastResponse,
            'cost' => 0.0,
            'sent' => $sent,
            'failed' => $failed,
        ];
    }

    public function getBalance(): ?float
    {
        try {
            $response = Http::timeout(15)->asJson()->post(
                'https://rest.payamak-panel.com/api/SendSMS/GetCredit',
                [
                    'username' => $this->apiKey,
                    'password' => config('sms.melipayamak_password', ''),
                ]
            );

            return (float) ($response->json('Value') ?? 0);
        } catch (\Throwable) {
            return null;
        }
    }
}