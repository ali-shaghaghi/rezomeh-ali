<?php

namespace Modules\SMS\Livewire\Admin;

use Illuminate\Support\Str;
use Livewire\Component;
use Modules\SMS\Models\SmsTemplate;

/**
 * مدیریت قالب‌های پیامک — CRUD + پیش‌نمایش رندر متغیرها
 */
class SmsTemplates extends Component
{
    public bool $showForm = false;

    public bool $editing = false;

    public ?int $editId = null;

    public array $form = [
        'key' => '',
        'title' => '',
        'body' => '',
        'variables' => '',
        'is_active' => true,
    ];

    public bool $successToast = false;

    public string $successMessage = '';

    public function openCreateForm(): void
    {
        $this->resetErrorBag();
        $this->editing = false;
        $this->editId = null;
        $this->form = ['key' => '', 'title' => '', 'body' => '', 'variables' => '', 'is_active' => true];
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $this->resetErrorBag();
        $template = SmsTemplate::findOrFail($id);

        $this->editing = true;
        $this->editId = $id;
        $this->form = [
            'key' => $template->key,
            'title' => $template->title,
            'body' => $template->body,
            'variables' => implode(', ', $template->variables ?? []),
            'is_active' => $template->is_active,
        ];
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate([
            'form.key' => ['required', 'string', 'max:100', 'alpha_dash', $this->editing ? 'unique:sms_templates,key,'.$this->editId : 'unique:sms_templates,key'],
            'form.title' => ['required', 'string', 'max:255'],
            'form.body' => ['required', 'string', 'max:1000'],
            'form.variables' => ['nullable', 'string', 'max:500'],
            'form.is_active' => ['boolean'],
        ], [], [
            'form.key' => 'کلید قالب',
            'form.title' => 'عنوان',
            'form.body' => 'متن قالب',
        ]);

        $variables = collect(explode(',', $this->form['variables']))
            ->map(fn ($v) => trim($v))
            ->filter()
            ->values()
            ->all();

        $template = SmsTemplate::updateOrCreate(
            ['id' => $this->editId],
            [
                'key' => $this->form['key'],
                'title' => $this->form['title'],
                'body' => $this->form['body'],
                'variables' => $variables,
                'is_active' => $this->form['is_active'],
            ]
        );

        $this->showForm = false;
        $this->notifySuccess($this->editing ? 'قالب ویرایش شد.' : 'قالب «'.$template->title.'» اضافه شد.');
    }

    public function delete(int $id): void
    {
        SmsTemplate::findOrFail($id)->delete();
        $this->notifySuccess('قالب حذف شد.');
    }

    public function render()
    {
        return view('sms::livewire.admin.templates', [
            'templates' => SmsTemplate::latest()->get(),
            'preview' => $this->form['body'] !== ''
                ? app(\Modules\SMS\Services\SmsService::class)->render(
                    $this->form['body'],
                    collect(explode(',', $this->form['variables']))
                        ->mapWithKeys(fn ($v) => [trim($v) => 'نمونه'])
                        ->filter(fn ($v) => $v !== '')
                        ->all()
                )
                : '',
        ]);
    }

    private function notifySuccess(string $message): void
    {
        $this->successMessage = $message;
        $this->successToast = true;
    }
}