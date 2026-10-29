<?php

namespace Modules\Resume\Livewire\Admin;

use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\Resume\Models\Skill;

class SkillsManager extends Component
{
    use WithPagination;

    // جستجو و فیلتر
    public string $search = '';

    public string $categoryFilter = '';

    // مودال افزودن/ویرایش
    public bool $modalOpen = false;

    public ?Skill $editingSkill = null;

    public bool $editing = false;

    // فیلدهای فرم
    public string $name = '';

    public string $slug = '';

    public string $category = 'other';

    public int $proficiency = 0;

    public string $icon = '';

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

    public function updatingCategoryFilter(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Skill::query();

        if ($this->search) {
            $query->where('name', 'like', "%{$this->search}%")
                ->orWhere('slug', 'like', "%{$this->search}%");
        }

        if ($this->categoryFilter) {
            $query->where('category', $this->categoryFilter);
        }

        return view('resume::livewire.admin.skills-manager', [
            'skills' => $query->ordered()->paginate(9),
        ]);
    }

    // بازکردن مودال ایجاد
    public function openCreateModal(): void
    {
        $this->authorize('create', Skill::class);

        $this->resetErrorBag();
        $this->editing = false;
        $this->editingSkill = null;
        $this->name = '';
        $this->slug = '';
        $this->category = 'other';
        $this->proficiency = 0;
        $this->icon = '';
        $this->is_active = true;
        $this->show_in_resume = true;
        $this->modalOpen = true;
    }

    // بازکردن مودال ویرایش
    public function openEditModal(int $id): void
    {
        $skill = Skill::findOrFail($id);
        $this->authorize('update', $skill);

        $this->resetErrorBag();
        $this->editing = true;
        $this->editingSkill = $skill;
        $this->name = $skill->name;
        $this->slug = $skill->slug;
        $this->category = $skill->category;
        $this->proficiency = $skill->proficiency;
        $this->icon = $skill->icon ?? '';
        $this->is_active = (bool) $skill->is_active;
        $this->show_in_resume = (bool) $skill->show_in_resume;
        $this->modalOpen = true;
    }

    // تولید خودکار slug از نام
    public function updatedName(string $value): void
    {
        if (! $this->editing || empty($this->slug)) {
            $slug = Str::slug($value);
            $this->slug = $slug ?: Str::slug(Str::ascii($value)) ?: 'skill-'.Str::random(6);
        }
    }

    public function save(): void
    {
        $rules = [
            'name' => 'required|string|max:255',
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', $this->editing
                ? 'unique:skills,slug,'.($this->editingSkill->id ?? 0)
                : 'unique:skills,slug'],
            'category' => 'required|in:frontend,backend,devops,database,mobile,soft-skills,other',
            'proficiency' => 'required|integer|min:0|max:100',
            'icon' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'show_in_resume' => 'boolean',
        ];

        $validated = $this->validate($rules);

        if ($this->editing) {
            $this->authorize('update', $this->editingSkill);
            $this->editingSkill->update($validated);
            $message = 'مهارت «'.$validated['name'].'» به‌روزرسانی شد.';
        } else {
            $this->authorize('create', Skill::class);
            Skill::create($validated);
            $message = 'مهارت «'.$validated['name'].'» ایجاد شد.';
        }

        $this->modalOpen = false;
        $this->notifySuccess($message);
    }

    // تغییر وضعیت فعال/غیرفعال (ریاکتیو)
    public function toggleActive(int $id): void
    {
        $skill = Skill::findOrFail($id);
        $this->authorize('update', $skill);

        $skill->update(['is_active' => ! $skill->is_active]);
        $this->notifySuccess($skill->is_active
            ? 'مهارت «'.$skill->name.'» فعال شد.'
            : 'مهارت «'.$skill->name.'» غیرفعال شد.');
    }

    // تغییر نمایش در رزومه (ریاکتیو)
    public function toggleResume(int $id): void
    {
        $skill = Skill::findOrFail($id);
        $this->authorize('update', $skill);

        $skill->update(['show_in_resume' => ! $skill->show_in_resume]);
        $this->notifySuccess($skill->show_in_resume
            ? 'مهارت «'.$skill->name.'» برای نمایش در رزومه انتخاب شد.'
            : 'مهارت «'.$skill->name.'» از رزومه حذف شد.');
    }

    // جابجایی ترتیب
    public function move(int $id, string $direction): void
    {
        $skill = Skill::findOrFail($id);
        $this->authorize('update', $skill);

        $skill->order = max(0, $skill->order + ($direction === 'up' ? -1 : 1));
        $skill->save();
    }

    public function confirmDelete(int $id): void
    {
        $this->deleteId = $id;
        $this->confirmingDelete = true;
    }

    public function deleteSkill(): void
    {
        $skill = Skill::findOrFail($this->deleteId);
        $this->authorize('delete', $skill);

        $skill->delete();
        $this->confirmingDelete = false;
        $this->deleteId = null;
        $this->notifySuccess('مهارت «'.$skill->name.'» حذف شد.');
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'categoryFilter']);
        $this->resetPage();
    }

    private function notifySuccess(string $message): void
    {
        $this->successMessage = $message;
        $this->successToast = true;
    }
}
