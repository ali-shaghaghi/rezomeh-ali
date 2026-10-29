<?php

namespace Modules\ProjectDev\Livewire\Public;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Modules\ProjectDev\Services\ProjectService;

/**
 * فرم ثبت درخواست پروژه — مثل فرم ثبت تیکت جدید
 * انتخاب نوع پروژه + ویژگی‌ها با به‌روزرسانی زنده قیمت + افزودن ویژگی دلخواه
 */
class ProjectRequestForm extends Component
{
    public array $form = [
        'title' => '',
        'project_type_id' => null,
        'description' => '',
    ];

    /** @var array<int> */
    public array $selectedOptionIds = [];

    /** @var array<array{name: string, note: string}> */
    public array $customOptions = [];

    public array $customOptionForm = ['name' => '', 'note' => ''];

    public bool $successToast = false;

    public string $successMessage = '';

    public $createdRequestId = null;

    public function toggleOption(int $optionId): void
    {
        if (in_array($optionId, $this->selectedOptionIds, true)) {
            $this->selectedOptionIds = array_values(array_diff($this->selectedOptionIds, [$optionId]));
        } else {
            $this->selectedOptionIds[] = $optionId;
        }
    }

    public function selectType(?int $typeId): void
    {
        $this->form['project_type_id'] = $typeId;
        $this->selectedOptionIds = [];
    }

    /**
     * افزودن ویژگی دلخواه (گزینه‌ای که در کاتالوگ نیست) — قیمتش در زمان بررسی ادمین تعیین می‌شود
     */
    public function addCustomOption(): void
    {
        $this->validate([
            'customOptionForm.name' => ['required', 'string', 'max:190'],
            'customOptionForm.note' => ['nullable', 'string', 'max:500'],
        ], [
            'customOptionForm.name.required' => 'نام ویژگی دلخواه الزامی است.',
        ]);

        $this->customOptions[] = [
            'name' => trim($this->customOptionForm['name']),
            'note' => trim($this->customOptionForm['note'] ?? ''),
        ];

        $this->customOptionForm = ['name' => '', 'note' => ''];
    }

    public function removeCustomOption(int $index): void
    {
        unset($this->customOptions[$index]);
        $this->customOptions = array_values($this->customOptions);
    }

    public function submit(): void
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $this->validate([
            'form.title' => ['required', 'string', 'min:3', 'max:190'],
            'form.project_type_id' => ['required', 'integer'],
            'form.description' => ['nullable', 'string', 'max:3000'],
            'selectedOptionIds' => ['nullable', 'array'],
            'selectedOptionIds.*' => ['integer'],
            'customOptions' => ['nullable', 'array'],
        ], [
            'form.title.required' => 'موضوع پروژه الزامی است.',
            'form.title.min' => 'موضوع باید حداقل ۳ کاراکتر باشد.',
            'form.project_type_id.required' => 'انتخاب نوع پروژه الزامی است.',
        ]);

        $request = app(ProjectService::class)->createRequest($user, [
            'title' => $validated['form']['title'],
            'project_type_id' => $validated['form']['project_type_id'],
            'description' => $validated['form']['description'],
            'option_ids' => $validated['selectedOptionIds'] ?? [],
            'custom_options' => $this->customOptions,
        ]);

        $this->createdRequestId = $request->id;
        $this->successMessage = 'درخواست پروژه شما ثبت شد؛ به‌زودی بررسی و قیمت‌گذاری می‌شود.';
        $this->successToast = true;
    }

    public function resetForm(): void
    {
        $this->form = ['title' => '', 'project_type_id' => null, 'description' => ''];
        $this->selectedOptionIds = [];
        $this->customOptions = [];
        $this->createdRequestId = null;
        $this->successToast = false;
    }

    public function render()
    {
        $service = app(ProjectService::class);
        $types = $service->activeTypes();

        $activeTypeId = $this->form['project_type_id'];
        $options = $service->activeOptionsForType($activeTypeId);

        $activeType = $types->firstWhere('id', $activeTypeId);
        $estimatedPrice = $service->estimatePrice($activeType, $this->selectedOptionIds);

        return view('projectdev::livewire.public.project-request-form', [
            'types' => $types,
            'options' => $options,
            'estimatedPrice' => $estimatedPrice,
        ]);
    }
}
