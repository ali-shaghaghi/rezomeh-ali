<?php

namespace Modules\UserPanel\Livewire;

use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * شل ریل‌تایم پنل کاربر — سوییچ بین بخش‌ها بدون رفرش صفحه.
 *
 * ماژول UserPanel فقط داشبورد را نگه می‌دارد؛ هر بخش دیگر
 * کامپوننت Livewire مخصوص خودش را از ماژول خودش رندر می‌کند:
 *   orders → orders.my-orders | tickets → tickets.my-tickets |
 *   products → user.my-products | projects → projectdev.my-projects |
 *   consultations → consultation.my-consultations | security → auth.account-security
 *
 * نکته: تب notifications حذف شده است — طبق درخواست، اعلان‌ها ویژگی ادمین است
 * و کاربران عادی به آن دسترسی ندارند (لینک‌های قدیمی به داشبورد برمی‌گردند).
 */
class PanelShell extends Component
{
    /** تب فعال (سینک با ?tab= در URL) */
    #[Url]
    public string $tab = 'dashboard';

    /** تب اولیه از کنترلر (سازگاری /dashboard?tab=X قدیمی) */
    public string $initialTab = 'dashboard';

    /** نگاشت تب‌های مجاز → کامپوننت Livewire هر ماژول (برچسب دو‌زبانه از lang/panel.php) */
    public const TABS = [
        'dashboard' => ['component' => 'userpanel.dashboard', 'label_key' => 'panel.tabs.dashboard', 'icon' => 'dashboard'],
        'orders' => ['component' => 'orders.my-orders', 'label_key' => 'panel.tabs.orders', 'icon' => 'orders'],
        'products' => ['component' => 'user.my-products', 'label_key' => 'panel.tabs.products', 'icon' => 'download'],
        'projects' => ['component' => 'projectdev.my-projects', 'label_key' => 'panel.tabs.projects', 'icon' => 'products'],
        'tickets' => ['component' => 'tickets.my-tickets', 'label_key' => 'panel.tabs.tickets', 'icon' => 'mail'],
        'consultations' => ['component' => 'consultation.my-consultations', 'label_key' => 'panel.tabs.consultations', 'icon' => 'users'],
        'security' => ['component' => 'auth.account-security', 'label_key' => 'panel.tabs.security', 'icon' => 'settings'],
    ];

    /** تب‌ها با برچسب ترجمه‌شده برای ویو */
    public function tabsForView(): array
    {
        return collect(self::TABS)->map(fn ($def) => [
            'component' => $def['component'],
            'label' => __($def['label_key']),
            'icon' => $def['icon'],
        ])->all();
    }

    public function mount(string $initialTab = 'dashboard'): void
    {
        // سازگاری با /dashboard?tab=X قدیمی — فقط اگر کوئری‌استرینگ تب را مشخص نکرده باشد
        $requested = (string) request()->query('tab', '');

        if ($requested !== '' && array_key_exists($requested, self::TABS)) {
            $this->tab = $requested;
        } elseif (array_key_exists($initialTab, self::TABS)) {
            $this->tab = $initialTab;
        }

        $this->initialTab = $this->tab;
    }

    /**
     * سوییچ تب بدون رفرش (الگوی SettingsManager ادمین)
     */
    public function switchTab(string $tab): void
    {
        abort_unless(array_key_exists($tab, self::TABS), 404);

        $this->tab = $tab;
    }

    protected function queryParams()
    {
        return [];
    }

    public function render()
    {
        $user = auth()->user();
        $navStats = $user ? app(\Modules\UserPanel\Services\UserPanelService::class)->stats($user) : [];

        // بج‌های سایدبار (تعداد در انتظار هر بخش)
        $badges = [
            'projects' => $navStats['projects_pending'] ?? app(\Modules\ProjectDev\Services\ProjectService::class)->userStats($user)['projects_pending'] ?? 0,
            'tickets' => $navStats['tickets_open'] ?? 0,
        ];

        return view('userpanel::livewire.panel-shell', [
            'tabs' => $this->tabsForView(),
            'badges' => $badges,
        ]);
    }
}
