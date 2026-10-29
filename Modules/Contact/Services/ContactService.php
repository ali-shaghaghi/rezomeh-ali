<?php

namespace Modules\Contact\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Modules\Contact\Models\ContactMessage;
use Modules\Settings\Models\Setting;

/**
 * سرویس ویجت تماس — مسیرهای ارتباطی را از Settings می‌خواند (social_*)
 * و پیام‌های سریع کاربران را ثبت + ایمیل می‌کند.
 */
class ContactService
{
    /**
     * شبکه‌های اجتماعی پشتیبانی‌شده — کلیدها با کامپوننت <x-social> منطبق‌اند.
     * ponytail: منبع واحد آیکون‌ها components/social.blade.php است؛
     * هر کلید اینجا باید آنجا تعریف شده باشد، وگرنه آیکون خالی رندر می‌شود.
     */
    public const SOCIAL_NETWORKS = [
        'telegram' => ['label' => 'تلگرام', 'icon' => 'telegram', 'color' => '#26A5E4'],
        'whatsapp' => ['label' => 'واتساپ', 'icon' => 'whatsapp', 'color' => '#25D366'],
        'instagram' => ['label' => 'اینستاگرام', 'icon' => 'instagram', 'color' => '#E4405F'],
        'rubika' => ['label' => 'روبیکا', 'icon' => 'rubika', 'color' => '#7C3AED'],
        'bale' => ['label' => 'بله', 'icon' => 'bale', 'color' => '#0EA5E9'],
        'eita' => ['label' => 'ایتا', 'icon' => 'eita', 'color' => '#3B82F6'],
        'phone' => ['label' => 'تلفن همراه', 'icon' => 'phone', 'color' => '#10B981'],
        'mail' => ['label' => 'ایمیل', 'icon' => 'mail', 'color' => '#6366F1'],
        'linkedin' => ['label' => 'لینکدین', 'icon' => 'linkedin', 'color' => '#0A66C2'],
        'github' => ['label' => 'گیت‌هاب', 'icon' => 'github', 'color' => '#181717'],
    ];

    /**
     * لیست شبکه‌های اجتماعی فعال
     *
     * منبع واحد حقیقت: کلید social_links (مدیریت از پنل ادمین → تب شبکه‌های اجتماعی).
     * این لیست هم در ویجت شناور و هم در فوتر سایت نمایش داده می‌شود.
     *
     * @return array<int, array{key: string, label: string, icon: string, color: string, url: string}>
     */
    public function socialLinks(): array
    {
        $links = [];

        foreach (self::SOCIAL_NETWORKS as $key => $meta) {
            $url = $this->socialUrl($key);

            if (! $url) {
                continue;
            }

            $links[] = array_merge($meta, [
                'key' => $key,
                'url' => $url,
            ]);
        }

        return $links;
    }

    /**
     * آدرس یک شبکه اجتماعی از Settings (یا null اگر تنظیم نشده)
     */
    public function socialUrl(string $key): ?string
    {
        if (! isset(self::SOCIAL_NETWORKS[$key])) {
            return null;
        }

        $url = trim((string) Setting::get('social_'.$key, ''));

        return $url !== '' ? $url : null;
    }

    /**
     * آدرس واتساپ نرمال‌شده (بدون + یا فاصله) برای لینک wa.me
     */
    public function whatsappLink(): ?string
    {
        $url = $this->socialUrl('whatsapp');

        if (! $url) {
            return null;
        }

        // اگر قبلاً لینک کامل است، همان برگردانده می‌شود
        if (str_starts_with($url, 'http')) {
            return $url;
        }

        $digits = preg_replace('/[^0-9]/', '', $url);

        return $digits !== '' ? 'https://wa.me/'.$digits : null;
    }

    /**
     * اطلاعات تماس عمومی از Settings
     */
    public function contactInfo(): array
    {
        return [
            'email' => trim((string) Setting::get('contact_email', '')) ?: null,
            'phone' => trim((string) Setting::get('contact_phone', '')) ?: null,
            'address' => trim((string) Setting::get('contact_address', '')) ?: null,
        ];
    }

    /**
     * آیا ویجت تماس چیزی برای نمایش دارد؟ (حداقل یک مسیر ارتباطی)
     */
    public function hasChannels(): bool
    {
        if (! empty($this->socialLinks())) {
            return true;
        }

        return ! empty(array_filter($this->contactInfo()));
    }

    /**
     * ثبت پیام تماس کاربر + ارسال ایمیل به مدیر
     *
     * @param  array{name: string, email?: string, phone?: string, subject?: string, message: string}  $data
     */
    public function sendMessage(array $data, ?Request $request = null): ContactMessage
    {
        $message = ContactMessage::create([
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'subject' => $data['subject'] ?? null,
            'message' => $data['message'],
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'user_id' => auth()->id(),
            'status' => 'new',
        ]);

        $this->notifyAdmin($message);

        return $message;
    }

    /**
     * ارسال ایمیل به مدیر سایت — ایمیل مدیر از تنظیمات contact_email
     * در صورت نبود تنظیمات یا شکست ایمیل، فقط لاگ می‌شود (جریان اصلی نمی‌شکند).
     */
    private function notifyAdmin(ContactMessage $message): void
    {
        $adminEmail = (string) Setting::get('contact_email', '');

        if ($adminEmail === '' || ! filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
            \Illuminate\Support\Facades\Log::info('Contact message received (no admin email configured)', [
                'id' => $message->id,
                'name' => $message->name,
            ]);

            return;
        }

        try {
            Mail::raw($this->buildEmailBody($message), function ($mail) use ($adminEmail, $message) {
                $mail->to($adminEmail)
                    ->subject('پیام جدید از فرم تماس: '.($message->subject ?: 'بدون موضوع'))
                    ->replyTo($message->email ?: config('mail.from.address'));
            });
        } catch (\Throwable $e) {
            // ponytail: شکست ایمیل نباید پیام کاربر را از بین ببرد؛ رکورد ذخیره شده است.
            \Illuminate\Support\Facades\Log::warning('Contact admin email failed: '.$e->getMessage(), [
                'message_id' => $message->id,
            ]);
        }
    }

    private function buildEmailBody(ContactMessage $message): string
    {
        $lines = [
            'پیام جدید از فرم تماس سایت دریافت شد:',
            '',
            'نام: '.$message->name,
            'ایمیل: '.($message->email ?: '—'),
            'تلفن: '.($message->phone ?: '—'),
            'موضوع: '.($message->subject ?: '—'),
            '',
            'پیام:',
            $message->message,
            '',
            '—',
            'IP: '.($message->ip_address ?? '—'),
            'زمان: '.$message->created_at?->format('Y-m-d H:i'),
        ];

        return implode("\n", $lines);
    }

    /**
     * تعداد پیام‌های خوانده‌نشده (برای badge ادمین)
     */
    public function unreadCount(): int
    {
        return ContactMessage::unread()->count();
    }
}
