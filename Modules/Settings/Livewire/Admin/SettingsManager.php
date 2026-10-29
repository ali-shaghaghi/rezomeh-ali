<?php

namespace Modules\Settings\Livewire\Admin;

use Livewire\Component;
use Modules\Settings\Models\Setting;
use Modules\Settings\Services\SettingsService;

class SettingsManager extends Component
{
    // تب فعال (بدون رفرش صفحه جابه‌جا می‌شود)
    public string $activeTab = 'site';

    // مقادیر فرم گروه‌های داینامیک
    public array $values = [];

    // شبکه‌های اجتماعی (CRUD داینامیک)
    public array $socialLinks = [];

    public bool $socialModalOpen = false;

    public bool $socialEditing = false;

    public int $socialEditIndex = -1;

    public array $socialForm = ['name' => '', 'url' => '', 'icon' => 'globe', 'order' => 0];

    // درگاه پرداخت — کدام آکاردئون باز است
    public string $openGateway = '';

    // نوتیفیکیشن
    public bool $successToast = false;

    public string $successMessage = '';

    // شناسه یکتا برای آبجکت‌های alpine در این کامپوننت
    public string $cid = 'settings';

    protected $queryString = [
        'activeTab' => ['except' => 'site', 'as' => 'tab'],
    ];

    public function mount(string $initialTab = 'site'): void
    {
        $this->activeTab = $initialTab;

        $schema = app(SettingsService::class)->schema();

        // سازگاری با /admin/settings?tab=payment — کوئری‌استرینگ فقط وقتی مسیر تب را مشخص نکرده باشد
        $requested = (string) request()->query('tab', '');
        if ($this->activeTab === 'site' && $requested !== '' && array_key_exists($requested, $schema)) {
            $this->activeTab = $requested;
        }

        // فقط گروه‌های دارای فرم داینامیک مقدار اولیه می‌گیرند (profile/appearance/security و... فرم اختصاصی دارند)
        if (! empty($schema[$this->activeTab]['fields'])) {
            $this->values = app(SettingsService::class)->groupValues($this->activeTab);
        }

        if ($this->activeTab === 'payment') {
            $this->loadGatewayState();
        }

        $this->socialLinks = app(SettingsService::class)->socialLinks();
    }

    /**
     * جابه‌جایی تب بدون رفرش — مقادیر فرم گروه جدید لود می‌شود
     */
    public function switchTab(string $tab): void
    {
        $schema = app(SettingsService::class)->schema();

        abort_unless(array_key_exists($tab, $schema), 404, 'گروه تنظیمات یافت نشد.');

        $this->activeTab = $tab;
        $this->successToast = false;

        if (! empty($schema[$tab]['fields'])) {
            $this->values = app(SettingsService::class)->groupValues($tab);
        }

        if ($tab === 'social') {
            $this->socialLinks = app(SettingsService::class)->socialLinks();
        }

        if ($tab === 'payment') {
            // آکاردئون درگاه فعال به‌صورت پیش‌فرض باز + بارگذاری مقادیر درگاه‌ها
            $this->loadGatewayState();
        }
    }

    /**
     * ذخیره گروه داینامیک (site/smtp/ai)
     */
    public function save(): void
    {
        $service = app(SettingsService::class);
        $schema = $service->schema()[$this->activeTab];

        if (empty($schema['fields'])) {
            return;
        }

        $rules = [];
        foreach ($schema['fields'] as $key => $field) {
            $rule = match ($field['type']) {
                'email' => 'nullable|email|max:255',
                // numeric (نه integer) تا ضریب‌های اعشاری مثل 1.3 هم قبول شوند؛
                // max بزرگ برای قیمت‌های ارزی مثل نرخ دلار 250000
                'number' => 'nullable|numeric|min:0|max:9999999999',
                'boolean' => 'boolean',
                'password' => 'nullable|string|max:255',
                'textarea' => 'nullable|string|max:2000',
                default => 'nullable|string|max:500',
            };

            $rules["values.{$key}"] = $rule;
        }

        $validated = $this->validate($rules);

        $service->saveGroup($this->activeTab, $validated['values']);

        $this->notifySuccess('تنظیمات «'.$schema['title'].'» با موفقیت ذخیره شد.');
    }

    // ===== درگاه پرداخت =====

    /**
     * باز/بسته کردن آکاردئون درگاه + سوییچ درگاه فعال
     */
    public function toggleGateway(string $gateway): void
    {
        // اگر درگاه دیگری باز است، بسته می‌شود و این یکی باز می‌شود
        $this->openGateway = $this->openGateway === $gateway ? '' : $gateway;

        // با کلیک روی آکاردئون، همان درگاه «فعال» می‌شود (ذخیره با دکمه جداگانه)
        if ($this->openGateway === $gateway) {
            $this->values['payment_driver'] = $gateway;
            // مرچنت هر درگاه جداگانه ذخیره می‌شود — مقدار همان درگاه باز نمایش داده شود
            $this->values['payment_merchant_id'] = $this->gatewayMerchant($gateway);
        }
    }

    /**
     * بارگذاری مقادیر تب درگاه پرداخت (مشترک + مرچنت اختصاصی درگاه فعال)
     */
    private function loadGatewayState(): void
    {
        $this->values = app(SettingsService::class)->groupValues('payment');

        $driver = (string) ($this->values['payment_driver'] ?? Setting::get('payment_driver', 'zarinpal'));
        $this->values['payment_driver'] = $driver;
        $this->values['payment_merchant_id'] = $this->gatewayMerchant($driver);
        $this->openGateway = $driver;
    }

    /**
     * مرچنت ذخیره‌شده هر درگاه (زرین‌پال/آی‌دی‌پی جدا از هم)
     */
    private function gatewayMerchant(string $gateway): string
    {
        return (string) (Setting::get("payment_merchant_id_{$gateway}") ?? Setting::get('payment_merchant_id') ?? '');
    }

    /**
     * ذخیره تنظیمات درگاه (همراه مرچنت هر درگاه)
     */
    public function saveGateway(string $gateway): void
    {
        $rules = [
            'values.payment_driver' => 'required|in:zarinpal,idpay,manual,crypto',
            'values.payment_merchant_id' => 'nullable|string|max:255',
            'values.payment_callback_url' => 'nullable|url|max:500',
            'values.payment_currency' => 'nullable|string|max:50',
            'values.payment_test_mode' => 'boolean',
            'values.payment_card_number' => 'nullable|string|max:64',
        ];

        $validated = $this->validate($rules)['values'];

        // مرچنت هر درگاه جدا ذخیره می‌شود (وقتی زرین‌پال فعال است مرچنت آی‌دی‌پی دست نخورده می‌ماند)
        $service = app(SettingsService::class);
        $service->saveGroup('payment', $validated);

        if (! empty($validated['payment_merchant_id'])) {
            Setting::set("payment_merchant_id_{$gateway}", $validated['payment_merchant_id'], 'payment');
        }

        // درگاه ارز دیجیتال: فیلدهای اختصاصی (آدرس کیف پول، شبکه، فعال‌سازی + رمزارزهای جایگزین)
        if ($gateway === 'crypto') {
            $this->validate([
                'values.crypto_wallet_address' => ['nullable', 'string', 'max:120'],
                'values.crypto_network' => ['required', 'in:trc20,erc20,bep20'],
                'values.crypto_enabled' => ['boolean'],
                'values.crypto_btc_address' => ['nullable', 'string', 'max:120'],
                'values.crypto_btc_rate' => ['nullable', 'numeric', 'min:0'],
                'values.crypto_eth_address' => ['nullable', 'string', 'max:120'],
                'values.crypto_eth_rate' => ['nullable', 'numeric', 'min:0'],
                'values.crypto_ltc_address' => ['nullable', 'string', 'max:120'],
                'values.crypto_ltc_rate' => ['nullable', 'numeric', 'min:0'],
                'values.crypto_trx_address' => ['nullable', 'string', 'max:120'],
                'values.crypto_trx_rate' => ['nullable', 'numeric', 'min:0'],
            ]);

            $cryptoData = [
                'crypto_wallet_address' => $this->values['crypto_wallet_address'] ?? '',
                'crypto_network' => $this->values['crypto_network'] ?? 'trc20',
                'crypto_enabled' => (bool) ($this->values['crypto_enabled'] ?? false),
            ];

            foreach (['btc', 'eth', 'ltc', 'trx'] as $coin) {
                $cryptoData["crypto_{$coin}_address"] = $this->values["crypto_{$coin}_address"] ?? '';
                $cryptoData["crypto_{$coin}_rate"] = $this->values["crypto_{$coin}_rate"] ?? 0;
            }

            $service->saveGroup('payment', $cryptoData);
        }

        $labels = ['zarinpal' => 'زرین‌پال', 'idpay' => 'آی‌دی‌پی', 'manual' => 'کارت به کارت (دستی)', 'crypto' => 'کیف پول ارز دیجیتال'];

        $this->notifySuccess('تنظیمات درگاه «'.($labels[$gateway] ?? $gateway).'» با موفقیت ذخیره شد.');
    }

    // ===== شبکه‌های اجتماعی =====

    public function openSocialCreate(): void
    {
        $this->resetErrorBag();
        $this->socialEditing = false;
        $this->socialEditIndex = -1;
        $this->socialForm = [
            'name' => '',
            'url' => '',
            'icon' => 'globe',
            'order' => count($this->socialLinks) + 1,
        ];
        $this->socialModalOpen = true;
    }

    public function openSocialEdit(int $index): void
    {
        $this->resetErrorBag();
        $link = $this->socialLinks[$index] ?? null;

        if (! $link) {
            return;
        }

        $this->socialEditing = true;
        $this->socialEditIndex = $index;
        $this->socialForm = [
            'name' => $link['name'],
            'url' => $link['url'],
            'icon' => $link['icon'],
            'order' => $link['order'],
        ];
        $this->socialModalOpen = true;
    }

    public function saveSocial(): void
    {
        $validated = $this->validate([
            'socialForm.name' => 'required|string|max:100',
            'socialForm.url' => 'required|string|max:500|starts_with:http://,https://,@',
            'socialForm.icon' => 'required|string|max:50',
            'socialForm.order' => 'nullable|integer|min:0|max:999',
        ], [
            'socialForm.name.required' => 'نام شبکه الزامی است.',
            'socialForm.url.required' => 'آدرس شبکه الزامی است.',
            'socialForm.url.starts_with' => 'آدرس باید با http:// ، https:// یا @ (آیدی تلگرام و...) شروع شود.',
        ])['socialForm'];

        if ($this->socialEditing && $this->socialEditIndex >= 0) {
            $this->socialLinks[$this->socialEditIndex] = $validated + ['order' => (int) ($validated['order'] ?? 0)];
        } else {
            $this->socialLinks[] = $validated + ['order' => (int) ($validated['order'] ?? (count($this->socialLinks) + 1))];
        }

        app(SettingsService::class)->saveSocialLinks($this->socialLinks);

        $this->socialModalOpen = false;
        $this->notifySuccess($this->socialEditing ? 'شبکه اجتماعی ویرایش شد.' : 'شبکه اجتماعی جدید اضافه شد.');
    }

    public function deleteSocial(int $index): void
    {
        $name = $this->socialLinks[$index]['name'] ?? '';

        unset($this->socialLinks[$index]);
        $this->socialLinks = array_values($this->socialLinks);

        app(SettingsService::class)->saveSocialLinks($this->socialLinks);

        $this->notifySuccess('شبکه اجتماعی «'.$name.'» حذف شد.');
    }

    public function moveSocialUp(int $index): void
    {
        if ($index <= 0) {
            return;
        }

        $tmp = $this->socialLinks[$index - 1];
        $this->socialLinks[$index - 1] = $this->socialLinks[$index];
        $this->socialLinks[$index] = $tmp;

        app(SettingsService::class)->saveSocialLinks($this->socialLinks);
    }

    public function moveSocialDown(int $index): void
    {
        if ($index >= count($this->socialLinks) - 1) {
            return;
        }

        $tmp = $this->socialLinks[$index + 1];
        $this->socialLinks[$index + 1] = $this->socialLinks[$index];
        $this->socialLinks[$index] = $tmp;

        app(SettingsService::class)->saveSocialLinks($this->socialLinks);
    }

    // نمایش نوتیفیکیشن موفقیت
    private function notifySuccess(string $message): void
    {
        $this->successMessage = $message;
        $this->successToast = true;
    }

    public function render()
    {
        $service = app(SettingsService::class);

        return view('settings::livewire.admin.settings-manager', [
            'schema' => $service->schema(),
            'activeSchema' => $service->schema()[$this->activeTab],
            'socialIcons' => $service->socialFields()['icon']['options'],
        ]);
    }
}
