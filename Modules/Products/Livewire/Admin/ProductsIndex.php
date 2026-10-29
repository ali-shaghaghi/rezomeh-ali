<?php

namespace Modules\Products\Livewire\Admin;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Modules\Products\Enums\ProductType;
use Modules\Products\Models\Product;
use Modules\Products\Models\ProductCategory;
use Modules\Products\Models\ProductImage;
use Modules\Products\Services\ProductService;

class ProductsIndex extends Component
{
    use WithFileUploads;
    use WithPagination;

    // فیلترها
    public string $search = '';

    public string $type = '';

    public string $category = '';

    public string $published = '';

    // حالت صفحه: لیست | ایجاد | ویرایش
    public bool $showForm = false;

    public bool $editing = false;

    public ?Product $editingProduct = null;

    // فیلدهای فرم محصول
    public string $name = '';

    public string $slug = '';

    public string $short_description = '';

    public string $description = '';

    public $price = '';

    public $sale_price = '';

    public string $type_field = ProductType::Template->value;

    public $category_id = '';

    public string $file_path = '';

    public string $demo_url = '';

    public string $version = '1.0.0';

    public bool $is_published = true;

    public bool $show_in_resume = false;

    // فیلدهای انگلیسی (دو‌زبانه)
    public string $name_en = '';

    public string $short_description_en = '';

    public string $description_en = '';

    // تصاویر — فقط از گالری انتخاب می‌شوند (آپلود مستقیم داخل فرم محصول حذف شد)
    public string $coverSelected = '';

    /** @var array<int, string> uploader_path عکس‌های تازه انتخاب‌شده از گالری */
    public array $gallerySelected = [];

    // انتخاب‌گر گالری (مودال روی فرم محصول)
    public bool $pickerOpen = false;

    public string $pickerMode = 'cover'; // cover | gallery

    // گالری ذخیره‌شده محصول در حال ویرایش (id => path)
    public array $existingImages = [];

    // ===== تصاویر درون‌متنی توضیحات (آپلود فوری و درج در محل مکان‌نما) =====
    // آپلودهای توضیحات فارسی و انگلیسی از هم جدا — رویداد هم جدا تا هر نوار
    // ابزار فقط متن خودش را ویرایش کند
    public $inlineUpload = null;

    public $inlineUploadEn = null;

    // سرویس گالری برای تبدیل فایل‌های آپلودشده به آرشیو عمومی
    protected \Modules\Gallery\Services\GalleryService $galleryService;

    // مودال حذف
    public bool $confirmingDelete = false;

    public $deleteId = null;

    // نوتیفیکیشن
    public bool $successToast = false;

    public string $successMessage = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'type' => ['except' => ''],
        'category' => ['except' => ''],
        'published' => ['except' => ''],
    ];

    public function boot(\Modules\Gallery\Services\GalleryService $service): void
    {
        $this->galleryService = $service;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    // ===== تصاویر درون‌متنی — آپلود و اعلام آمادگی برای درج =====

    public function updatedInlineUpload(): void
    {
        $this->storeInlineUploads($this->inlineUpload, 'inline-media-uploaded');
        $this->inlineUpload = null;
    }

    public function updatedInlineUploadEn(): void
    {
        $this->storeInlineUploads($this->inlineUploadEn, 'inline-media-uploaded-en');
        $this->inlineUploadEn = null;
    }

    /**
     * ذخیرهٔ دائمی فایل‌های موقت آپلودشده در Gallery و ارسال رویداد
     * به نوار ابزار (شامل آدرس‌های عمومی هش‌شده) تا توکن
     * [[media|لایوت|آدرس]] در محل مکان‌نما درج شود.
     */
    private function storeInlineUploads($files, string $event): void
    {
        $files = array_values(array_filter((array) $files, static fn ($f) => $f !== null));

        if ($files === []) {
            return;
        }

        $validator = validator(
            ['files' => $files],
            ['files' => 'array|max:6', 'files.*' => 'image|max:4096']
        );

        if ($validator->fails()) {
            return;
        }

        $urls = [];
        foreach ($files as $file) {
            $asset = $this->galleryService->createFromUploadedFile($file);
            $urls[] = $asset->srcAttribute();
        }

        $this->dispatch($event, urls: $urls);
    }

    public function updatingType(): void
    {
        $this->resetPage();
    }

    public function updatingCategory(): void
    {
        $this->resetPage();
    }

    public function updatingPublished(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $service = app(ProductService::class);

        $products = $service->adminList([
            'search' => $this->search,
            'type' => $this->type,
            'category' => $this->category,
            'published' => $this->published,
        ]);

        return view('products::livewire.admin.products-index', [
            'products' => $products,
            'stats' => $service->stats(),
            'categories' => ProductCategory::orderBy('order')->get(),
            'types' => ProductType::cases(),
            // تصاویر منتشرشدهٔ گالری — برای درج در ویرایشگر متن
            'galleryAssets' => $this->galleryService->publishedAssets(),
        ]);
    }

    // ===== فرم ایجاد/ویرایش =====

    public function openCreateForm(): void
    {
        $this->authorize('create', Product::class);

        $this->resetErrorBag();
        $this->editing = false;
        $this->editingProduct = null;

        $this->fill([
            'name' => '',
            'slug' => '',
            'short_description' => '',
            'description' => '',
            'price' => '',
            'sale_price' => '',
            'type_field' => ProductType::Template->value,
            'category_id' => '',
            'file_path' => '',
            'demo_url' => '',
            'version' => '1.0.0',
            'is_published' => true,
            'show_in_resume' => false,
            'name_en' => '',
            'short_description_en' => '',
            'description_en' => '',
        ]);

        $this->coverSelected = '';
        $this->gallerySelected = [];
        $this->pickerOpen = false;
        $this->existingImages = [];

        $this->showForm = true;
    }

    public function openEditForm(int $id): void
    {
        $product = Product::findOrFail($id);
        $this->authorize('update', $product);

        $this->resetErrorBag();
        $this->editing = true;
        $this->editingProduct = $product;

        $this->fill([
            'name' => $product->name,
            'slug' => $product->slug,
            'short_description' => $product->short_description ?? '',
            'description' => $product->description ?? '',
            'price' => $product->price,
            'sale_price' => $product->sale_price,
            'type_field' => $product->type,
            'category_id' => $product->category_id,
            'file_path' => $product->file_path ?? '',
            'demo_url' => $product->demo_url ?? '',
            'version' => $product->version,
            'is_published' => (bool) $product->is_published,
            'show_in_resume' => (bool) $product->show_in_resume,
            'name_en' => $product->name_en ?? '',
            'short_description_en' => $product->short_description_en ?? '',
            'description_en' => $product->description_en ?? '',
        ]);

        $this->coverSelected = '';
        $this->gallerySelected = [];
        $this->pickerOpen = false;
        $this->existingImages = $product->images()
            ->orderBy('order')->orderBy('id')
            ->pluck('path', 'id')->all();

        $this->showForm = true;
    }

    /**
     * انتخاب تصویر از گالری — جایگزین آپلود مستقیم در فرم محصول
     */
    public function openPicker(string $mode): void
    {
        $this->pickerMode = $mode === 'gallery' ? 'gallery' : 'cover';
        $this->pickerOpen = true;
    }

    public function closePicker(): void
    {
        $this->pickerOpen = false;
    }

    public function pickAsset(string $path): void
    {
        if ($this->pickerMode === 'cover') {
            // انتخاب مجدد = برداشتن انتخاب، سپس بستن انتخاب‌گر
            $this->coverSelected = $this->coverSelected === $path ? '' : $path;
            $this->pickerOpen = false;

            return;
        }

        if (in_array($path, $this->gallerySelected, true)) {
            $this->gallerySelected = array_values(array_diff($this->gallerySelected, [$path]));
        } elseif (count($this->gallerySelected) < 10) {
            $this->gallerySelected[] = $path;
        }
    }

    public function removeSelectedGalleryImage(string $path): void
    {
        $this->gallerySelected = array_values(array_diff($this->gallerySelected, [$path]));
    }

    public function clearCoverSelected(): void
    {
        $this->coverSelected = '';
    }

    /**
     * حذف یکی از عکس‌های گالری ذخیره‌شده
     */
    public function removeExistingImage(int $imageId): void
    {
        $this->authorize('update', $this->editingProduct ?? Product::class);

        $image = ProductImage::find($imageId);
        if ($image && $this->editingProduct && $image->product_id === $this->editingProduct->id) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($image->path);
            $image->delete();
            unset($this->existingImages[$imageId]);
        }
    }

    public function saveProduct(): void
    {
        $this->authorize($this->editing ? 'update' : 'create', $this->editingProduct ?? Product::class);

        $validated = $this->validate([
            'name' => ['required', 'string', 'min:3', 'max:200'],
            'slug' => ['nullable', 'string', 'max:200', 'unique:products,slug,'.($this->editingProduct?->id ?? 'NULL')],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:20000'],
            'price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'type_field' => ['required', 'string'],
            'category_id' => ['nullable', 'exists:product_categories,id'],
            'file_path' => ['nullable', 'string', 'max:500'],
            'demo_url' => ['nullable', 'string', 'max:500'],
            'version' => ['nullable', 'string', 'max:32'],
            'is_published' => ['boolean'],
            'show_in_resume' => ['boolean'],
            'name_en' => ['nullable', 'string', 'max:200'],
            'short_description_en' => ['nullable', 'string', 'max:500'],
            'description_en' => ['nullable', 'string', 'max:20000'],
            'coverSelected' => ['nullable', 'string', 'max:500', 'exists:gallery_assets,uploader_path'],
            'gallerySelected' => ['nullable', 'array', 'max:10'],
            'gallerySelected.*' => ['string', 'max:500', 'exists:gallery_assets,uploader_path'],
        ]);

        $data = [
            'name' => $validated['name'],
            'slug' => $validated['slug'] ?: null,
            'short_description' => $validated['short_description'] ?: null,
            'description' => $validated['description'] ?: null,
            'price' => $validated['price'],
            'sale_price' => $validated['sale_price'] ?: null,
            'type' => $validated['type_field'],
            'category_id' => $validated['category_id'] ?: null,
            'file_path' => $validated['file_path'] ?: null,
            'demo_url' => $validated['demo_url'] ?: null,
            'version' => $validated['version'] ?: '1.0.0',
            'is_published' => $validated['is_published'],
            'show_in_resume' => $validated['show_in_resume'],
            'name_en' => $validated['name_en'] ?: null,
            'short_description_en' => $validated['short_description_en'] ?: null,
            'description_en' => $validated['description_en'] ?: null,
        ];

        // عکس شاخص — فقط از گالری (فایل قبلی حذف نمی‌شود؛ ممکن است در گالری مشترک باشد)
        if ($this->coverSelected !== '') {
            $data['cover_image'] = $this->coverSelected;
        }

        $product = $this->editing
            ? app(ProductService::class)->save($this->editingProduct, $data)
            : app(ProductService::class)->save(new Product, $data);

        // ذخیره عکس‌های انتخاب‌شده از گالری (پشت‌سرهم — هر کدام یک ردیف product_images)
        if ($this->gallerySelected !== []) {
            $order = (int) $product->images()->max('order');
            foreach ($this->gallerySelected as $path) {
                $product->images()->create([
                    'path' => $path,
                    'alt' => $product->name,
                    'order' => ++$order,
                ]);
            }
        }

        $this->coverSelected = '';
        $this->gallerySelected = [];
        $this->pickerOpen = false;
        $this->showForm = false;
        $this->notifySuccess($this->editing
            ? 'محصول «'.$product->name.'» به‌روزرسانی شد.'
            : 'محصول «'.$product->name.'» ایجاد شد.');
    }

    // ===== عملیات سریع =====

    public function togglePublished(int $id): void
    {
        $product = Product::findOrFail($id);
        $this->authorize('update', $product);

        $product->update(['is_published' => ! $product->is_published]);

        $this->notifySuccess('محصول «'.$product->name.'» '.($product->is_published ? 'منتشر' : 'از انتشار خارج').' شد.');
    }

    public function toggleResume(int $id): void
    {
        $product = Product::findOrFail($id);
        $this->authorize('update', $product);

        $product->update(['show_in_resume' => ! $product->show_in_resume]);

        $this->notifySuccess('محصول «'.$product->name.'» '.($product->show_in_resume ? 'به رزومه اضافه' : 'از رزومه حذف').' شد.');
    }

    // ===== حذف =====

    public function confirmDelete($id): void
    {
        $this->deleteId = $id;
        $this->confirmingDelete = true;
    }

    public function deleteProduct(): void
    {
        $product = Product::findOrFail($this->deleteId);
        $this->authorize('delete', $product);

        $name = $product->name;
        app(ProductService::class)->delete($product);

        $this->confirmingDelete = false;
        $this->deleteId = null;
        $this->notifySuccess('محصول «'.$name.'» حذف شد.');
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'type', 'category', 'published']);
        $this->resetPage();
    }

    private function notifySuccess(string $message): void
    {
        $this->successMessage = $message;
        $this->successToast = true;
    }
}
