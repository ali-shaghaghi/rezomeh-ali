<?php

namespace Modules\Resume\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\Resume\Models\Language;

class LanguagesManager extends Component
{
    use WithPagination;

    // جستجو
    public string $search = '';

    // مودال افزودن/ویرایش
    public bool $modalOpen = false;

    public ?Language $editingLanguage = null;

    public bool $editing = false;

    // فیلدهای فرم
    public string $name = '';

    public string $code = '';

    public string $level = 'intermediate';

    public int $proficiency = 50;

    public string $description = '';

    public bool $is_active = true;

    public bool $show_in_resume = true;

    // مودال حذف
    public bool $confirmingDelete = false;

    public $deleteId = null;

    // نوتیفیکیشن
    public bool $successToast = false;

    public string $successMessage = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Language::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('code', 'like', "%{$this->search}%");
            });
        }

        return view('resume::livewire.admin.languages-manager', [
            'languages' => $query->ordered()->paginate(9),
        ]);
    }

    // بازکردن مودال ایجاد
    public function openCreateModal(): void
    {
        $this->authorize('create', Language::class);

        $this->resetErrorBag();
        $this->editing = false;
        $this->editingLanguage = null;
        $this->name = '';
        $this->code = '';
        $this->level = 'intermediate';
        $this->proficiency = 50;
        $this->description = '';
        $this->is_active = true;
        $this->show_in_resume = true;
        $this->modalOpen = true;
    }

    // بازکردن مودال ویرایش
    public function openEditModal(int $id): void
    {
        $language = Language::findOrFail($id);
        $this->authorize('update', $language);

        $this->resetErrorBag();
        $this->editing = true;
        $this->editingLanguage = $language;
        $this->name = $language->name;
        $this->code = $language->code ?? '';
        $this->level = $language->level;
        $this->proficiency = $language->proficiency;
        $this->description = $language->description ?? '';
        $this->is_active = (bool) $language->is_active;
        $this->show_in_resume = (bool) $language->show_in_resume;
        $this->modalOpen = true;
    }

    // همگام‌سازی درصد تسلط با سطح زبان
    public function updatedLevel(string $value): void
    {
        $map = [
            'beginner' => 30,
            'intermediate' => 50,
            'advanced' => 75,
            'fluent' => 90,
            'native' => 100,
        ];
        $this->proficiency = $map[$value] ?? 50;
    }

    public function save(): void
    {
        $rules = [
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:10',
            'level' => 'required|in:beginner,intermediate,advanced,fluent,native',
            'proficiency' => 'required|integer|min:0|max:100',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
            'show_in_resume' => 'boolean',
        ];

        $validated = $this->validate($rules);

        $data = [
            'name' => $validated['name'],
            'code' => $validated['code'] ?: null,
            'level' => $validated['level'],
            'proficiency' => $validated['proficiency'],
            'description' => $validated['description'] ?: null,
            'is_active' => $validated['is_active'],
            'show_in_resume' => $validated['show_in_resume'],
        ];

        if ($this->editing) {
            $this->authorize('update', $this->editingLanguage);
            $this->editingLanguage->update($data);
            $message = 'زبان «'.$data['name'].'» به‌روزرسانی شد.';
        } else {
            $this->authorize('create', Language::class);
            Language::create($data);
            $message = 'زبان «'.$data['name'].'» ایجاد شد.';
        }

        $this->modalOpen = false;
        $this->notifySuccess($message);
    }

    // تغییر وضعیت فعال/غیرفعال (ریاکتیو)
    public function toggleActive(int $id): void
    {
        $language = Language::findOrFail($id);
        $this->authorize('update', $language);

        $language->update(['is_active' => ! $language->is_active]);
        $this->notifySuccess($language->is_active
            ? 'زبان «'.$language->name.'» فعال شد.'
            : 'زبان «'.$language->name.'» غیرفعال شد.');
    }

    // تغییر نمایش در رزومه (ریاکتیو)
    public function toggleResume(int $id): void
    {
        $language = Language::findOrFail($id);
        $this->authorize('update', $language);

        $language->update(['show_in_resume' => ! $language->show_in_resume]);
        $this->notifySuccess($language->show_in_resume
            ? 'زبان «'.$language->name.'» برای نمایش در رزومه انتخاب شد.'
            : 'زبان «'.$language->name.'» از رزومه حذف شد.');
    }

    // جابجایی ترتیب
    public function move(int $id, string $direction): void
    {
        $language = Language::findOrFail($id);
        $this->authorize('update', $language);

        $language->order = max(0, $language->order + ($direction === 'up' ? -1 : 1));
        $language->save();
    }

    public function confirmDelete(int $id): void
    {
        $this->deleteId = $id;
        $this->confirmingDelete = true;
    }

    public function deleteLanguage(): void
    {
        $language = Language::findOrFail($this->deleteId);
        $this->authorize('delete', $language);

        $language->delete();
        $this->confirmingDelete = false;
        $this->deleteId = null;
        $this->notifySuccess('زبان «'.$language->name.'» حذف شد.');
    }

    public function resetFilters(): void
    {
        $this->reset(['search']);
        $this->resetPage();
    }

    private function notifySuccess(string $message): void
    {
        $this->successMessage = $message;
        $this->successToast = true;
    }
}
