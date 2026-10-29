<?php

namespace Modules\SEO\Livewire\Admin;

use Livewire\Component;
use Modules\SEO\Models\SeoMeta;

/**
 * مدیریت متاهای SEO در پنل ادمین (#ماژول سئو).
 * هر صفحه (route) فارسی و انگلیسی جدا — عنوان، توضیح، کلمات کلیدی،
 * اسکیما، FAQ و پیش‌نمایش SERP گوگل به‌صورت زنده.
 */
class MetaManager extends Component
{
    public string $locale = 'fa'; // fa | en

    public array $routes = []; // route_name => [fa_meta, en_meta]

    public bool $modalOpen = false;

    public string $editRoute = '';

    // فرم
    public string $title = '';

    public string $description = '';

    public string $keywords = '';

    public string $og_image = '';

    public string $schema_type = 'WebPage';

    public array $faq = []; // [{q, a}]

    public bool $noindex = false;

    public function mount(): void
    {
        $this->loadRoutes();
    }

    public function loadRoutes(): void
    {
        $metas = SeoMeta::orderBy('route_name')->get()->groupBy('route_name');

        // مسیرهای پیش‌فرض مهم — همیشه نمایش داده می‌شوند
        $known = [
            'public.home', 'public.products.index', 'public.portfolio.index',
            'public.consultation.index', 'public.blog.index', 'public.project.request',
        ];

        $this->routes = collect($known)
            ->merge($metas->keys())
            ->unique()
            ->values()
            ->mapWithKeys(function ($route) use ($metas) {
                return [$route => [
                    'fa' => $metas->get($route)?->firstWhere('locale', 'fa'),
                    'en' => $metas->get($route)?->firstWhere('locale', 'en'),
                ]];
            })
            ->all();
    }

    public function openEdit(string $route): void
    {
        $this->editRoute = $route;
        $meta = SeoMeta::where('route_name', $route)->where('locale', $this->locale)->first();

        $this->fill([
            'title' => $meta?->title ?? '',
            'description' => $meta?->description ?? '',
            'keywords' => $meta?->keywords ?? '',
            'og_image' => $meta?->og_image ?? '',
            'schema_type' => $meta?->schema_type ?? 'WebPage',
            'faq' => $meta?->faq_json ?? [['q' => '', 'a' => '']],
            'noindex' => $meta?->noindex ?? false,
        ]);

        $this->modalOpen = true;
    }

    public function addFaq(): void
    {
        $this->faq[] = ['q' => '', 'a' => ''];
    }

    public function removeFaq(int $i): void
    {
        unset($this->faq[$i]);
        $this->faq = array_values($this->faq);
    }

    public function save(): void
    {
        $this->validate([
            'title' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:300',
            'keywords' => 'nullable|string|max:500',
            'og_image' => 'nullable|string|max:255',
            'schema_type' => 'nullable|string|max:50',
            'faq' => 'nullable|array',
            'faq.*.q' => 'nullable|string|max:200',
            'faq.*.a' => 'nullable|string|max:1000',
        ]);

        // ردیف‌های FAQ خالی حذف می‌شوند
        $faq = array_values(array_filter($this->faq, fn ($item) => trim((string) ($item['q'] ?? '')) !== ''));

        SeoMeta::updateOrCreate(
            ['route_name' => $this->editRoute, 'locale' => $this->locale],
            [
                'title' => $this->title ?: null,
                'description' => $this->description ?: null,
                'keywords' => $this->keywords ?: null,
                'og_image' => $this->og_image ?: null,
                'schema_type' => $this->schema_type ?: null,
                'faq_json' => $faq ?: null,
                'noindex' => $this->noindex,
            ]
        );

        $this->modalOpen = false;
        $this->loadRoutes();

        session()->flash('success', 'متاهای SEO ذخیره شد.');
    }

    public function render()
    {
        return view('seo::livewire.admin.meta-manager');
    }
}
