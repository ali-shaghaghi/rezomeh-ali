<?php

namespace Modules\Products\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\Products\Models\Product;
use Modules\Products\Models\ProductCategory;

/**
 * مدیریت دسته‌بندی‌های محصولات — الگوی PortfolioIndex:
 * گرید کارت‌ها + جستجوی زنده + مودال ایجاد/ویرایش (real-time، بدون رفرش صفحه)
 */
class CategoriesManager extends Component
{
    use WithPagination;

    protected function viewPath(): string
    {
        return 'products::livewire.admin.categories-manager';
    }

    // فیلترها
    public string $search = '';

    public string $status = ''; // '' | active | inactive

    // فرم مودال
    public bool $modalOpen = false;

    public string $name = '';

    public string $slug = '';

    public string $description = '';

    public $parent_id = '';

    public bool $is_active = true;

    public bool $editing = false;

    public ?int $editingId = null;

    // مودال حذف
    public bool $confirmingDelete = false;

    public $deleteId = null;

    public string $deleteName = '';

    // نوتیفیکیشن
    public bool $successToast = false;

    public string $successMessage = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
    ];

    protected $rules = [
        'name' => ['required', 'string', 'min:2', 'max:150'],
        'slug' => ['nullable', 'string', 'max:150'],
        'description' => ['nullable', 'string', 'max:1000'],
        'parent_id' => ['nullable', 'exists:product_categories,id'],
        'is_active' => ['boolean'],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = ProductCategory::query()
            ->withCount(['products'])
            ->orderBy('order')
            ->orderBy('id');

        if ($this->search !== '') {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if ($this->status === 'active') {
            $query->where('is_active', true);
        } elseif ($this->status === 'inactive') {
            $query->where('is_active', false);
        }

        $categories = $query->paginate(12);

        // دسته‌های والد برای select فرم (خارج از صفحه‌بندی)
        $parentOptions = ProductCategory::orderBy('name')->get()
            ->reject(fn ($c) => $c->id === $this->editingId);

        return view('products::livewire.admin.categories-manager', [
            'categories' => $categories,
            'parentOptions' => $parentOptions,
            'stats' => [
                'total' => ProductCategory::count(),
                'active' => ProductCategory::where('is_active', true)->count(),
            ],
        ]);
    }

    public function openCreateModal(): void
    {
        $this->authorize('create', Product::class);

        $this->resetForm();
        $this->editing = false;
        $this->modalOpen = true;
    }

    public function openEditModal(int $id): void
    {
        $this->authorize('update', Product::class);

        $category = ProductCategory::findOrFail($id);

        $this->editing = true;
        $this->editingId = $id;
        $this->name = $category->name;
        $this->slug = $category->slug;
        $this->description = $category->description ?? '';
        $this->parent_id = $category->parent_id;
        $this->is_active = (bool) $category->is_active;
        $this->modalOpen = true;
    }

    /**
     * نام جایگزین openEditModal (سازگاری با تست‌ها و فراخوانی‌های قدیمی)
     */
    public function editCategory(int $id): void
    {
        $this->openEditModal($id);
    }

    public function closeModal(): void
    {
        $this->modalOpen = false;
        $this->resetForm();
    }

    public function saveCategory(): void
    {
        $this->authorize($this->editing ? 'update' : 'create', Product::class);

        $validated = $this->validate();

        ProductCategory::updateOrCreate(
            ['id' => $this->editingId],
            [
                'name' => $validated['name'],
                'slug' => $validated['slug'] ?: null,
                'description' => $validated['description'] ?: null,
                'parent_id' => $validated['parent_id'] ?: null,
                'is_active' => $validated['is_active'],
            ]
        );

        $this->modalOpen = false;
        $this->notifySuccess($this->editing ? 'دسته‌بندی به‌روزرسانی شد.' : 'دسته‌بندی ایجاد شد.');
        $this->resetForm();
    }

    public function toggleActive(int $id): void
    {
        $this->authorize('update', Product::class);

        $category = ProductCategory::findOrFail($id);
        $category->update(['is_active' => ! $category->is_active]);

        $this->notifySuccess('دسته‌بندی «'.$category->name.'» '.($category->is_active ? 'فعال' : 'غیرفعال').' شد.');
    }

    public function confirmDelete($id): void
    {
        $category = ProductCategory::findOrFail($id);
        $this->deleteId = $id;
        $this->deleteName = $category->name;
        $this->confirmingDelete = true;
    }

    public function deleteCategory(): void
    {
        $this->authorize('delete', Product::class);

        $category = ProductCategory::findOrFail($this->deleteId);
        $name = $category->name;
        $category->delete();

        $this->confirmingDelete = false;
        $this->deleteId = null;
        $this->deleteName = '';
        $this->notifySuccess('دسته‌بندی «'.$name.'» حذف شد.');
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'status']);
        $this->resetPage();
    }

    public function resetForm(): void
    {
        $this->reset(['name', 'slug', 'description', 'parent_id', 'is_active', 'editing', 'editingId']);
        $this->is_active = true;
        $this->resetErrorBag();
    }

    private function notifySuccess(string $message): void
    {
        $this->successMessage = $message;
        $this->successToast = true;
    }
}

