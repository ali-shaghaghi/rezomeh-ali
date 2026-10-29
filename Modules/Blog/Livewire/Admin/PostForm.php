<?php

namespace Modules\Blog\Livewire\Admin;

use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Admin;
use Modules\Blog\Services\BlogService;
use Modules\Core\Models\BlogPost;
use Modules\Core\Models\Category;
use Modules\Core\Models\Tag;

class PostForm extends Component
{
    use WithFileUploads;

    public ?BlogPost $post = null;

    public bool $creating = false;

    public string $locale = 'fa';

    // فیلدهای فرم
    public string $title = '';

    public string $slug = '';

    public string $excerpt = '';

    public string $content = '';

    public $featured_image = null;

    public string $status = 'draft';

    public string $seo_title = '';

    public string $seo_description = '';

    // ===== نسخه انگلیسی (اختیاری — برای سئوی نسخه خارجی) =====

    public string $title_en = '';

    public string $excerpt_en = '';

    public string $content_en = '';

    public string $seo_title_en = '';

    public string $seo_description_en = '';

    public array $category_ids = [];

    public array $tag_ids = [];

    // نمایش فایل موجود
    public ?string $existingImage = null;

    // انتخاب تصویر شاخص از گالری (مسیر نسبی gallery/{hash}) — آپلود مستقیم در featured_image می‌ماند
    public ?string $gallerySrc = null;

    // متن جایگزین تصویر شاخص (سئو / دسترسی‌پذیری)
    public string $featured_image_alt = '';

    // ===== تصاویر درون‌متنی (آپلود فوری و درج در محل مکان‌نما) =====
    // آپلودهای متن فارسی و انگلیسی از هم جدا — رویداد هم جدا تا هر نوار
    // ابزار فقط متن خودش را ویرایش کند
    public $inlineUpload = null;

    public $inlineUploadEn = null;

    // وضعیت ذخیره
    public bool $saving = false;

    // ریدایرکت پس از ذخیره
    public ?string $redirectTo = null;

    protected function rules(): array
    {
        $slugUnique = $this->post && $this->post->exists
            ? 'unique:blog_posts,slug,'.$this->post->id
            : 'unique:blog_posts,slug';

        return [
            'locale' => 'in:fa,en',
            'title' => 'required|string|max:255',
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', $slugUnique],
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'featured_image' => 'nullable|image|max:4096',
            'gallerySrc' => ['nullable', 'string', 'regex:#^gallery/[A-Za-z0-9_\-]{16,128}$#'],
            'featured_image_alt' => 'nullable|string|max:255',
            'status' => 'required|in:draft,published',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:500',
            // نسخه انگلیسی — اختیاری
            'title_en' => 'nullable|string|max:255',
            'excerpt_en' => 'nullable|string|max:500',
            'content_en' => 'nullable|string',
            'seo_title_en' => 'nullable|string|max:255',
            'seo_description_en' => 'nullable|string|max:500',
            'category_ids' => 'array',
            'category_ids.*' => 'exists:categories,id',
            'tag_ids' => 'array',
            'tag_ids.*' => 'exists:tags,id',
        ];
    }

    public function mount(?BlogPost $post = null, string $locale = 'fa'): void
    {
        $this->locale = in_array($locale, ['fa', 'en'], true) ? $locale : 'fa';

        if ($post && $post->exists) {
            $this->post = $post;
            $this->fillFromPost();
        } else {
            $this->creating = true;
            $this->post = null;
        }
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
        $service = app(\Modules\Gallery\Services\GalleryService::class);
        foreach ($files as $file) {
            $asset = $service->createFromUploadedFile($file);
            $urls[] = $asset->srcAttribute();
        }

        $this->dispatch($event, urls: $urls);
    }

    // پر کردن فیلدها از مقاله موجود
    private function fillFromPost(): void
    {
        $p = $this->post;
        $this->title = $p->title;
        $this->slug = $p->slug;
        $this->excerpt = $p->excerpt ?? '';
        $this->content = $p->content ?? '';
        $this->status = $p->status ?? 'draft';
        $this->seo_title = $p->seo_title ?? '';
        $this->seo_description = $p->seo_description ?? '';
        // انگلیسی
        $this->title_en = $p->title_en ?? '';
        $this->excerpt_en = $p->excerpt_en ?? '';
        $this->content_en = $p->content_en ?? '';
        $this->seo_title_en = $p->seo_title_en ?? '';
        $this->seo_description_en = $p->seo_description_en ?? '';
        $this->category_ids = $p->categories->pluck('id')->map(fn ($id) => (string) $id)->toArray();
        $this->tag_ids = $p->tags->pluck('id')->map(fn ($id) => (string) $id)->toArray();
        // تصویر شاخص — مسیر گالری (gallery/{hash}) یا فایل آپلودی قدیمی (storage)
        $fi = $p->featured_image;
        if ($fi && str_starts_with(ltrim($fi, '/'), 'gallery/')) {
            $this->gallerySrc = ltrim($fi, '/');
            $this->existingImage = null;
        } else {
            $this->gallerySrc = null;
            $this->existingImage = $fi;
        }
        $this->featured_image_alt = $p->featured_image_alt ?? '';
    }

    // تولید خودکار slug از عنوان
    public function updatedTitle(string $value): void
    {
        if ($this->creating || empty($this->slug)) {
            $this->slug = Str::slug($value) ?: Str::slug(Str::ascii($value));
            if (empty($this->slug)) {
                $this->slug = 'post-'.Str::random(6);
            }
        }
    }

    /**
     * انتخاب تصویر از گالری (مودال ریل‌تایم) — src ممکن است مطلق (http://…/gallery/hash)
     * باشد؛ فقط مسیر نسبی gallery/{hash} ذخیره می‌شود تا با تغییر دامنه نشکند.
     */
    public function pickGalleryImage(string $src, string $alt = ''): void
    {
        $path = preg_replace('#^https?://[^/]+#', '', $src);
        $path = ltrim((string) $path, '/');

        if (! preg_match('#^gallery/[A-Za-z0-9_\-]{16,128}$#', $path)) {
            return;
        }

        $this->gallerySrc = $path;
        $this->featured_image = null; // انتخاب از گالری جایگزین آپلود مستقیم می‌شود

        // سئو: alt خود تصویر گالری در صورت وجود به‌عنوان پیش‌فرض اعمال می‌شود
        if (trim($alt) !== '') {
            $this->featured_image_alt = mb_substr(trim(strip_tags($alt)), 0, 255);
        }
    }

    // حذف تصویر شاخص (همهٔ منابع: آپلود، گالری، مسیر ذخیره‌شده + alt)
    public function removeImage(): void
    {
        $this->existingImage = null;
        $this->gallerySrc = null;
        $this->featured_image = null;
        $this->featured_image_alt = '';
    }

    public function save(): void
    {
        $this->authorize($this->post && $this->post->exists ? 'update' : 'create', $this->post ?? BlogPost::class);

        $validated = $this->validate();

        $this->saving = true;

        try {
            // مالکیت مقاله: در ویرایش حفظ می‌شود؛ در ایجاد از هویت فعلی ثبت می‌شود
            // (شناسه ادمین در ستون user_id که FK به users دارد درج نمی‌شود)
            if ($this->post && $this->post->exists) {
                $authorship = [
                    'user_id' => $this->post->user_id,
                    'admin_id' => $this->post->admin_id,
                ];
            } else {
                $identity = auth()->user();
                $authorship = $identity instanceof Admin
                    ? ['user_id' => null, 'admin_id' => $identity->id]
                    : ['user_id' => auth()->id(), 'admin_id' => null];
            }

            // در صورت ویرایش، مقدار فعلی هر فیلد فارسی/انگلیسی در صورت خالی نبودن حفظ می‌شود
            // (حالت ویرایش: اگر کاربر نسخه انگلیسی را خالی بگذارد، مقدار قبلی نگهداری می‌شود)
            $prevContent = $this->post?->content;
            $prevContentEn = $this->post?->content_en;

            $data = [
                ...$authorship,
                'title' => $validated['title'],
                'slug' => $validated['slug'],
                'excerpt' => $validated['excerpt'] ?: null,
                'content' => $validated['content'],
                'status' => $validated['status'],
                'seo_title' => $validated['seo_title'] ?: null,
                'seo_description' => $validated['seo_description'] ?: null,
                // نسخهٔ انگلیسی: اگر خالی باشد مقدار قبلی همان فیلد حفظ می‌شود
                // (قبلاً به‌اشتباه کل محتوای فارسی اینجا می‌نشست و باعث «Data too long» می‌شد)
                'title_en' => $validated['title_en'] ?: $this->post?->title_en,
                'excerpt_en' => $validated['excerpt_en'] ?: $this->post?->excerpt_en,
                'content_en' => $validated['content_en'] ?: $prevContentEn,
                'seo_title_en' => $validated['seo_title_en'] ?: $this->post?->seo_title_en,
                'seo_description_en' => $validated['seo_description_en'] ?: $this->post?->seo_description_en,
                'categories' => array_map('intval', $validated['category_ids']),
                'tags' => array_map('intval', $validated['tag_ids']),
            ];

            // تصویر شاخص: آپلود مستقیم ← انتخاب از گالری ← مسیر قبلی؛ اگر حذف شده باشد null ثبت می‌شود
            if ($this->featured_image) {
                $data['featured_image'] = $this->featured_image->store('blog/featured', 'public');
            } elseif ($this->gallerySrc) {
                $data['featured_image'] = $this->gallerySrc;
            } elseif ($this->existingImage) {
                $data['featured_image'] = $this->existingImage;
            } else {
                $data['featured_image'] = null;
            }
            $data['featured_image_alt'] = $validated['featured_image_alt'] ?: null;

            $post = $this->post ?? new BlogPost;
            $saved = app(BlogService::class)->save($post, $data);

            session()->flash('success', $this->creating
                ? 'مقاله «'.$saved->title.'» با موفقیت ایجاد شد.'
                : 'مقاله «'.$saved->title.'» با موفقیت به‌روزرسانی شد.');

            $this->redirect(route('admin.blog.show', $saved), navigate: true);

            return;
        } finally {
            $this->saving = false;
        }
    }

    public function render()
    {
        return view('blog::livewire.admin.post-form', [
            'categories' => Category::orderBy('name')->get(),
            'tags' => Tag::orderBy('name')->get(),
            // تصاویر منتشرشدهٔ گالری — برای درج در ویرایشگر متن
            'galleryAssets' => app(\Modules\Gallery\Services\GalleryService::class)->publishedAssets(),
        ]);
    }
}
