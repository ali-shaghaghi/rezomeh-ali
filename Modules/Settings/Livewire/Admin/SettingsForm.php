<?php

namespace Modules\Settings\Livewire\Admin;

use Livewire\Component;
use Modules\Settings\Services\SettingsService;

class SettingsForm extends Component
{
    // گروه فعال
    public string $group = 'general';

    // مقادیر فرم (داینامیک بر اساس schema)
    public array $values = [];

    // نوتیفیکیشن
    public bool $successToast = false;

    public string $successMessage = '';

    public function mount(string $group, array $initialValues = []): void
    {
        $this->group = $group;
        $this->values = $initialValues;
    }

    public function save(): void
    {
        $service = app(SettingsService::class);
        $schema = $service->schema()[$this->group];

        // اعتبارسنجی داینامیک بر اساس نوع فیلدها
        $rules = [];
        foreach ($schema['fields'] as $key => $field) {
            $rule = match ($field['type']) {
                'email' => 'nullable|email|max:255',
                // numeric (نه integer) تا ضریب‌های اعشاری مثل 1.3 قبول شوند؛
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

        $service->saveGroup($this->group, $validated['values']);

        $this->notifySuccess('تنظیمات «'.$schema['title'].'» با موفقیت ذخیره شد.');
    }

    // نمایش نوتیفیکیشن موفقیت
    private function notifySuccess(string $message): void
    {
        $this->successMessage = $message;
        $this->successToast = true;
    }

    public function render()
    {
        $schema = app(SettingsService::class)->schema()[$this->group];

        return view('settings::livewire.admin.settings-form', [
            'schema' => $schema,
        ]);
    }
}
