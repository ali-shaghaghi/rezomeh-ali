<?php

namespace Modules\Resume\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\Core\Models\Category;
use Modules\Resume\Models\Project;
use Modules\Resume\Services\PortfolioService;

class PortfolioIndex extends Component
{
    use WithPagination;

    // فیلترها
    public string $search = '';

    public string $status = '';

    public string $categoryId = '';

    public string $featured = '';

    // مودال حذف
    public bool $confirmingDelete = false;

    public $deleteId = null;

    public string $deleteName = '';

    // مودال دمو
    public bool $showDemo = false;

    public string $demoUrl = '';

    public string $demoTitle = '';

    // نوتیفیکیشن موفقیت
    public bool $successToast = false;

    public string $successMessage = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'categoryId' => ['except' => ''],
        'featured' => ['except' => ''],
    ];

    protected $listeners = ['project-deleted' => '$refresh'];

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

    public function updatingFeatured(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $service = app(PortfolioService::class);

        $projects = $service->list([
            'search' => $this->search,
            'status' => $this->status,
            'category_id' => $this->categoryId,
            'featured' => $this->featured,
        ]);

        return view('resume::livewire.admin.portfolio-index', [
            'projects' => $projects,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    // نمایش مودال تایید حذف
    public function confirmDelete($id): void
    {
        $this->deleteId = $id;
        $this->confirmingDelete = true;
    }

    // حذف نهایی پروژه
    public function deleteProject(): void
    {
        $project = Project::findOrFail($this->deleteId);

        $this->authorize('delete', $project);

        app(PortfolioService::class)->delete($project);

        $this->confirmingDelete = false;
        $this->deleteId = null;
        $this->notifySuccess('پروژه «'.$project->title.'» با موفقیت حذف شد.');
    }

    // نمایش دمو در مودال
    public function openDemo($id): void
    {
        $project = Project::findOrFail($id);
        $link = app(PortfolioService::class)->resolveDemoLink($project);

        if (! $link) {
            $this->notifySuccess('برای این پروژه دمویی ثبت نشده است.');

            return;
        }

        $this->demoUrl = $link;
        $this->demoTitle = $project->title;
        $this->showDemo = true;
    }

    // تغییر وضعیت انتشار (ریاکتیو)
    public function toggleStatus($id): void
    {
        $project = Project::findOrFail($id);
        $this->authorize('update', $project);

        app(PortfolioService::class)->toggleStatus($project);
        $this->notifySuccess($project->status === 'published'
            ? 'پروژه «'.$project->title.'» منتشر شد.'
            : 'پروژه «'.$project->title.'» از حالت انتشار خارج شد.');
    }

    // تغییر وضعیت ویژه (ریاکتیو)
    public function toggleFeatured($id): void
    {
        $project = Project::findOrFail($id);
        $this->authorize('update', $project);

        app(PortfolioService::class)->toggleFeatured($project);
        $this->notifySuccess($project->featured
            ? 'پروژه «'.$project->title.'» ویژه شد.'
            : 'پروژه «'.$project->title.'» از حالت ویژه خارج شد.');
    }

    // جابجایی ترتیب نمایش
    public function move($id, string $direction): void
    {
        $project = Project::findOrFail($id);
        $this->authorize('update', $project);

        app(PortfolioService::class)->move($project, $direction);
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'status', 'categoryId', 'featured']);
        $this->resetPage();
    }

    // نمایش نوتیفیکیشن موفقیت
    private function notifySuccess(string $message): void
    {
        $this->successMessage = $message;
        $this->successToast = true;
        $this->dispatch('notify'); // شروع تایمر مخفی‌سازی
    }
}
