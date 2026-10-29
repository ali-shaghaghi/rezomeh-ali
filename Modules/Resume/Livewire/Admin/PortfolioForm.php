<?php

namespace Modules\Resume\Livewire\Admin;

use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;
use Modules\Core\Models\Category;
use Modules\Products\Models\Product;
use Modules\Resume\Models\Project;
use Modules\Resume\Models\Skill;
use Modules\Resume\Models\Technology;
use Modules\Resume\Services\PortfolioService;

class PortfolioForm extends Component
{
    use WithFileUploads;

    public ?Project $project = null;

    public bool $creating = false;

    // فیلدهای فرم
    public string $title = '';

    public string $slug = '';

    public string $short_description = '';

    public string $description = '';

    // نسخه انگلیسی — عنوان، اسلاگ، توضیحات و سئوی انگلیسی
    public string $title_en = '';

    public string $slug_en = '';

    public string $short_description_en = '';

    public string $description_en = '';

    public string $seo_title_en = '';

    public string $seo_description_en = '';

    // کاور اصلی: آپلود مستقیم یا انتخاب از گالری (مسیر gallery/{hash})
    public $cover_image = null;

    public ?string $coverSrc = null;

    // کاور نسخه انگلیسی — به جای «تصویر کوچک» قدیمی (#درخواست کاربر)
    public $cover_image_en = null;

    public ?string $coverEnSrc = null;

    // محصول مرتبط — مقصد دکمهٔ «جزئیات پروژه»
    public $relatedProductId = '';

    public $categoryId = '';

    public string $client_name = '';

    public $project_date = null;

    public string $github_url = '';

    public string $demo_url = '';

    public string $demo_type = 'link';

    public string $video_url = '';

    public string $status = 'draft';

    public bool $featured = false;

    public int $order = 0;

    public string $seo_title = '';

    public string $seo_description = '';

    public array $technology_ids = [];

    public array $skill_ids = [];

    // نمایش فایل‌های موجود
    public ?string $existingCover = null;

    public ?string $existingCoverEn = null;

    // وضعیت ذخیره
    public bool $saving = false;

    protected function rules(): array
    {
        $slugUnique = $this->project
            ? 'unique:projects,slug,'.$this->project->id
            : 'unique:projects,slug';

        $slugEnUnique = $this->project
            ? 'unique:projects,slug_en,'.$this->project->id
            : 'unique:projects,slug_en';

        return [
            'title' => 'required|string|max:255',
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', $slugUnique],
            'short_description' => 'nullable|string|max:500',
            'description' => 'required|string',
            'title_en' => 'nullable|string|max:255',
            'slug_en' => ['nullable', 'string', 'max:255', 'alpha_dash', $slugEnUnique],
            'short_description_en' => 'nullable|string|max:500',
            'description_en' => 'nullable|string',
            'seo_title_en' => 'nullable|string|max:255',
            'seo_description_en' => 'nullable|string|max:500',
            'cover_image' => 'nullable|image|max:4096',
            'coverSrc' => ['nullable', 'string', 'regex:#^gallery/[A-Za-z0-9_\-]{16,128}$#'],
            'cover_image_en' => 'nullable|image|max:4096',
            'coverEnSrc' => ['nullable', 'string', 'regex:#^gallery/[A-Za-z0-9_\-]{16,128}$#'],
            'relatedProductId' => 'nullable|exists:products,id',
            'categoryId' => 'nullable|exists:categories,id',
            'client_name' => 'nullable|string|max:255',
            'project_date' => 'nullable|date',
            'github_url' => 'nullable|url|max:255',
            'demo_url' => 'nullable|url|max:255',
            'demo_type' => 'required|in:link,external_url,subdomain',
            'video_url' => 'nullable|url|max:255',
            'status' => 'required|in:draft,published,archived',
            'featured' => 'boolean',
            'order' => 'integer|min:0|max:9999',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:500',
            'technology_ids' => 'array',
            'technology_ids.*' => 'exists:technologies,id',
            'skill_ids' => 'array',
            'skill_ids.*' => 'exists:skills,id',
        ];
    }

    public function mount(?Project $project = null): void
    {
        if ($project && $project->exists) {
            $this->project = $project;
            $this->fillFromProject();
        } else {
            $this->creating = true;
            $this->project = null;
        }
    }

    // پر کردن فیلدها از پروژه موجود
    private function fillFromProject(): void
    {
        $p = $this->project;
        $this->title = $p->title;
        $this->slug = $p->slug;
        $this->short_description = $p->short_description ?? '';
        $this->description = $p->description ?? '';
        $this->title_en = $p->title_en ?? '';
        $this->slug_en = $p->slug_en ?? '';
        $this->short_description_en = $p->short_description_en ?? '';
        $this->description_en = $p->description_en ?? '';
        $this->seo_title_en = $p->seo_title_en ?? '';
        $this->seo_description_en = $p->seo_description_en ?? '';
        $this->categoryId = $p->category_id ?? '';
        $this->client_name = $p->client_name ?? '';
        $this->project_date = $p->project_date?->format('Y-m-d');
        $this->github_url = $p->github_url ?? '';
        $this->demo_url = $p->demo_url ?? '';
        $this->demo_type = $p->demo_type ?? 'link';
        $this->video_url = $p->video_url ?? '';
        $this->status = $p->status ?? 'draft';
        $this->featured = (bool) $p->featured;
        $this->order = (int) $p->order;
        $this->seo_title = $p->seo_title ?? '';
        $this->seo_description = $p->seo_description ?? '';
        $this->technology_ids = $p->technologies->pluck('id')->map(fn ($id) => (string) $id)->toArray();
        $this->skill_ids = $p->skills->pluck('id')->map(fn ($id) => (string) $id)->toArray();
        $this->relatedProductId = $p->related_product_id ?? '';

        // کاورها: مسیر گالری (gallery/{hash}) یا فایل آپلودی قدیمی (storage)
        [$this->coverSrc, $this->existingCover] = $this->splitImageSource($p->cover_image);
        [$this->coverEnSrc, $this->existingCoverEn] = $this->splitImageSource($p->cover_image_en);
    }

    /** جداسازی مقدار ذخیره‌شده به مسیر گالری یا مسیر آپلودی قدیمی */
    private function splitImageSource(?string $value): array
    {
        if ($value && str_starts_with(ltrim($value, '/'), 'gallery/')) {
            return [ltrim($value, '/'), null];
        }

        return [null, $value];
    }

    // تولید خودکار slug از عنوان
    public function updatedTitle(string $value): void
    {
        // فقط زمانی که کاربر slug را دستی تغییر نداده باشد
        if ($this->creating || empty($this->slug)) {
            $this->slug = Str::slug($value) ?: Str::slug(Str::ascii($value));
            // برای عناوین فارسی، از عنوان اصلی استفاده می‌شود
            if (empty($this->slug)) {
                $this->slug = 'project-'.Str::random(6);
            }
        }
    }

    // تولید خودکار اسلاگ انگلیسی از عنوان انگلیسی
    public function updatedTitleEn(string $value): void
    {
        if ($this->creating || empty($this->slug_en)) {
            $this->slug_en = Str::slug($value);
        }
    }

    /** انتخاب کاور اصلی از گالری (ریل‌تایم) — فقط مسیر نسبی gallery/{hash} ذخیره می‌شود */
    public function pickCoverImage(string $src): void
    {
        $path = $this->normalizeGalleryPath($src);
        if ($path === null) {
            return;
        }

        $this->coverSrc = $path;
        $this->cover_image = null;
        $this->existingCover = null;
    }

    /** انتخاب کاور نسخه انگلیسی از گالری (ریل‌تایم) */
    public function pickCoverEnImage(string $src): void
    {
        $path = $this->normalizeGalleryPath($src);
        if ($path === null) {
            return;
        }

        $this->coverEnSrc = $path;
        $this->cover_image_en = null;
        $this->existingCoverEn = null;
    }

    private function normalizeGalleryPath(string $src): ?string
    {
        $path = ltrim((string) preg_replace('#^https?://[^/]+#', '', $src), '/');

        return preg_match('#^gallery/[A-Za-z0-9_\-]{16,128}$#', $path) ? $path : null;
    }

    // حذف کاور موجود
    public function removeCover(): void
    {
        $this->existingCover = null;
        $this->coverSrc = null;
        $this->cover_image = null;
    }

    // حذف کاور انگلیسی موجود
    public function removeCoverEn(): void
    {
        $this->existingCoverEn = null;
        $this->coverEnSrc = null;
        $this->cover_image_en = null;
    }

    public function save(): void
    {
        $this->authorize($this->project ? 'update' : 'create', $this->project ?? Project::class);

        $validated = $this->validate();

        $this->saving = true;

        try {
            $data = [
                'title' => $validated['title'],
                'slug' => $validated['slug'],
                'short_description' => $validated['short_description'] ?: null,
                'description' => $validated['description'],
                'title_en' => $validated['title_en'] ?: null,
                'slug_en' => $validated['slug_en'] ?: null,
                'short_description_en' => $validated['short_description_en'] ?: null,
                'description_en' => $validated['description_en'] ?: null,
                'seo_title_en' => $validated['seo_title_en'] ?: null,
                'seo_description_en' => $validated['seo_description_en'] ?: null,
                'category_id' => $validated['categoryId'] ?: null,
                'related_product_id' => $validated['relatedProductId'] ?: null,
                'client_name' => $validated['client_name'] ?: null,
                'project_date' => $validated['project_date'] ?: null,
                'github_url' => $validated['github_url'] ?: null,
                'demo_url' => $validated['demo_url'] ?: null,
                'demo_type' => $validated['demo_type'],
                'video_url' => $validated['video_url'] ?: null,
                'status' => $validated['status'],
                'featured' => $validated['featured'],
                'order' => $validated['order'],
                'seo_title' => $validated['seo_title'] ?: null,
                'seo_description' => $validated['seo_description'] ?: null,
                'technologies' => array_map('intval', $validated['technology_ids']),
                'skills' => array_map('intval', $validated['skill_ids']),
            ];

            // کاور اصلی: آپلود مستقیم ← انتخاب از گالری ← مسیر قبلی؛ اگر حذف شده باشد null
            if ($this->cover_image) {
                $data['cover_image'] = $this->cover_image->store('projects/covers', 'public');
            } elseif ($this->coverSrc) {
                $data['cover_image'] = $this->coverSrc;
            } elseif ($this->existingCover) {
                $data['cover_image'] = $this->existingCover;
            } else {
                $data['cover_image'] = null;
            }

            // کاور نسخه انگلیسی — همان اولویت‌بندی
            if ($this->cover_image_en) {
                $data['cover_image_en'] = $this->cover_image_en->store('projects/covers', 'public');
            } elseif ($this->coverEnSrc) {
                $data['cover_image_en'] = $this->coverEnSrc;
            } elseif ($this->existingCoverEn) {
                $data['cover_image_en'] = $this->existingCoverEn;
            } else {
                $data['cover_image_en'] = null;
            }

            $project = $this->project ?? new Project;
            app(PortfolioService::class)->save($project, $data);

            session()->flash('success', $this->creating
                ? 'پروژه «'.$project->title.'» با موفقیت ایجاد شد.'
                : 'پروژه «'.$project->title.'» با موفقیت به‌روزرسانی شد.');

            $this->redirect(route('admin.resume.portfolio.show', $project), navigate: true);
        } finally {
            $this->saving = false;
        }
    }

    public function render()
    {
        return view('resume::livewire.admin.portfolio-form', [
            'categories' => Category::orderBy('name')->get(),
            'technologies' => Technology::orderBy('name')->get(),
            'skills' => Skill::active()->ordered()->get(),
            // محصولات (گروه‌بندی‌شده بر اساس دسته‌بندی محصولات) — برای اتصال نمونه‌کار به محصول
            'products' => Product::with('category')->orderBy('name')->get(),
            // تصاویر منتشرشدهٔ گالری — برای مودال انتخاب ریل‌تایم کاورها
            'galleryAssets' => app(\Modules\Gallery\Services\GalleryService::class)->publishedAssets(),
        ]);
    }
}
