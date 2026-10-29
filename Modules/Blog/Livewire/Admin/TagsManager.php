<?php

namespace Modules\Blog\Livewire\Admin;

use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\Core\Models\BlogPost;
use Modules\Core\Models\Tag;

class TagsManager extends Component
{
    use WithPagination;

    // جستجو
    public string $search = '';

    // مودال افزودن/ویرایش
    public bool $modalOpen = false;

    public ?Tag $editingTag = null;

    public bool $editing = false;

    // فیلدهای فرم
    public string $name = '';

    public string $slug = '';

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
        $query = Tag::query();

        if ($this->search) {
            $query->where('name', 'like', "%{$this->search}%")
                ->orWhere('slug', 'like', "%{$this->search}%");
        }

        return view('blog::livewire.admin.tags-manager', [
            'tags' => $query->withCount('blogPosts')->latest()->paginate(10),
        ]);
    }

    // بازکردن مودال ایجاد
    public function openCreateModal(): void
    {
        $this->authorize('create', BlogPost::class);

        $this->resetErrorBag();
        $this->editing = false;
        $this->editingTag = null;
        $this->name = '';
        $this->slug = '';
        $this->modalOpen = true;
    }

    // بازکردن مودال ویرایش
    public function openEditModal(int $id): void
    {
        $tag = Tag::findOrFail($id);
        abort_unless(auth()->user()?->hasPermission('manage_blog'), 404, 'چنین صفحه‌ای در سیستم وجود ندارد.');

        $this->resetErrorBag();
        $this->editing = true;
        $this->editingTag = $tag;
        $this->name = $tag->name;
        $this->slug = $tag->slug;
        $this->modalOpen = true;
    }

    // تولید خودکار slug از نام
    public function updatedName(string $value): void
    {
        if (! $this->editing || empty($this->slug)) {
            $slug = Str::slug($value);
            $this->slug = $slug ?: Str::slug(Str::ascii($value)) ?: 'tag-'.Str::random(6);
        }
    }

    public function save(): void
    {
        abort_unless(auth()->user()?->hasPermission('manage_blog'), 404, 'چنین صفحه‌ای در سیستم وجود ندارد.');

        $rules = [
            'name' => 'required|string|max:255',
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', $this->editing
                ? 'unique:tags,slug,'.($this->editingTag->id ?? 0)
                : 'unique:tags,slug'],
        ];

        $validated = $this->validate($rules);

        if ($this->editing) {
            $this->editingTag->update($validated);
            $message = 'برچسب «'.$validated['name'].'» به‌روزرسانی شد.';
        } else {
            Tag::create($validated);
            $message = 'برچسب «'.$validated['name'].'» ایجاد شد.';
        }

        $this->modalOpen = false;
        $this->notifySuccess($message);
    }

    public function confirmDelete(int $id): void
    {
        $this->deleteId = $id;
        $this->confirmingDelete = true;
    }

    public function deleteTag(): void
    {
        abort_unless(auth()->user()?->hasPermission('manage_blog'), 404, 'چنین صفحه‌ای در سیستم وجود ندارد.');

        $tag = Tag::findOrFail($this->deleteId);
        $tag->blogPosts()->detach();
        $tag->delete();

        $this->confirmingDelete = false;
        $this->deleteId = null;
        $this->notifySuccess('برچسب «'.$tag->name.'» حذف شد.');
    }

    public function resetFilters(): void
    {
        $this->reset('search');
        $this->resetPage();
    }

    // نمایش نوتیفیکیشن موفقیت
    private function notifySuccess(string $message): void
    {
        $this->successMessage = $message;
        $this->successToast = true;
    }
}
