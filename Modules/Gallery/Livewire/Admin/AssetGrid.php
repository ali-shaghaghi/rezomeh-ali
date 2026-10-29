<?php

declare(strict_types=1);

namespace Modules\Gallery\Livewire\Admin;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Modules\Gallery\Models\Asset;
use Modules\Gallery\Services\GalleryService;

/**
 * مدیریت گالری تصاویر در پنل ادمین:
 * آپلود چندتایی، ویرایش اطلاعات سئو (alt/title/description)، انتشار/لغو انتشار، حذف.
 */
#[Layout('adminpanel::layouts.master')]
#[Title('گالری تصاویر')]
final class AssetGrid extends Component
{
    use WithFileUploads;
    use WithPagination;

    // فیلترها
    public string $search = '';

    public string $filterType = 'all';

    public bool $showUnlisted = false;

    // آپلود
    /** @var array<int, \Livewire\Features\SupportFileUploads\TemporaryUploadedFile> */
    public array $uploads = [];

    public bool $uploading = false;

    // ویرایش سئو
    public ?int $editingId = null;

    public string $editAlt = '';

    public string $editTitle = '';

    public string $editDescription = '';

    public bool $editPublished = true;

    // حذف
    public ?int $confirmDeleteId = null;

    // پیام
    public string $message = '';

    public bool $successToast = false;

    protected $listeners = ['gallery:refresh' => '$refresh'];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterType(): void
    {
        $this->resetPage();
    }

    public function updatedShowUnlisted(): void
    {
        $this->resetPage();
    }

    // ===== آپلود =====

    public function updatedUploads(): void
    {
        $this->upload();
    }

    public function upload(): void
    {
        $files = array_values(array_filter($this->uploads, static fn ($f) => $f !== null));

        if ($files === []) {
            return;
        }

        $this->validate([
            'uploads' => 'array|max:' . GalleryService::MAX_FILES_PER_UPLOAD,
            'uploads.*' => 'file|max:' . (int) (GalleryService::MAX_BYTES_PER_IMAGE / 1024),
        ], [
            'uploads.*.max' => 'حجم هر فایل نباید بیشتر از ۸ مگابایت باشد.',
        ]);

        $this->uploading = true;

        $added = 0;

        try {
            $service = app(GalleryService::class);

            foreach ($files as $file) {
                $service->createFromUploadedFile($file, [
                    'alt' => $this->search ?: null,
                ]);

                $added++;
            }
        } finally {
            $this->uploads = [];
            $this->uploading = false;
        }

        if ($added > 0) {
            $this->flash($added === 1 ? 'تصویر آپلود شد.' : "{$added} تصویر آپلود شد.");
        }
    }

    // ===== ویرایش سئو =====

    public function startEdit(int $id): void
    {
        $asset = Asset::findOrFail($id);

        $this->editingId = $asset->id;
        $this->editAlt = $asset->alt ?? '';
        $this->editTitle = $asset->meta_title ?? '';
        $this->editDescription = $asset->meta_description ?? '';
        $this->editPublished = $asset->public_hash !== null;
    }

    public function cancelEdit(): void
    {
        $this->reset(['editingId', 'editAlt', 'editTitle', 'editDescription', 'editPublished']);
    }

    public function saveEdit(): void
    {
        $this->validate([
            'editAlt' => 'nullable|string|max:255',
            'editTitle' => 'nullable|string|max:255',
            'editDescription' => 'nullable|string|max:1000',
        ]);

        $asset = Asset::findOrFail($this->editingId);

        $service = app(GalleryService::class);

        $asset->update([
            'alt' => $this->editAlt ?: null,
            'meta_title' => $this->editTitle ?: null,
            'meta_description' => $this->editDescription ?: null,
            'public_hash' => $this->editPublished
                ? ($asset->public_hash ?? $service->hash())
                : null,
        ]);

        $this->cancelEdit();
        $this->flash('اطلاعات سئوی تصویر ذخیره شد.');
    }

    // ===== انتشار / لغو انتشار =====

    public function togglePublish(int $id): void
    {
        $asset = Asset::findOrFail($id);

        $asset->update([
            'public_hash' => $asset->public_hash ? null : app(GalleryService::class)->hash(),
        ]);

        $this->flash($asset->public_hash ? 'تصویر منتشر شد — لینک هش‌شده فعال است.' : 'انتشار تصویر لغو شد.');
    }

    // ===== حذف =====

    public function confirmDelete(int $id): void
    {
        Asset::findOrFail($id);
        $this->confirmDeleteId = $id;
    }

    public function cancelDelete(): void
    {
        $this->confirmDeleteId = null;
    }

    public function destroyAsset(): void
    {
        $asset = Asset::find($this->confirmDeleteId);

        if ($asset === null) {
            $this->cancelDelete();

            return;
        }

        app(GalleryService::class)->deleteAsset($asset);

        $this->cancelDelete();
        $this->flash('تصویر حذف شد.');
    }

    // ===== پیام =====

    private function flash(string $message): void
    {
        $this->message = $message;
        $this->successToast = true;
        $this->dispatch('gallery-notify', message: $message);
    }

    public function render()
    {
        $assets = app(GalleryService::class)->assets([
            'search' => $this->search,
            'type' => $this->filterType,
            'sort' => 'newest',
            'published_only' => ! $this->showUnlisted,
        ], perPage: 16);

        return view('gallery::livewire.admin.asset-grid', [
            'assets' => $assets,
            'total' => Asset::count(),
        ]);
    }
}
