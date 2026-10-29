<?php

namespace Modules\SMS\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Modules\SMS\Contracts\SmsProviderInterface;
use Modules\SMS\Drivers\GhasedakDriver;
use Modules\SMS\Drivers\KavenegarDriver;
use Modules\SMS\Drivers\LogDriver;
use Modules\SMS\Drivers\MelipayamakDriver;
use Modules\SMS\Models\SmsLog;
use Modules\SMS\Models\SmsTemplate;
use Modules\Settings\Models\Setting;

/**
 * سرویس ارسال پیامک — قالب + درایور + لاگ در یک نقطه
 */
class SmsService
{
    /**
     * درایورهای موجود — نام درایور => لیبل فارسی
     */
    public const DRIVERS = [
        'kavenegar' => 'کاوه‌نگار',
        'ghasedak' => 'قاصدک',
        'melipayamak' => 'ملی‌پیامک',
        'log' => 'لاگ (تست)',
    ];

    /**
     * ارسال پیامک با قالب
     *
     * @param  string  $phone  شماره موبایل مقصد
     * @param  string  $templateKey  کلید قالب (sms_templates.key)
     * @param  array<string, string>  $vars  متغیرهای جایگزین در قالب
     * @return SmsLog ردیف لاگ ثبت‌شده
     *
     * @throws \InvalidArgumentException اگر قالب یافت نشد یا غیرفعال باشد
     */
    public function send(string $phone, string $templateKey, array $vars = [], ?User $user = null): SmsLog
    {
        $template = SmsTemplate::where('key', $templateKey)->where('is_active', true)->first();

        if (! $template) {
            throw new \InvalidArgumentException("قالب پیامک «{$templateKey}» یافت نشد یا غیرفعال است.");
        }

        $message = $this->render($template->body, $vars);

        return $this->sendRaw($phone, $message, $user);
    }

    /**
     * ارسال پیامک با متن خام (بدون قالب)
     */
    public function sendRaw(string $phone, string $message, ?User $user = null): SmsLog
    {
        // حالت توسعه → هرگز تماس واقعی با ارائه‌دهنده گرفته نمی‌شود (فقط لاگ)
        // (برای تست واقعی کاوه‌نگار، حالت توسعه را در تنظیمات خاموش کنید)
        $devMode = dev_mode_enabled();

        // وقتی پیامک از پنل غیرفعال است فقط لاگ می‌شود (بدون تلاش واقعی)
        $enabled = ! $devMode && Setting::getBool('sms_is_enabled', false);

        $driverName = $enabled
            ? Setting::get('sms_driver', config('sms.default_driver', 'log'))
            : 'log';

        $provider = $this->resolveDriver($driverName);

        $result = $provider->send(
            $phone,
            $message,
            Setting::get('sms_sender') ?: null
        );

        return $this->log($user, $phone, $message, $driverName, $result);
    }

    /**
     * ارسال گروهی با متن خام — یک لاگ به‌ازای هر شماره
     *
     * @param  array<int, string>  $phones
     */
    public function sendBulk(array $phones, string $message, ?User $user = null): int
    {
        $sent = 0;

        foreach ($phones as $phone) {
            $log = $this->sendRaw($phone, $message, $user);

            if ($log->status === 'sent') {
                $sent++;
            }
        }

        return $sent;
    }

    /**
     * رندر قالب با جایگزینی متغیرها — {var} یا { var }
     *
     * @param  array<string, string>  $vars
     */
    public function render(string $body, array $vars): string
    {
        foreach ($vars as $key => $value) {
            $body = str_replace(['{'.$key.'}', '{ '.$key.' }'], (string) $value, $body);
        }

        return $body;
    }

    /**
     * ساختن نمونه درایور بر اساس نام — با کلید دیتابیس و fallback به کانفیگ
     */
    public function resolveDriver(string $driver): SmsProviderInterface
    {
        $apiKey = Setting::get('sms_api_key') ?: null;

        return match ($driver) {
            'kavenegar' => new KavenegarDriver(
                $apiKey ?: config('sms.kavenegar_api_key', ''),
                Setting::get('sms_sender') ?: config('sms.kavenegar_sender') ?: null,
            ),
            'ghasedak' => new GhasedakDriver(
                $apiKey ?: config('sms.ghasedak_api_key', ''),
                Setting::get('sms_sender') ?: config('sms.ghasedak_sender') ?: null,
            ),
            'melipayamak' => new MelipayamakDriver(
                $apiKey ?: config('sms.melipayamak_username', ''),
                Setting::get('sms_sender') ?: config('sms.melipayamak_sender') ?: null,
            ),
            default => new LogDriver,
        };
    }

    /**
     * ثبت ردیف لاگ
     *
     * @param  array{success: bool, response: string, cost: float}  $result
     */
    private function log(?User $user, string $phone, string $message, string $provider, array $result): SmsLog
    {
        return SmsLog::create([
            'user_id' => $this->safeUserId($user),
            'phone' => $phone,
            'message' => $message,
            'provider' => $provider,
            'status' => $result['success'] ? 'sent' : 'failed',
            'response' => $result['response'],
            'sent_at' => $result['success'] ? now() : null,
            'cost' => $result['cost'],
        ]);
    }

    /**
     * شناسهٔ کاربر مجاز برای ستون user_id.
     *
     * sms_logs.user_id به جدول users اشاره می‌کند (FK). ادمین‌ها جدول جدا دارند،
     * پس اگر کاربرِ احرازشده یا پاس‌داده‌شده از نوع Admin باشد null برمی‌گردد تا
     * دچار نقض FK (خطای 1452) نشویم — مخصوصاً در پنل ادمین که گارد پیش‌فرض admin است.
     */
    private function safeUserId(?User $user): ?int
    {
        $candidate = $user ?? auth()->user();

        if ($candidate === null || $candidate instanceof \App\Models\Admin) {
            return null;
        }

        return $candidate->id;
    }
}