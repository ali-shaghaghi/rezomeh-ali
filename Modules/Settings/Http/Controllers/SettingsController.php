<?php

namespace Modules\Settings\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Settings\Services\SettingsService;

class SettingsController extends Controller
{
    /**
     * صفحه تنظیمات — همه تب‌ها داخل یک کامپوننت Livewire (بدون رفرش بین تب‌ها)
     */
    public function index(string $group = 'site')
    {
        $this->authorizeAccess();

        $schema = app(SettingsService::class)->schema();

        abort_unless(array_key_exists($group, $schema), 404, 'گروه تنظیمات یافت نشد.');

        return view('settings::index', [
            'activeGroup' => $group,
        ]);
    }

    /**
     * هدایت گروه‌های قدیمی تنظیمات به نام جدید (backward compatible)
     */
    public function legacyRedirect(string $group = 'general')
    {
        $this->authorizeAccess();

        $map = [
            'general' => 'site',
        ];

        $target = $map[$group] ?? $group;

        abort_unless(array_key_exists($target, app(SettingsService::class)->schema()), 404, 'گروه تنظیمات یافت نشد.');

        return redirect()->route('admin.settings.group', $target);
    }

    private function authorizeAccess(): void
    {
        abort_unless(
            auth()->user()?->hasPermission('manage_settings'),
            404,
            'چنین صفحه‌ای در سیستم وجود ندارد.'
        );
    }
}
