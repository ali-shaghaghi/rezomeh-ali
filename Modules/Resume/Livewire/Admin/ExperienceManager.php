<?php

namespace Modules\Resume\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\Resume\Models\Experience;

class ExperienceManager extends Component
{
    use WithPagination;

    // جستجو
    public string $search = '';

    // مودال افزودن/ویرایش
    public bool $modalOpen = false;

    public ?Experience $editingExperience = null;

    public bool $editing = false;

    // فیلدهای فرم
    public string $company = '';

    public string $position = '';

    public string $employment_type = 'full-time';

    public string $location = '';

    public $start_date = null;

    public $end_date = null;

    public bool $is_current = false;

    public string $description = '';

    public string $technologies_input = '';

    public string $company_url = '';

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
        $query = Experience::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('company', 'like', "%{$this->search}%")
                    ->orWhere('position', 'like', "%{$this->search}%");
            });
        }

        return view('resume::livewire.admin.experience-manager', [
            'experiences' => $query->ordered()->paginate(9),
        ]);
    }

    // بازکردن مودال ایجاد
    public function openCreateModal(): void
    {
        $this->authorize('create', Experience::class);

        $this->resetErrorBag();
        $this->editing = false;
        $this->editingExperience = null;
        $this->company = '';
        $this->position = '';
        $this->employment_type = 'full-time';
        $this->location = '';
        $this->start_date = null;
        $this->end_date = null;
        $this->is_current = false;
        $this->description = '';
        $this->technologies_input = '';
        $this->company_url = '';
        $this->is_active = true;
        $this->show_in_resume = true;
        $this->modalOpen = true;
    }

    // بازکردن مودال ویرایش
    public function openEditModal(int $id): void
    {
        $experience = Experience::findOrFail($id);
        $this->authorize('update', $experience);

        $this->resetErrorBag();
        $this->editing = true;
        $this->editingExperience = $experience;
        $this->company = $experience->company;
        $this->position = $experience->position;
        $this->employment_type = $experience->employment_type;
        $this->location = $experience->location ?? '';
        $this->start_date = $experience->start_date?->format('Y-m-d');
        $this->end_date = $experience->end_date?->format('Y-m-d');
        $this->is_current = (bool) $experience->is_current;
        $this->description = $experience->description ?? '';
        $this->technologies_input = implode(', ', $experience->technologies ?? []);
        $this->company_url = $experience->company_url ?? '';
        $this->is_active = (bool) $experience->is_active;
        $this->show_in_resume = (bool) $experience->show_in_resume;
        $this->modalOpen = true;
    }

    public function save(): void
    {
        $rules = [
            'company' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'employment_type' => 'required|in:full-time,part-time,freelance,remote,internship',
            'location' => 'nullable|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_current' => 'boolean',
            'description' => 'nullable|string|max:5000',
            'company_url' => 'nullable|url|max:255',
            'is_active' => 'boolean',
            'show_in_resume' => 'boolean',
        ];

        if ($this->is_current) {
            $this->end_date = null;
        }

        $validated = $this->validate($rules);

        // تبدیل رشته تکنولوژی‌ها به آرایه
        $technologies = array_values(array_filter(array_map('trim', explode(',', $this->technologies_input))));

        $data = [
            'company' => $validated['company'],
            'position' => $validated['position'],
            'employment_type' => $validated['employment_type'],
            'location' => $validated['location'] ?: null,
            'start_date' => $validated['start_date'],
            'end_date' => $this->is_current ? null : ($validated['end_date'] ?? null),
            'is_current' => $validated['is_current'],
            'description' => $validated['description'] ?: null,
            'technologies' => $technologies ?: null,
            'company_url' => $validated['company_url'] ?: null,
            'is_active' => $validated['is_active'],
            'show_in_resume' => $validated['show_in_resume'],
        ];

        if ($this->editing) {
            $this->authorize('update', $this->editingExperience);
            $this->editingExperience->update($data);
            $message = 'سابقه کاری «'.$data['company'].'» به‌روزرسانی شد.';
        } else {
            $this->authorize('create', Experience::class);
            Experience::create($data);
            $message = 'سابقه کاری «'.$data['company'].'» ایجاد شد.';
        }

        $this->modalOpen = false;
        $this->notifySuccess($message);
    }

    // تغییر وضعیت فعال/غیرفعال
    public function toggleActive(int $id): void
    {
        $experience = Experience::findOrFail($id);
        $this->authorize('update', $experience);

        $experience->update(['is_active' => ! $experience->is_active]);
        $this->notifySuccess($experience->is_active
            ? 'سابقه «'.$experience->company.'» فعال شد.'
            : 'سابقه «'.$experience->company.'» غیرفعال شد.');
    }

    // تغییر نمایش در رزومه
    public function toggleResume(int $id): void
    {
        $experience = Experience::findOrFail($id);
        $this->authorize('update', $experience);

        $experience->update(['show_in_resume' => ! $experience->show_in_resume]);
        $this->notifySuccess($experience->show_in_resume
            ? 'سابقه «'.$experience->company.'» به رزومه اضافه شد.'
            : 'سابقه «'.$experience->company.'» از رزومه حذف شد.');
    }

    // جابجایی ترتیب
    public function move(int $id, string $direction): void
    {
        $experience = Experience::findOrFail($id);
        $this->authorize('update', $experience);

        $experience->order = max(0, $experience->order + ($direction === 'up' ? -1 : 1));
        $experience->save();
    }

    public function confirmDelete(int $id): void
    {
        $this->deleteId = $id;
        $this->confirmingDelete = true;
    }

    public function deleteExperience(): void
    {
        $experience = Experience::findOrFail($this->deleteId);
        $this->authorize('delete', $experience);

        $experience->delete();
        $this->confirmingDelete = false;
        $this->deleteId = null;
        $this->notifySuccess('سابقه کاری «'.$experience->company.'» حذف شد.');
    }

    private function notifySuccess(string $message): void
    {
        $this->successMessage = $message;
        $this->successToast = true;
    }
}
