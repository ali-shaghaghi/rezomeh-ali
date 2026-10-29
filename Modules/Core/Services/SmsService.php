<?php

namespace Modules\Core\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Core\Services\Contracts\SmsServiceInterface;

class SmsService implements SmsServiceInterface
{
    /**
     * SMS provider configuration.
     */
    protected string $provider;
    protected string $apiKey;
    protected string $senderNumber;
    protected string $baseUrl;

    public function __construct()
    {
        $this->provider = config('services.sms.provider', 'kavenegar');
        $this->apiKey = config('services.sms.api_key', '');
        $this->senderNumber = config('services.sms.sender_number', '');
        $this->baseUrl = config('services.sms.base_url', '');
    }

    /**
     * Send an SMS message.
     */
    public function send(string $phone, string $message): bool
    {
        try {
            return match ($this->provider) {
                'kavenegar' => $this->sendViaKavenegar($phone, $message),
                'smsir' => $this->sendViaSmsIr($phone, $message),
                default => $this->sendViaGenericApi($phone, $message),
            };
        } catch (\Exception $e) {
            Log::error('SMS sending failed', [
                'phone' => $phone,
                'provider' => $this->provider,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Send an OTP code via SMS.
     */
    public function sendOtp(string $phone, string $code, string $type): bool
    {
        $template = match ($type) {
            'login' => "کد ورود شما: {$code}\nاعتبار تا ۲ دقیقه",
            'register' => "کد ثبت‌نام شما: {$code}\nاعتبار تا ۲ دقیقه",
            'password_reset' => "کد بازیابی رمز عبور: {$code}\nاعتبار تا ۲ دقیقه",
            'phone_verify' => "کد تایید شماره تلفن: {$code}\nاعتبار تا ۲ دقیقه",
            'two_factor' => "کد احراز هویت دو مرحله‌ای: {$code}\nاعتبار تا ۲ دقیقه",
            default => "کد تایید شما: {$code}\nاعتبار تا ۲ دقیقه",
        };

        return $this->send($phone, $template);
    }

    /**
     * Check if the SMS service is configured and available.
     */
    public function isAvailable(): bool
    {
        return !empty($this->apiKey) && !empty($this->senderNumber);
    }

    /**
     * Send SMS via Kavenegar API.
     */
    protected function sendViaKavenegar(string $phone, string $message): bool
    {
        $response = Http::withHeaders([
            'accept' => 'application/json',
        ])->get("https://api.kavenegar.com/v1/{$this->apiKey}/sms/send.json", [
            'receptor' => $phone,
            'sender' => $this->senderNumber,
            'message' => $message,
        ]);

        return $response->successful();
    }

    /**
     * Send SMS via SMS.ir API.
     */
    protected function sendViaSmsIr(string $phone, string $message): bool
    {
        $response = Http::withHeaders([
            'Authorization' => $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post('https://api.sms.ir/v1/send/verify', [
            'mobile' => $phone,
            'templateId' => config('services.sms.template_id'),
            'parameters' => [
                ['name' => 'code', 'value' => $message],
            ],
        ]);

        return $response->successful();
    }

    /**
     * Send SMS via generic API.
     */
    protected function sendViaGenericApi(string $phone, string $message): bool
    {
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->apiKey}",
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl, [
            'to' => $phone,
            'message' => $message,
        ]);

        return $response->successful();
    }
}