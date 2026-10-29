<?php

namespace Modules\SMS\Livewire\Admin;

use Livewire\Component;
use Modules\Settings\Models\Setting;

/**
 * تنظیمات SMS — گروه sms (driver/api_key/sender/is_enabled)
 */
class SmsSettings extends Component
{
    public bool $isEnabled = false;

    public string $driver = 'log';

    public string $apiKey = '';

    public string $sender = '';

    public ?float $balance = null;

    /** حالت توسعه — پیامک خودکار فقط لاگ می‌شود (خاموش = تست واقعی کاوه‌نگار) */
    public bool $devMode = true;

    /** شماره مقصد پیامک تستی */
    public string $testPhone = '';

    public bool $successToast = false;

    public string $successMessage = '';

    public bool $errorToast = false;

    public string $errorMessage = '';

    public function mount(): void
    {
        $this->isEnabled = Setting::getBool('sms_is_enabled', false);
        $this->driver = Setting::get('sms_driver', config('sms.default_driver', 'log'));
        $this->apiKey = Setting::get('sms_api_key', '');
        $this->sender = Setting::get('sms_sender', '');
        $this->devMode = dev_mode_enabled();
        $this->testPhone = Setting::get('sms_admin_phone', '') ?: '';
    }

    public function save(): void
    {
        $this->validate([
            'driver' => ['required', 'in:kavenegar,ghasedak,melipayamak,log'],
            'apiKey' => ['nullable', 'string', 'max:255'],
            'sender' => ['nullable', 'string', 'max:50'],
        ]);

        Setting::set('sms_is_enabled', $this->isEnabled ? '1' : '0', 'sms');
        Setting::set('sms_driver', $this->driver, 'sms');
        Setting::set('sms_api_key', $this->apiKey, 'sms');
        Setting::set('sms_sender', $this->sender, 'sms');

        $this->notifySuccess('تنظیمات پیامک ذخیره شد.');
    }

    public function checkBalance(): void
    {
        $provider = app(\Modules\SMS\Services\SmsService::class)->resolveDriver($this->driver);
        $this->balance = $provider->getBalance();

        $this->notifySuccess($this->balance === null
            ? 'این درایور از موجودی پشتیبانی نمی‌کند.'
            : 'موجودی: '.number_format($this->balance).' تومان');
    }

    /**
     * ارسال پیامک تستی واقعی با درایور انتخاب‌شده (مستقل از حالت توسعه) —
     * برای بررسی اینکه آیا اتصال به کاوه‌نگار درست کار می‌کند یا نه.
     */
    public function sendTest(): void
    {
        $this->validate([
            'testPhone' => ['required', 'string', 'regex:/^09[0-9]{9}$/'],
        ], [
            'testPhone.required' => 'شماره مقصد پیامک تستی الزامی است.',
            'testPhone.regex' => 'شماره موبایل معتبر نیست (مثال: 09123456789).',
        ]);

        $service = app(\Modules\SMS\Services\SmsService::class);
        $provider = $service->resolveDriver($this->driver);

        $message = 'پیامک تستی '.config('app.name').' — اتصال '.\Modules\SMS\Services\SmsService::DRIVERS[$this->driver];
        $result = $provider->send($this->testPhone, $message, $this->sender ?: null);

        // ثبت در لاگ پیامک‌ها مثل ارسال معمولی
        // user_id اینجا null است: پیامک تستی به شمارهٔ دلخواه می‌رود و به هیچ ردیف
        // جدول users وابسته نیست. (admins جدول جداگانه دارد و FK به users نمی‌خورد.)
        \Modules\SMS\Models\SmsLog::create([
            'user_id' => null,
            'phone' => $this->testPhone,
            'message' => $message,
            'provider' => $this->driver,
            'status' => $result['success'] ? 'sent' : 'failed',
            'response' => $result['response'],
            'sent_at' => $result['success'] ? now() : null,
            'cost' => $result['cost'],
        ]);

        if ($result['success']) {
            $this->notifySuccess('پیامک تستی با موفقیت ارسال شد — اتصال '.$this->driver.' برقرار است.');
        } else {
            $response = (string) $result['response'];
            $message = 'ارسال ناموفق بود: '.mb_substr($response, 0, 180);

            // 412 = ارسال‌کننده نامعتبر یا متعلق به حساب نیست — راهنمای عملی اضافه کن
            if (preg_match('/"status"\s*:\s*412/', $response)) {
                $message .= ' — فرستندهٔ معتبری در حساب کاوه‌نگار ثبت نیست. در پنل کاوه‌نگار به بخش «خطوط و ارسال‌کننده‌ها» بروید و فرستندهٔ موجود را در فیلد «شماره فرستنده» همین صفحه وارد کنید (خالی = ارسال‌کنندهٔ پیش‌فرض حساب). شمارهٔ موبایل معمولی به‌عنوان فرستنده پذیرفته نمی‌شود.';
            }

            // 501 = حساب در حالت تست است فقط ارسال به شماره صاحب حساب مجاز است
            if (preg_match('/"status"\s*:\s*501/', $response)) {
                $message .= ' — حساب کاوه‌نگار در حالت «پیام تست» است و فقط به شماره صاحب حساب ارسال می‌شود. برای ارسال به همه کاربران در پنل کاوه‌نگار اعتبار بخرید و/یا احراز هویت را کامل کنید تا محدودیت برداشته شود (در صورت نیاز با پشتیبانی کاوه‌نگار تیکت بزنید). این محدودیت سمت کاوه‌نگار است و ربطی به تنظیمات سایت ندارد.';
            }

            $this->notifyError($message);
        }
    }

    public function render()
    {
        return view('sms::livewire.admin.settings');
    }

    private function notifySuccess(string $message): void
    {
        $this->successMessage = $message;
        $this->successToast = true;
        $this->errorToast = false;
    }

    private function notifyError(string $message): void
    {
        $this->errorMessage = $message;
        $this->errorToast = true;
        $this->successToast = false;
    }
}