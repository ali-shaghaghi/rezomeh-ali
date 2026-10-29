<?php

namespace Modules\Contact\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Settings\Models\Setting;

/**
 * سرویس پاسخ هوشمند چت (#۲۱).
 *
 * اگر هوش مصنوعی در تنظیمات ادمین فعال باشد (تنظیمات → هوش مصنوعی)،
 * پاسخ از طریق API واقعی تولید می‌شود؛ در غیر این صورت پاسخ مبتنی بر الگو.
 *
 * پشتیبانی از: OpenAI (سازگار با هر endpoint که /chat/completions دارد)
 *              و Google Gemini.
 */
class AiChatService
{
    /**
     * تولید پاسخ برای پیام کاربر
     */
    public function reply(string $userText, array $history = []): string
    {
        if (! $this->isEnabled()) {
            return $this->patternReply($userText);
        }

        try {
            return $this->callProvider($userText, $history);
        } catch (\Throwable $e) {
            Log::warning('AI chat failed: '.$e->getMessage());

            // ponytail: سقوط زیاد — اگر API در دسترس نبود، الگو جواب کاربر را می‌دهد
            // تا کاربر بی‌پاسخ نماند. ارتقا: صف مجدد + ادمین آگاه می‌شود.
            return $this->patternReply($userText);
        }
    }

    /**
     * آیا هوش مصنوعی فعال و پیکربندی شده است؟
     */
    public function isEnabled(): bool
    {
        return Setting::getBool('ai_enabled', false)
            && Setting::get('ai_provider', 'none') !== 'none'
            && trim((string) Setting::get('ai_api_key', '')) !== '';
    }

    /**
     * متادیتای نمایشی برای پنل ادمین
     */
    public function status(): array
    {
        return [
            'enabled'  => $this->isEnabled(),
            'provider' => (string) Setting::get('ai_provider', 'none'),
            'model'    => (string) Setting::get('ai_model', 'gpt-4o-mini'),
        ];
    }

    /**
     * فراخوانی سرویس‌دهنده انتخاب‌شده
     */
    private function callProvider(string $userText, array $history): string
    {
        $provider = (string) Setting::get('ai_provider', 'none');
        $key = (string) Setting::get('ai_api_key', '');
        $model = (string) Setting::get('ai_model', 'gpt-4o-mini');

        // تاریخچه به فرمت provider تبدیل می‌شود
        $messages = [];
        foreach ($history as $msg) {
            $role = ($msg['from'] ?? 'user') === 'user' ? 'user' : 'assistant';
            $messages[] = ['role' => $role, 'content' => $msg['text'] ?? ''];
        }
        $messages[] = ['role' => 'user', 'content' => $userText];

        return match ($provider) {
            'gemini' => $this->callGemini($key, $model, $messages),
            default  => $this->callOpenAI($key, $model, $messages),
        };
    }

    /**
     * میزان خلاقیت پاسخ (0 تا 2) — از تنظیمات
     */
    private function temperature(): float
    {
        $v = (float) Setting::get('ai_temperature', 0.7);

        return $v >= 0 && $v <= 2 ? $v : 0.7;
    }

    /**
     * OpenAI (یا هر endpoint سازگار — e.g. Azure OpenAI، DeepSeek، Atria، ...)
     */
    private function callOpenAI(string $key, string $model, array $messages): string
    {
        $base = rtrim((string) Setting::get('ai_base_url', 'https://api.openai.com/v1'), '/');

        $res = Http::withToken($key)
            ->timeout(30)
            ->post($base.'/chat/completions', [
                'model'    => $model,
                'messages' => array_merge([['role' => 'system', 'content' => $this->systemPrompt()]], $messages),
                'temperature' => $this->temperature(),
                'max_tokens'  => 400,
            ]);

        if (! $res->successful()) {
            Log::warning('AI API error: '.$res->status().' '.$res->body());

            return $this->patternReply(end($messages)['content']);
        }

        $text = $res->json('choices.0.message.content');

        return is_string($text) && trim($text) !== '' ? trim($text) : $this->patternReply(end($messages)['content']);
    }

    /**
     * Google Gemini
     */
    private function callGemini(string $key, string $model, array $messages): string
    {
        $contents = [];
        foreach ($messages as $msg) {
            $contents[] = [
                'role' => $msg['role'] === 'assistant' ? 'model' : 'user',
                'parts' => [['text' => $msg['content']]],
            ];
        }

        $res = Http::withToken($key)
            ->timeout(30)
            ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent", [
                'system_instruction' => ['parts' => [['text' => $this->systemPrompt()]]],
                'contents' => $contents,
                'generationConfig' => ['temperature' => 0.7, 'maxOutputTokens' => 400],
            ]);

        $text = $res->json('candidates.0.content.parts.0.text');

        return is_string($text) && trim($text) !== '' ? trim($text) : $this->patternReply(end($messages)['content']);
    }

    /**
     * شخصیت و دستورالعمل‌های ربات — از تنظیمات ادمین خوانده می‌شود.
     *
     * بخش «دستورالعمل و نقش ربات» در تنظیمات → هوش مصنوعی، متن کامل system prompt را
     * نگه می‌دارد. اگر خالی باشد، یک دستورالعمل پیش‌فرض ساخته می‌شود.
     * متغیرهای پشتیبانی‌شده که با داده‌های واقعی سایت جایگزین می‌شوند:
     *   {site_title}  {site_description}  {contact_email}  {contact_phone}
     *   {telegram}  {whatsapp}  {instagram}  {linkedin}  {github}
     */
    private function systemPrompt(): string
    {
        $prompt = trim((string) Setting::get('ai_system_prompt', ''));

        if ($prompt === '') {
            $name = (string) Setting::get('site_title', 'علی شقاقی');

            $prompt = 'تو دستیار پشتیبانی و مشاوره سایت «'.$name.'» هستی. '
                .'مودب، دوستانه و حرفه‌ای پاسخ بده. هدف اصلی: راهنمایی کاربر برای سفارش پروژه، '
                .'مشاوره فنی، و پاسخ به سوالات درباره رزومه، نمونه‌کارها و محصولات. '
                .'اگر چیزی نمی‌دانی، صادقانه بگو و کاربر را به راه‌های ارتباطی انسانی ارجاع بده. '
                .'پاسخ‌ها کوتاه (حداکثر ۳ جمله) و مفید باشند.';
        }

        // جایگزینی متغیرها با داده‌های واقعی سایت
        return strtr($prompt, [
            '{site_title}'       => (string) Setting::get('site_title', ''),
            '{site_description}' => (string) Setting::get('site_description', ''),
            '{contact_email}'    => (string) Setting::get('contact_email', ''),
            '{contact_phone}'    => (string) Setting::get('contact_phone', ''),
            '{telegram}'         => (string) Setting::get('social_telegram', ''),
            '{whatsapp}'         => (string) Setting::get('social_whatsapp', ''),
            '{instagram}'        => (string) Setting::get('social_instagram', ''),
            '{linkedin}'         => (string) Setting::get('social_linkedin', ''),
            '{github}'           => (string) Setting::get('social_github', ''),
        ]);
    }

    /**
     * پاسخ مبتنی بر الگو — زمانی که AI در دسترس نیست (عمومی تا ویجت هم بتواند صدا بزند)
     */
    public function patternReply(string $text): string
    {
        $lower = mb_strtolower($text);

        return match (true) {
            str_contains($lower, 'قیمت') || str_contains($lower, 'هزینه') || str_contains($lower, 'خرید')
                => 'برای دریافت قیمت دقیق، لطفاً نوع پروژه و جزئیاتش را بگویید. پروژه‌های رزومه‌ساز معمولاً از ۲ میلیون تومان شروع می‌شوند. 🎯',

            str_contains($lower, 'سفارش') || str_contains($lower, 'پروژه')
                => 'عالی! ✨ برای سفارش پروژه کافیست از بخش «سفارش پروژه» فرم را پر کنید یا شماره تماس بگذارید تا با شما تماس بگیرم.',

            str_contains($lower, 'زمان') || str_contains($lower, 'کی') || str_contains($lower, 'کِی')
                => 'سعی می‌کنم حداکثر ظرف ۲۴ ساعت پاسخ بدهم. پروژه‌ها معمولاً بین ۳ تا ۱۰ روز کاری تحویل داده می‌شوند. ⏱️',

            str_contains($lower, 'سلام') || str_contains($lower, 'درود') || str_contains($lower, 'hi')
                => 'سلام! 😊 خوشحال می‌شوم کمکتان کنم. می‌توانید درباره پروژه، قیمت یا هر سوال دیگری بپرسید.',

            str_contains($lower, 'ممنون') || str_contains($lower, 'مرسی') || str_contains($lower, 'تشکر')
                => 'خواهش می‌کنم! 🙏 اگر سوال دیگری داشتید، من اینجا هستم.',

            str_contains($lower, 'تماس') || str_contains($lower, 'شماره') || str_contains($lower, 'تلفن')
                => 'می‌توانید از راه‌های ارتباطی کنار این پنل با من در ارتباط باشید، یا ایمیل بگذارید تا پاسخ دهم. 📞',

            str_contains($lower, 'ایمیل') || str_contains($lower, 'mail')
                => 'می‌توانید ایمیل خود را بگذارید تا حداکثر ظرف یک روز کاری پاسخ بدهم. 📧',

            default => 'متوجه شدم! 💡 می‌توانید جزئیات بیشتری بگویید؟ مثلاً درباره چه پروژه‌ای می‌خواهید مشاوره بگیرید؟',
        };
    }
}
