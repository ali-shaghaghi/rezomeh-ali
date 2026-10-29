<?php

namespace Modules\Settings\Services;

use Modules\Settings\Models\Setting;

class SettingsService
{
    /**
     * تعریف schema گروه‌های تنظیمات — فرم‌ها به‌صورت داینامیک از این ساخته می‌شوند
     *
     * گروه‌های site/smtp/payment/ai فرم داینامیک دارند؛
     * profile و social کامپوننت اختصاصی Livewire دارند (فرم خاص خودشان)
     */
    public function schema(): array
    {
        return [
            'site' => [
                'title' => 'تنظیمات عمومی سایت',
                'short' => 'عمومی سایت',
                'icon' => 'settings',
                'fields' => [
                    'site_title' => ['label' => 'عنوان سایت', 'type' => 'text', 'default' => 'علی شقاقی | توسعه‌دهنده فول‌استک'],
                    'site_description' => ['label' => 'توضیحات سایت (SEO)', 'type' => 'textarea', 'default' => 'سایت شخصی علی شقاقی — رزومه، نمونه کارها و وبلاگ'],
                    'site_keywords' => ['label' => 'کلمات کلیدی (SEO)', 'type' => 'text', 'default' => 'برنامه‌نویس, لاراول, فول‌استک, طراحی سایت'],
                    'maintenance_mode' => ['label' => 'حالت تعمیر و نگهداری (سایت خاموش)', 'type' => 'boolean', 'default' => false],
                    'dev_mode' => ['label' => 'حالت توسعه', 'type' => 'boolean', 'default' => true, 'help' => 'فقط در محیط local. روشن: پیامک‌ها فقط لاگ می‌شوند و کد OTP در صفحه تایید نمایش داده می‌شود. برای تست واقعی اتصال کاوه‌نگار آن را خاموش کنید تا پیامک واقعی ارسال شود.'],
                ],
            ],
            'profile' => [
                'title' => 'پروفایل',
                'short' => 'پروفایل',
                'icon' => 'users',
                'component' => true, // رندر با کامپوننت اختصاصی (اطلاعات پروفایل + آواتار)
            ],
            'appearance' => [
                'title' => 'ظاهر',
                'short' => 'ظاهر',
                'icon' => 'palette',
                'component' => true, // رندر با کامپوننت اختصاصی (لوگوی پنل + تم سایت)
            ],
            'security' => [
                'title' => 'امنیت حساب',
                'short' => 'امنیت حساب',
                'icon' => 'shield',
                'component' => true, // رندر با کامپوننت اختصاصی (تغییر رمز)
            ],
            'smtp' => [
                'title' => 'ایمیل (SMTP)',
                'short' => 'ایمیل (SMTP)',
                'icon' => 'mail',
                'fields' => [
                    'smtp_host' => ['label' => 'میزبان SMTP', 'type' => 'text', 'default' => 'sandbox.smtp.mailtrap.io'],
                    'smtp_port' => ['label' => 'پورت', 'type' => 'number', 'default' => '2525'],
                    'smtp_username' => ['label' => 'نام کاربری', 'type' => 'text', 'default' => ''],
                    'smtp_password' => ['label' => 'رمز عبور', 'type' => 'password', 'default' => ''],
                    'smtp_encryption' => ['label' => 'رمزنگاری', 'type' => 'select', 'options' => ['tls' => 'TLS', 'ssl' => 'SSL', 'none' => 'بدون رمزنگاری'], 'default' => 'tls'],
                    'smtp_from_address' => ['label' => 'آدرس فرستنده', 'type' => 'email', 'default' => 'noreply@alishaghaghi.ir'],
                    'smtp_from_name' => ['label' => 'نام فرستنده', 'type' => 'text', 'default' => 'علی شقاقی'],
                ],
            ],
            'payment' => [
                'title' => 'درگاه پرداخت',
                'short' => 'درگاه پرداخت',
                'icon' => 'wallet',
                'gateway' => true, // رندر اختصاصی درگاه‌ها (آکوردئون زرین‌پال/آی‌دی‌پی/کیف پول ارز دیجیتال/کارت به کارت)
                'fields' => [
                    // فیلدهای مشترک درگاه‌ها — داخل آکاردئون مدیریت و ذخیره می‌شوند
                    'payment_driver' => ['label' => 'درگاه فعال', 'type' => 'select', 'options' => ['zarinpal' => 'زرین‌پال', 'idpay' => 'آی‌دی‌پی', 'crypto' => 'کیف پول ارز دیجیتال', 'manual' => 'کارت به کارت (دستی)'], 'default' => 'zarinpal'],
                    'payment_callback_url' => ['label' => 'آدرس بازگشت (Callback)', 'type' => 'text', 'default' => ''],
                    'payment_currency' => ['label' => 'واحد پول نمایشی', 'type' => 'text', 'default' => 'تومان'],
                    'payment_test_mode' => ['label' => 'حالت تست (سندباکس)', 'type' => 'boolean', 'default' => false],
                    // شماره کارت کارت‌به‌کارت — تا زمانی ثبت نشود، گزینه «کارت به کارت» در checkout نمایش داده نمی‌شود
                    'payment_card_number' => ['label' => 'شماره کارت کارت‌به‌کارت', 'type' => 'text', 'default' => ''],
                    // فیلدهای درگاه ارز دیجیتال (مدیریت در آکوردئون crypto)
                    'crypto_enabled' => ['label' => 'نمایش پرداخت ارز دیجیتال در checkout', 'type' => 'boolean', 'default' => false],
                    'crypto_wallet_address' => ['label' => 'آدرس کیف پول USDT', 'type' => 'text', 'default' => ''],
                    'crypto_network' => ['label' => 'شبکه', 'type' => 'select', 'options' => ['trc20' => 'TRC20 (Tron)', 'erc20' => 'ERC20 (Ethereum)', 'bep20' => 'BEP20 (BSC)'], 'default' => 'trc20'],
                    // رمزارزهای جایگزین تتر — برای کاربران ایرانی که تتر سریعاً شناسایی می‌شود؛
                    // هر رمزارزی که «آدرس + نرخ» داشته باشد در پرداخت checkout نمایش داده می‌شود
                    'crypto_btc_address' => ['label' => 'آدرس کیف پول بیت‌کوین (BTC)', 'type' => 'text', 'default' => ''],
                    'crypto_btc_rate' => ['label' => 'نرخ هر بیت‌کوین به تومان', 'type' => 'number', 'default' => '0'],
                    'crypto_eth_address' => ['label' => 'آدرس کیف پول اتریوم (ETH)', 'type' => 'text', 'default' => ''],
                    'crypto_eth_rate' => ['label' => 'نرخ هر اتریوم به تومان', 'type' => 'number', 'default' => '0'],
                    'crypto_ltc_address' => ['label' => 'آدرس کیف پول لایت‌کوین (LTC)', 'type' => 'text', 'default' => ''],
                    'crypto_ltc_rate' => ['label' => 'نرخ هر لایت‌کوین به تومان', 'type' => 'number', 'default' => '0'],
                    'crypto_trx_address' => ['label' => 'آدرس کیف پول ترون (TRX)', 'type' => 'text', 'default' => ''],
                    'crypto_trx_rate' => ['label' => 'نرخ هر ترون به تومان', 'type' => 'number', 'default' => '0'],
                ],
            ],
            'payment_extended' => [
                'title' => 'تنظیمات پیشرفته پرداخت',
                'short' => 'پرداخت پیشرفته',
                'icon' => 'wallet',
                'fields' => [
                    'installment_enabled' => ['label' => 'فعال‌سازی پرداخت اقساطی', 'type' => 'boolean', 'default' => true],
                    'installment_max_count' => ['label' => 'حداکثر تعداد اقساط', 'type' => 'number', 'default' => '3'],
                    'installment_interest_rate' => ['label' => 'نرخ سود اقساطی (٪)', 'type' => 'number', 'default' => '0'],
                    'digify_merchant_id' => ['label' => 'شناسه پذیرنده دیجیفای', 'type' => 'password', 'default' => ''],
                    'digipay_merchant_id' => ['label' => 'شناسه پذیرنده دیجی‌پی', 'type' => 'password', 'default' => ''],
                    'zarinpal_merchant_id' => ['label' => 'شناسه پذیرنده زرین‌پال', 'type' => 'password', 'default' => ''],
                ],
            ],
            'currency' => [
                'title' => 'قیمت‌گذاری ارزی (دلار)',
                'short' => 'قیمت ارز',
                'icon' => 'wallet',
                'fields' => [
                    // قیمت روز دلار → همه قیمت‌های تومانی برای کاربر انگلیسی خودکار به دلار تبدیل می‌شوند
                    'usd_rate' => ['label' => 'قیمت هر دلار به تومان (مثال: 250000)', 'type' => 'number', 'default' => '0'],
                    // ضریب گرانی برای کاربر خارج — مثال: 1.3 یعنی ۳۰٪ گران‌تر از قیمت ایران
                    'usd_markup' => ['label' => 'ضریب قیمت دلاری (مثال: 1.3 = ۳۰٪ گران‌تر)', 'type' => 'number', 'default' => '1'],
                ],
            ],
            'contact' => [
                'title' => 'اطلاعات تماس',
                'short' => 'اطلاعات تماس',
                'icon' => 'mail',
                'fields' => [
                    'contact_email' => ['label' => 'ایمیل تماس', 'type' => 'email', 'default' => 'contact@alishaghaghi.ir'],
                    'contact_phone' => ['label' => 'تلفن تماس', 'type' => 'text', 'default' => ''],
                    'contact_address' => ['label' => 'آدرس', 'type' => 'textarea', 'default' => ''],
                    'social_telegram' => ['label' => 'تلگرام', 'type' => 'text', 'default' => 'https://t.me/alishaghaghi'],
                    'social_whatsapp' => ['label' => 'واتساپ (شماره یا لینک)', 'type' => 'text', 'default' => ''],
                    'social_instagram' => ['label' => 'اینستاگرام', 'type' => 'text', 'default' => ''],
                    'social_rubika' => ['label' => 'روبیکا', 'type' => 'text', 'default' => ''],
                    'social_bale' => ['label' => 'بله', 'type' => 'text', 'default' => ''],
                    'social_eita' => ['label' => 'ایتا', 'type' => 'text', 'default' => ''],
                    'social_phone' => ['label' => 'تلفن همراه (نمایش در شبکه‌های اجتماعی)', 'type' => 'text', 'default' => ''],
                    'social_mail' => ['label' => 'ایمیل (نمایش در شبکه‌های اجتماعی)', 'type' => 'text', 'default' => ''],
                    'social_linkedin' => ['label' => 'لینکدین', 'type' => 'text', 'default' => ''],
                    'social_github' => ['label' => 'گیت‌هاب', 'type' => 'text', 'default' => 'https://github.com/ali-shaghaghi'],
                ],
            ],
            'sms' => [
                'title' => 'پیامک (SMS)',
                'short' => 'پیامک (SMS)',
                'icon' => 'bell',
                'fields' => [
                    'sms_is_enabled' => ['label' => 'فعال‌سازی سرویس پیامک', 'type' => 'boolean', 'default' => false],
                    'sms_api_key' => ['label' => 'کلید API', 'type' => 'password', 'default' => ''],
                    'sms_sender_number' => ['label' => 'شماره فرستنده', 'type' => 'text', 'default' => ''],
                    'sms_provider' => ['label' => 'سرویس‌دهنده', 'type' => 'select', 'options' => ['log' => 'لاگ (تست)', 'kavenegar' => 'کاوه‌نگار', 'farapayamak' => 'فراپیامک', 'melipayamak' => 'ملی‌پیامک'], 'default' => 'log'],
                    'sms_admin_phone' => ['label' => 'شماره موبایل مدیر (دریافت اطلاع‌رسانی)', 'type' => 'text', 'default' => ''],
                ],
            ],
            'consultation' => [
                'title' => 'مشاوره',
                'short' => 'مشاوره',
                'icon' => 'users',
                'fields' => [
                    'consultation_booking_enabled' => ['label' => 'فعال‌سازی رزرو آنلاین مشاوره', 'type' => 'boolean', 'default' => true],
                    'consultation_sms_notify' => ['label' => 'ارسال پیامک اطلاع‌رسانی رزرو', 'type' => 'boolean', 'default' => true],
                ],
            ],
            'ai' => [
                'title' => 'هوش مصنوعی',
                'short' => 'هوش مصنوعی',
                'icon' => 'analytics',
                'fields' => [
                    'ai_enabled' => ['label' => 'فعال‌سازی هوش مصنوعی برای چت آنلاین', 'type' => 'boolean', 'default' => false],
                    'ai_provider' => ['label' => 'سرویس‌دهنده', 'type' => 'select', 'options' => ['none' => 'غیرفعال (پاسخ از پیش تعریف‌شده)', 'openai' => 'OpenAI (و سازگارها)', 'gemini' => 'Google Gemini'], 'default' => 'none'],
                    'ai_api_key' => ['label' => 'کلید API', 'type' => 'password', 'default' => ''],
                    'ai_model' => ['label' => 'مدل', 'type' => 'text', 'default' => 'gpt-4o-mini'],
                    'ai_base_url' => ['label' => 'آدرس API (اختیاری — برای Azure/DeepSeek/...', 'type' => 'text', 'default' => 'https://api.openai.com/v1'],
                    'ai_temperature' => ['label' => 'خلاقیت پاسخ (0 تا 2)', 'type' => 'number', 'default' => '0.7'],
                    // دستورالعمل و نقش ربات — متن کامل system prompt قابل ویرایش توسط ادمین
                    'ai_system_prompt' => ['label' => 'دستورالعمل و نقش ربات (System Prompt)', 'type' => 'textarea', 'default' => '', 'help' => 'نقش، لحن، قوانین و نحوه پاسخ‌دهی ربات را بنویسید. متغیرهای قابل استفاده: {site_title} {site_description} {contact_email} {contact_phone} {telegram} {whatsapp} {instagram} {linkedin} {github}'],
                ],
            ],
            'social' => [
                'title' => 'شبکه‌های اجتماعی',
                'short' => 'شبکه‌های اجتماعی',
                'icon' => 'users',
                'social' => true, // رندر اختصاصی شبکه‌های اجتماعی (افزودن/ویرایش/حذف داینامیک)
            ],
        ];
    }

    /**
     * گروه‌های دارای فرم داینامیک (schema-driven)
     */
    public function formGroups(): array
    {
        return collect($this->schema())
            ->filter(fn (array $group) => ! empty($group['fields']))
            ->toArray();
    }

    /**
     * ذخیره گروهی تنظیمات یک بخش
     */
    public function saveGroup(string $group, array $values): void
    {
        $schema = $this->schema();

        if (! isset($schema[$group]['fields'])) {
            abort(404, 'گروه تنظیمات یافت نشد.');
        }

        foreach ($schema[$group]['fields'] as $key => $field) {
            if (! array_key_exists($key, $values)) {
                continue;
            }

            $value = $values[$key];

            // تبدیل بولین به رشته ذخیره‌پذیر
            if ($field['type'] === 'boolean') {
                $value = $value ? '1' : '0';
            }

            // فیلد password خالی = بدون تغییر
            if ($field['type'] === 'password' && $value === '') {
                continue;
            }

            Setting::set($key, $value, $group);
        }
    }

    /**
     * مقادیر فعلی یک گروه (ادغام با پیش‌فرض‌های schema)
     */
    public function groupValues(string $group): array
    {
        $schema = $this->schema();

        if (! isset($schema[$group]['fields'])) {
            abort(404, 'گروه تنظیمات یافت نشد.');
        }

        $values = [];
        foreach ($schema[$group]['fields'] as $key => $field) {
            $current = Setting::get($key);
            $raw = $current !== null ? $current : ($field['default'] ?? '');

            // بولین‌ها باید bool واقعی برگردند — نه رشته '0'/'1'.
            // در جاوااسکریپت رشته '0' truthy است و سوییچ (wire:model) بعد از
            // هر بارگذاری صفحه روشن نمایش داده می‌شد: کاربر «حالت توسعه» را
            // خاموش می‌کرد ولی بعد از لود دوباره روشن می‌دید و فعال‌سازی هم
            // به‌ظاهر کار نمی‌کرد. bool واقعی در هر دو مسیر JS درست رندر می‌شود.
            $values[$key] = $field['type'] === 'boolean'
                ? filter_var($raw, FILTER_VALIDATE_BOOLEAN)
                : $raw;
        }

        return $values;
    }

    // ===== شبکه‌های اجتماعی (داینامیک) =====

    /**
     * تعریف فیلدهای هر شبکه اجتماعی (برای فرم افزودن/ویرایش)
     */
    public function socialFields(): array
    {
        return [
            'name' => ['label' => 'نام شبکه', 'type' => 'text'],
            'url' => ['label' => 'آدرس (لینک کامل)', 'type' => 'text'],
            'icon' => ['label' => 'آیکون', 'type' => 'select', 'options' => [
                'github' => 'GitHub', 'linkedin' => 'LinkedIn', 'telegram' => 'تلگرام',
                'instagram' => 'اینستاگرام', 'twitter' => 'X (توییتر)', 'youtube' => 'یوتیوب',
                'discord' => 'دیسکورد', 'whatsapp' => 'واتساپ', 'globe' => 'وب‌سایت (عمومی)',
            ]],
            'order' => ['label' => 'ترتیب نمایش', 'type' => 'number'],
        ];
    }

    /**
     * لیست شبکه‌های اجتماعی (مرتب بر اساس order)
     *
     * @return array<int, array{id: string, name: string, url: string, icon: string, order: int}>
     */
    public function socialLinks(): array
    {
        $raw = Setting::get('social_links', '[]');
        $links = is_array($raw) ? $raw : json_decode((string) $raw, true);

        if (! is_array($links)) {
            return [];
        }

        // حذف رکوردهای ناقص + مرتب‌سازی
        $links = array_values(array_filter($links, fn ($l) => is_array($l) && ! empty($l['name'])));
        usort($links, fn ($a, $b) => ($a['order'] ?? 0) <=> ($b['order'] ?? 0));

        return $links;
    }

    /**
     * ذخیره کامل لیست شبکه‌های اجتماعی
     */
    public function saveSocialLinks(array $links): void
    {
        // نرمال‌سازی + حذف خالی‌ها
        $clean = [];
        $order = 0;
        foreach ($links as $link) {
            if (! is_array($link) || empty(trim((string) ($link['name'] ?? '')))) {
                continue;
            }

            $order++;
            $clean[] = [
                'name' => trim((string) $link['name']),
                'url' => trim((string) ($link['url'] ?? '')),
                'icon' => (string) ($link['icon'] ?? 'globe'),
                'order' => (int) ($link['order'] ?? $order),
            ];
        }

        usort($clean, fn ($a, $b) => $a['order'] <=> $b['order']);

        Setting::set('social_links', json_encode($clean, JSON_UNESCAPED_UNICODE), 'social');
    }
}
