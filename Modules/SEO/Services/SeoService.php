<?php

namespace Modules\SEO\Services;

use Modules\SEO\Models\SeoMeta;
use Modules\Settings\Models\Setting;

/**
 * سرویس مرکزی SEO — تولید متا، JSON-LD و تنظیمات (#ماژول سئو).
 */
class SeoService
{
    /**
     * متای صفحه جاری — از seo_metas یا fallback خودکار
     */
    public function metaFor(string $routeName, ?string $locale = null): array
    {
        $locale = $locale ?? current_locale();
        $stored = SeoMeta::for($routeName, $locale);

        $defaults = $this->defaults($routeName, $locale);

        return [
            'title' => $stored?->title ?: $defaults['title'],
            'description' => $stored?->description ?: $defaults['description'],
            'keywords' => $stored?->keywords ?: $defaults['keywords'],
            'og_image' => $stored?->og_image ?: $defaults['og_image'],
            'schema_type' => $stored?->schema_type ?: $defaults['schema_type'],
            'faq' => $stored?->faq_json ?? [],
            'noindex' => $stored?->noindex ?? false,
            'canonical' => url()->current(),
        ];
    }

    /**
     * مقادیر پیش‌فرض بر اساس مسیر — سئوی خودکار صفحات اصلی سه‌گانه
     * (انجام پروژه، مشاوره، محصولات) + برند دوزبانه
     */
    private function defaults(string $routeName, string $locale): array
    {
        return $locale === 'en'
            ? $this->defaultsEn($routeName)
            : $this->defaultsFa($routeName);
    }

    /**
     * پیش‌فرض‌های فارسی — سئوی «انجام پروژه / مشاوره / محصولات»
     */
    private function defaultsFa(string $routeName): array
    {
        $brand = Setting::get('site_title', config('seo.brand.name'));

        $map = [
            'public.home' => [
                'title' => "{$brand} | توسعه‌دهنده وب و مشاور فنی",
                'description' => "{$brand} — طراحی سایت، ساخت CRM اختصاصی، انجام پروژه‌های برنامه‌نویسی و مشاوره آنلاین برای کسب‌وکارها. نمونه‌کارها و محصولات آماده.",
                'keywords' => 'طراحی سایت, انجام پروژه برنامه نویسی, مشاوره فنی, CRM اختصاصی, علی شقاقی',
                'schema_type' => 'ProfessionalService',
            ],
            'public.products.index' => [
                'title' => "خرید قالب، CRM و اسکریپت آماده | {$brand}",
                'description' => 'فروش محصولات دیجیتال: قالب‌های آماده، سیستم CRM و اسکریپت‌های کاربردی با لایسنس معتبر و پشتیبانی.',
                'keywords' => 'خرید قالب سایت, CRM آماده, اسکریپت, محصولات دیجیتال',
                'schema_type' => 'ItemList',
            ],
            'public.portfolio.index' => [
                'title' => "نمونه‌کارهای برنامه‌نویسی | {$brand}",
                'description' => 'پروژه‌های انجام‌شده: فروشگاه اینترنتی، CRM سازمانی، سایت شرکتی و پلتفرم‌های وب.',
                'keywords' => 'نمونه کار برنامه نویسی, پورتفولیو, پروژه وب',
                'schema_type' => 'CollectionPage',
            ],
            'public.consultation.index' => [
                'title' => "رزرو مشاوره آنلاین کسب‌وکار | {$brand}",
                'description' => 'مشاوره آنلاین تخصصی برای کسب‌وکارها: انتخاب تکنولوژی، معماری پروژه، دیجیتال‌سازی و CRM.',
                'keywords' => 'مشاوره آنلاین, مشاوره کسب و کار, مشاوره برنامه نویسی',
                'schema_type' => 'Service',
            ],
            'public.blog.index' => [
                'title' => "مقالات و آموزش‌های فنی | {$brand}",
                'description' => 'مقالات برنامه‌نویسی، Laravel، معماری نرم‌افزار و تجربیات واقعی پروژه‌ها.',
                'keywords' => 'مقالات برنامه نویسی, آموزش لاراول, وبلاگ فنی',
                'schema_type' => 'Blog',
            ],
        ];

        $d = $map[$routeName] ?? [
            'title' => $brand,
            'description' => Setting::get('site_description', "{$brand} — وب‌سایت رسمی"),
            'keywords' => $brand,
            'schema_type' => 'WebPage',
        ];

        $d['og_image'] = Setting::get('site_logo', '');

        return $d;
    }

    /**
     * پیش‌فرض‌های انگلیسی — سئوی برندینگ دوزبانه
     */
    private function defaultsEn(string $routeName): array
    {
        $brand = config('seo.brand.name_en');

        $map = [
            'public.home' => [
                'title' => "{$brand} | Web Developer & Technical Consultant",
                'description' => "{$brand} — Custom web development, CRM solutions, project outsourcing and online business consulting.",
                'keywords' => 'web development, custom CRM, hire developer, technical consulting',
                'schema_type' => 'ProfessionalService',
            ],
            'public.products.index' => [
                'title' => "Templates, CRM & Scripts | {$brand}",
                'description' => 'Ready-to-use digital products: website templates, CRM systems and scripts with license & support.',
                'keywords' => 'buy website template, CRM software, scripts',
                'schema_type' => 'ItemList',
            ],
            'public.consultation.index' => [
                'title' => "Book Online Business Consulting | {$brand}",
                'description' => 'Online consulting for businesses: tech stack selection, project architecture, CRM and digital transformation.',
                'keywords' => 'online consulting, business consulting, software architecture',
                'schema_type' => 'Service',
            ],
        ];

        $d = $map[$routeName] ?? [
            'title' => $brand,
            'description' => "Official website of {$brand}",
            'keywords' => $brand,
            'schema_type' => 'WebPage',
        ];

        $d['og_image'] = Setting::get('site_logo', '');

        return $d;
    }

    /**
     * JSON-LD کامل صفحه — Organization + schema صفحه + FAQ
     *
     * @return array<int, array<string, mixed>>
     */
    public function jsonLd(string $routeName, array $meta, ?object $model = null): array
    {
        $schemas = [$this->organizationSchema()];

        $schemaType = $meta['schema_type'] ?? 'WebPage';
        $custom = $this->pageSchema($schemaType, $meta, $model);
        if ($custom) {
            $schemas[] = $custom;
        }

        if (! empty($meta['faq'])) {
            $schemas[] = [
                '@context' => 'https://schema.org',
                '@type' => 'FAQPage',
                'mainEntity' => collect($meta['faq'])->map(fn ($item) => [
                    '@type' => 'Question',
                    'name' => $item['q'] ?? '',
                    'acceptedAnswer' => ['@type' => 'Answer', 'text' => $item['a'] ?? ''],
                ])->values()->all(),
            ];
        }

        return $schemas;
    }

    /**
     * اسکیمای برند (Person یا Organization) + sameAs شبکه‌های اجتماعی
     */
    public function organizationSchema(): array
    {
        $brand = config('seo.brand');
        $name = Setting::get('site_title', $brand['name']);
        $isPerson = ($brand['type'] ?? 'Person') === 'Person';
        $logo = Setting::get('site_logo', $brand['logo'] ?? '');

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => $isPerson ? 'Person' : 'Organization',
            'name' => $name,
            'url' => route('public.home'),
        ];

        if ($logo) {
            $schema['logo'] = asset('storage/'.$logo);
        }

        $socials = array_values(array_filter([
            Setting::get('social_linkedin'),
            Setting::get('social_github'),
            Setting::get('social_instagram'),
            Setting::get('social_telegram'),
        ]));
        if ($socials !== []) {
            $schema['sameAs'] = $socials;
        }

        if ($isPerson) {
            $schema['jobTitle'] = 'Full-Stack Web Developer & Technical Consultant';
            $schema['knowsAbout'] = ['Laravel', 'PHP', 'JavaScript', 'CRM Development', 'Web Architecture'];
        } else {
            $schema['description'] = Setting::get('site_description', '');
        }

        return $schema;
    }

    /**
     * اسکیمای اختصاصی هر نوع صفحه (Product / Article / Service / ...)
     */
    private function pageSchema(string $type, array $meta, ?object $model): ?array
    {
        $base = [
            '@context' => 'https://schema.org',
            '@type' => $type,
            'name' => $meta['title'],
            'description' => $meta['description'],
            'url' => $meta['canonical'] ?? url()->current(),
        ];

        if ($model) {
            if ($type === 'Product' && method_exists($model, 'localizedName')) {
                $base['name'] = $model->localizedName();
                $base['description'] = $model->localizedShortDescription() ?? $meta['description'];
                if (! empty($model->cover_image)) {
                    $base['image'] = asset('storage/'.$model->cover_image);
                }
                if (isset($model->final_price)) {
                    $usd = usd_price((float) $model->final_price);
                    $base['offers'] = [
                        '@type' => 'Offer',
                        'price' => $usd ?? (float) $model->final_price,
                        'priceCurrency' => $usd !== null ? 'USD' : 'IRT',
                        'availability' => 'https://schema.org/InStock',
                    ];
                }
            } elseif ($type === 'Article' && method_exists($model, 'localizedTitle')) {
                $base['headline'] = $model->localizedTitle();
                $base['datePublished'] = $model->created_at?->toIso8601String();
                $base['dateModified'] = $model->updated_at?->toIso8601String();
                if (! empty($model->featured_image)) {
                    // false: JSON-LD/سئو همیشه تصویر اصلی (png/jpg) را می‌گیرد، نه WebP
                    $base['image'] = featured_image_url($model->featured_image, false);
                }
            }
        }

        return $base;
    }
}
