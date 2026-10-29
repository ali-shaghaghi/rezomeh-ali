<?php

namespace Modules\Resume\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\Resume\Models\Education;

class EducationManager extends Component
{
    use WithPagination;

    // جستجو
    public string $search = '';

    // مودال افزودن/ویرایش
    public bool $modalOpen = false;

    public ?Education $editingEducation = null;

    public bool $editing = false;

    // فیلدهای فرم
    public string $institution = '';

    public string $degree = '';

    public string $field_of_study = '';

    public string $grade = '';

    public $start_date = null;

    public $end_date = null;

    public bool $is_current = false;

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
        $query = Education::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('institution', 'like', "%{$this->search}%")
                    ->orWhere('field_of_study', 'like', "%{$this->search}%")
                    ->orWhere('degree', 'like', "%{$this->search}%");
            });
        }

        return view('resume::livewire.admin.education-manager', [
            'educations' => $query->ordered()->paginate(9),
        ]);
    }

    // بازکردن مودال ایجاد
    public function openCreateModal(): void
    {
        $this->authorize('create', Education::class);

        $this->resetErrorBag();
        $this->editing = false;
        $this->editingEducation = null;
        $this->institution = '';
        $this->degree = '';
        $this->field_of_study = '';
        $this->grade = '';
        $this->start_date = null;
        $this->end_date = null;
        $this->is_current = false;
        $this->description = '';
        $this->is_active = true;
        $this->show_in_resume = true;
        $this->modalOpen = true;
    }

    // بازکردن مودال ویرایش
    public function openEditModal(int $id): void
    {
        $education = Education::findOrFail($id);
        $this->authorize('update', $education);

        $this->resetErrorBag();
        $this->editing = true;
        $this->editingEducation = $education;
        $this->institution = $education->institution;
        $this->degree = $education->degree;
        $this->field_of_study = $education->field_of_study;
        $this->grade = $education->grade ?? '';
        $this->start_date = $education->start_date?->format('Y-m-d');
        $this->end_date = $education->end_date?->format('Y-m-d');
        $this->is_current = (bool) $education->is_current;
        $this->description = $education->description ?? '';
        $this->is_active = (bool) $education->is_active;
        $this->show_in_resume = (bool) $education->show_in_resume;
        $this->modalOpen = true;
    }

    public function save(): void
    {
        $rules = [
            'institution' => 'required|string|max:255',
            'degree' => 'required|string|max:255',
            'field_of_study' => 'required|string|max:255',
            'grade' => 'nullable|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_current' => 'boolean',
            'description' => 'nullable|string|max:5000',
            'is_active' => 'boolean',
            'show_in_resume' => 'boolean',
        ];

        // دوره جاری نباید تاریخ پایان داشته باشد
        if ($this->is_current) {
            $this->end_date = null;
        }

        $validated = $this->validate($rules);

        $data = [
            'institution' => $validated['institution'],
            'degree' => $validated['degree'],
            'field_of_study' => $validated['field_of_study'],
            'grade' => $validated['grade'] ?: null,
            'start_date' => $validated['start_date'],
            'end_date' => $this->is_current ? null : ($validated['end_date'] ?? null),
            'is_current' => $validated['is_current'],
            'description' => $validated['description'] ?: null,
            'is_active' => $validated['is_active'],
            'show_in_resume' => $validated['show_in_resume'],
        ];

        if ($this->editing) {
            $this->authorize('update', $this->editingEducation);
            $this->editingEducation->update($data);
            $message = 'مقطع تحصیلی «'.$data['institution'].'» به‌روزرسانی شد.';
        } else {
            $this->authorize('create', Education::class);
            Education::create($data);
            $message = 'مقطع تحصیلی «'.$data['institution'].'» ایجاد شد.';
        }

        $this->modalOpen = false;
        $this->notifySuccess($message);
    }

    // تغییر وضعیت فعال/غیرفعال
    public function toggleActive(int $id): void
    {
        $education = Education::findOrFail($id);
        $this->authorize('update', $education);

        $education->update(['is_active' => ! $education->is_active]);
        $this->notifySuccess($education->is_active
            ? 'مقطع «'.$education->institution.'» فعال شد.'
            : 'مقطع «'.$education->institution.'» غیرفعال شد.');
    }

    // تغییر نمایش در رزومه
    public function toggleResume(int $id): void
    {
        $education = Education::findOrFail($id);
        $this->authorize('update', $education);

        $education->update(['show_in_resume' => ! $education->show_in_resume]);
        $this->notifySuccess($education->show_in_resume
            ? 'مقطع «'.$education->institution.'» به رزومه اضافه شد.'
            : 'مقطع «'.$education->institution.'» از رزومه حذف شد.');
    }

    // جابجایی ترتیب
    public function move(int $id, string $direction): void
    {
        $education = Education::findOrFail($id);
        $this->authorize('update', $education);

        $education->order = max(0, $education->order + ($direction === 'up' ? -1 : 1));
        $education->save();
    }

    public function confirmDelete(int $id): void
    {
        $this->deleteId = $id;
        $this->confirmingDelete = true;
    }

    public function deleteEducation(): void
    {
        $education = Education::findOrFail($this->deleteId);
        $this->authorize('delete', $education);

        $education->delete();
        $this->confirmingDelete = false;
        $this->deleteId = null;
        $this->notifySuccess('مقطع تحصیلی «'.$education->institution.'» حذف شد.');
    }

    private function notifySuccess(string $message): void
    {
        $this->successMessage = $message;
        $this->successToast = true;
    }
}
