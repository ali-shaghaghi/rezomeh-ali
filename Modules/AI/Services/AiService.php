<?php

namespace Modules\AI\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Settings\Models\Setting;

/**
 * سرویس مرکزی هوش مصنوعی ماژول AI — تولید محتوا با LLM.
 *
 * از همان کلید/پروایدر تنظیمات (Settings → AI) استفاده می‌کند تا ادمین
 * فقط یک‌بار کلید را تنظیم کند و همه ابزارهای AI از آن بهره ببرند.
 */
class AiService
{
    /**
     * آیا AI پیکربندی و فعال است؟
     */
    public function isEnabled(): bool
    {
        return Setting::getBool('ai_enabled', false)
            && Setting::get('ai_provider', 'none') !== 'none'
            && trim((string) Setting::get('ai_api_key', '')) !== '';
    }

    /**
     * فراخوانی LLM با یک پرامپت — پاسخ متنی برمی‌گرداند
     */
    public function complete(string $systemPrompt, string $userPrompt, int $maxTokens = 2000): string
    {
        if (! $this->isEnabled()) {
            throw new \RuntimeException('هوش مصنوعی فعال نیست. ابتدا کلید API را در تنظیمات → هوش مصنوعی وارد کنید.');
        }

        $provider = (string) Setting::get('ai_provider', 'openai');
        $key = (string) Setting::get('ai_api_key', '');
        $model = (string) Setting::get('ai_model', 'gpt-4o-mini');

        try {
            $response = match ($provider) {
                'gemini' => $this->callGemini($key, $model, $systemPrompt, $userPrompt, $maxTokens),
                default => $this->callOpenAI($key, $model, $systemPrompt, $userPrompt, $maxTokens),
            };
        } catch (\Throwable $e) {
            Log::warning('AI module failed: '.$e->getMessage());
            throw new \RuntimeException('خطا در ارتباط با هوش مصنوعی: '.$e->getMessage());
        }

        if (trim($response) === '') {
            throw new \RuntimeException('پاسخ خالی از سرویس هوش مصنوعی دریافت شد — مدل یا کلید را بررسی کنید.');
        }

        return $response;
    }

    private function callOpenAI(string $key, string $model, string $system, string $user, int $maxTokens): string
    {
        $base = rtrim((string) Setting::get('ai_base_url', 'https://api.openai.com/v1'), '/');
        $temperature = (float) Setting::get('ai_temperature', '0.7');

        $res = Http::withToken($key)->timeout(90)->post($base.'/chat/completions', [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user', 'content' => $user],
            ],
            'max_tokens' => $maxTokens,
            'temperature' => $temperature,
        ]);

        if ($res->failed()) {
            throw new \RuntimeException('API error: '.$res->status());
        }

        return trim((string) $res->json('choices.0.message.content'));
    }

    private function callGemini(string $key, string $model, string $system, string $user, int $maxTokens): string
    {
        $temperature = (float) Setting::get('ai_temperature', '0.7');

        $res = Http::timeout(90)->post(
            "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$key}",
            [
                'system_instruction' => ['parts' => [['text' => $system]]],
                'contents' => [['parts' => [['text' => $user]]]],
                'generationConfig' => [
                    'maxOutputTokens' => $maxTokens,
                    'temperature' => $temperature,
                ],
            ]
        );

        if ($res->failed()) {
            throw new \RuntimeException('Gemini error: '.$res->status());
        }

        return trim((string) $res->json('candidates.0.content.parts.0.text'));
    }
}
