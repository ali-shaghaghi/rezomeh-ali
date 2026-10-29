<?php

namespace Modules\SMS\Drivers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\SMS\Contracts\SmsProviderInterface;

/**
 * درایور قاصدک — REST API
 */
class GhasedakDriver implements SmsProviderInterface
{
    public function __construct(
        private readonly string $apiKey,
        private readonly ?string $sender = null,
    ) {}

    public function send(string $phone, string $message, ?string $sender = null): array
    {
        try {
            $response = Http::timeout(15)->asForm()->withHeaders([
                'apikey' => $this->apiKey,
            ])->post('https://api.ghasedak.me/v2/sms/send/simple', [
                'message' => $message,
                'receptor' => $phone,
                'linenumber' => $sender ?? $this->sender,
            ]);

            $ok = $response->json('result.code') === 200;

            return [
                'success' => $ok,
                'response' => $response->body(),
                'cost' => 0.0,
            ];
        } catch (\Throwable $e) {
            Log::error('SMS Ghasedak error: '.$e->getMessage());

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
            $response = Http::timeout(15)->withHeaders([
                'apikey' => $this->apiKey,
            ])->get('https://api.ghasedak.me/v2/account/balance');

            return (float) ($response->json('item.balance') ?? 0);
        } catch (\Throwable) {
            return null;
        }
    }
}