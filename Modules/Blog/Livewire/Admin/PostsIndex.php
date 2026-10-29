<?php

namespace Modules\Blog\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\Blog\Services\BlogService;
use Modules\Core\Models\BlogPost;
use Modules\Core\Models\Category;

class PostsIndex extends Component
{
    use WithPagination;

    // فیلترها
    public string $search = '';

    public string $status = '';

    public string $categoryId = '';

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
        'categoryId' => ['except' => ''],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingCategoryId(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $service = app(BlogService::class);

        $posts = $service->list([
            'search' => $this->search,
            'status' => $this->status,
            'category_id' => $this->categoryId,
        ]);

        return view('blog::livewire.admin.posts-index', [
            'posts' => $posts,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    // نمایش مودال تایید حذف
    public function confirmDelete($id): void
    {
        $this->deleteId = $id;
        $this->confirmingDelete = true;
    }

    // حذف نهایی مقاله
    public function deletePost(): void
    {
        $post = BlogPost::findOrFail($this->deleteId);

        $this->authorize('delete', $post);

        app(BlogService::class)->delete($post);

        $this->confirmingDelete = false;
        $this->deleteId = null;
        $this->notifySuccess('مقاله «'.$post->title.'» با موفقیت حذف شد.');
    }

    // تغییر وضعیت انتشار (ریاکتیو)
    public function toggleStatus($id): void
    {
        $post = BlogPost::findOrFail($id);
        $this->authorize('update', $post);

        app(BlogService::class)->toggleStatus($post);
        $this->notifySuccess($post->status === 'published'
            ? 'مقاله «'.$post->title.'» منتشر شد.'
            : 'مقاله «'.$post->title.'» از حالت انتشار خارج شد.');
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'status', 'categoryId']);
        $this->resetPage();
    }

    // نمایش نوتیفیکیشن موفقیت
    private function notifySuccess(string $message): void
    {
        $this->successMessage = $message;
        $this->successToast = true;
        $this->dispatch('notify');
    }
}
