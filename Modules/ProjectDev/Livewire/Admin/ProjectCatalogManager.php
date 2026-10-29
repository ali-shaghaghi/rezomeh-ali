<?php

namespace Modules\ProjectDev\Livewire\Admin;

use Livewire\Component;
use Modules\ProjectDev\Models\ProjectOption;
use Modules\ProjectDev\Models\ProjectType;

/**
 * کاتالوگ قیمت‌گذاری — مدیریت انواع پروژه + ویژگی‌ها و قیمت‌های هرکدام
 */
class ProjectCatalogManager extends Component
{
    // ---------- نوع پروژه ----------
    public bool $typeModalOpen = false;

    public bool $typeEditing = false;

    public $typeId = null;

    public array $typeForm = ['name' => '', 'description' => '', 'base_price' => 0, 'is_active' => true];

    // ---------- ویژگی/گزینه ----------
    public bool $optionModalOpen = false;

    public bool $optionEditing = false;

    public $optionId = null;

    public array $optionForm = ['name' => '', 'description' => '', 'price' => 0, 'project_type_id' => '', 'is_active' => true];

    // ---------- حذف ----------
    public bool $confirmingTypeDelete = false;

    public $deleteTypeId = null;

    public bool $confirmingOptionDelete = false;

    public $deleteOptionId = null;

    // نوتیفیکیشن
    public bool $successToast = false;

    public string $successMessage = '';

    public function openTypeCreate(): void
    {
        $this->resetErrorBag();
        $this->typeEditing = false;
        $this->typeId = null;
        $this->typeForm = ['name' => '', 'description' => '', 'base_price' => 0, 'is_active' => true];
        $this->typeModalOpen = true;
    }

    public function openTypeEdit(int $id): void
    {
        $this->resetErrorBag();
        $type = ProjectType::findOrFail($id);
        $this->authorize('update', $type);

        $this->typeEditing = true;
        $this->typeId = $id;
        $this->typeForm = [
            'name' => $type->name,
            'description' => (string) $type->description,
            'base_price' => (float) $type->base_price,
            'is_active' => $type->is_active,
        ];
        $this->typeModalOpen = true;
    }

    public function saveType(): void
    {
        $this->authorize('create', ProjectType::class);

        $validated = $this->validate([
            'typeForm.name' => ['required', 'string', 'max:150', 'unique:project_types,name'.($this->typeEditing ? ','.$this->typeId : '')],
            'typeForm.description' => ['nullable', 'string', 'max:1000'],
            'typeForm.base_price' => ['required', 'numeric', 'min:0'],
            'typeForm.is_active' => ['boolean'],
        ], [
            'typeForm.name.required' => 'نام نوع پروژه الزامی است.',
            'typeForm.name.unique' => 'این نوع پروژه قبلا ثبت شده است.',
            'typeForm.base_price.required' => 'قیمت پایه الزامی است.',
            'typeForm.base_price.min' => 'قیمت پایه نمی‌تواند منفی باشد.',
        ])['typeForm'];

        ProjectType::updateOrCreate(
            ['id' => $this->typeEditing ? $this->typeId : null],
            $validated + ['slug' => \Illuminate\Support\Str::slug($validated['name']) ?: 'type-'.\Illuminate\Support\Str::random(6)]
        );

        $this->typeModalOpen = false;
        $this->notifySuccess($this->typeEditing ? 'نوع پروژه ویرایش شد.' : 'نوع پروژه جدید اضافه شد.');
    }

    public function confirmTypeDelete($id): void
    {
        $this->deleteTypeId = $id;
        $this->confirmingTypeDelete = true;
    }

    public function deleteType(): void
    {
        $type = ProjectType::findOrFail($this->deleteTypeId);
        $this->authorize('delete', $type);

        $type->delete();

        $this->confirmingTypeDelete = false;
        $this->deleteTypeId = null;
        $this->notifySuccess('نوع پروژه حذف شد.');
    }

    // ===================== ویژگی‌ها =====================

    public function openOptionCreate(): void
    {
        $this->resetErrorBag();
        $this->optionEditing = false;
        $this->optionId = null;
        $this->optionForm = ['name' => '', 'description' => '', 'price' => 0, 'project_type_id' => '', 'is_active' => true];
        $this->optionModalOpen = true;
    }

    public function openOptionEdit(int $id): void
    {
        $this->resetErrorBag();
        $option = ProjectOption::findOrFail($id);
        $this->authorize('update', ProjectType::class);

        $this->optionEditing = true;
        $this->optionId = $id;
        $this->optionForm = [
            'name' => $option->name,
            'description' => (string) $option->description,
            'price' => (float) $option->price,
            'project_type_id' => $option->project_type_id ?? '',
            'is_active' => $option->is_active,
        ];
        $this->optionModalOpen = true;
    }

    public function saveOption(): void
    {
        $this->authorize('create', ProjectType::class);

        $validated = $this->validate([
            'optionForm.name' => ['required', 'string', 'max:190'],
            'optionForm.description' => ['nullable', 'string', 'max:500'],
            'optionForm.price' => ['required', 'numeric', 'min:0'],
            'optionForm.project_type_id' => ['nullable'],
            'optionForm.is_active' => ['boolean'],
        ], [
            'optionForm.name.required' => 'نام ویژگی الزامی است.',
            'optionForm.price.required' => 'قیمت ویژگی الزامی است.',
        ])['optionForm'];

        ProjectOption::updateOrCreate(
            ['id' => $this->optionEditing ? $this->optionId : null],
            [
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'price' => $validated['price'],
                'project_type_id' => $validated['project_type_id'] !== '' && $validated['project_type_id'] !== null
                    ? (int) $validated['project_type_id']
                    : null,
                'is_active' => $validated['is_active'],
            ]
        );

        $this->optionModalOpen = false;
        $this->notifySuccess($this->optionEditing ? 'ویژگی ویرایش شد.' : 'ویژگی جدید اضافه شد.');
    }

    public function confirmOptionDelete($id): void
    {
        $this->deleteOptionId = $id;
        $this->confirmingOptionDelete = true;
    }

    public function deleteOption(): void
    {
        $option = ProjectOption::findOrFail($this->deleteOptionId);
        $this->authorize('delete', ProjectType::class);

        $option->delete();

        $this->confirmingOptionDelete = false;
        $this->deleteOptionId = null;
        $this->notifySuccess('ویژگی حذف شد.');
    }

    private function notifySuccess(string $message): void
    {
        $this->successMessage = $message;
        $this->successToast = true;
    }

    public function render()
    {
        $types = ProjectType::with('options')->ordered()->get();
        $globalOptions = ProjectOption::whereNull('project_type_id')->orderBy('sort_order')->orderBy('id')->get();

        return view('projectdev::livewire.admin.project-catalog', [
            'types' => $types,
            'globalOptions' => $globalOptions,
        ]);
    }
}
