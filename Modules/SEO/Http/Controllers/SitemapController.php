<?php

namespace Modules\SEO\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Products\Models\Product;
use Modules\Resume\Models\Project;
use Modules\Core\Models\BlogPost;
use Modules\Settings\Models\Setting;

/**
 * sitemap.xml و robots.txt داینامیک (#ماژول سئو).
 *
 * sitemap: محصولات + مقالات + نمونه‌کارها + صفحات ثابت — دوزبانه (hreflang).
 * robots: مسدودسازی مسیرهای خصوصی + اجازه صریح به خزنده‌های AI
 * تا محصولات و خدمات در پاسخ‌های ChatGPT/Perplexity/Gemini پیشنهاد شوند.
 */
class SitemapController extends Controller
{
    public function index(): \Illuminate\Http\Response
    {
        $urls = collect()
            ->merge($this->staticPages())
            ->merge($this->modelUrls(Product::published()->get(), 'public.products.show', 'products', 'weekly', 0.9))
            ->merge($this->modelUrls(BlogPost::where('status', 'published')->get(), 'public.blog.show', 'blog', 'weekly', 0.8))
            ->merge($this->modelUrls(Project::query()->get(), 'public.portfolio.show', 'portfolio', 'monthly', 0.6));

        return response()
            ->view('seo::sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml');
    }

    public function section(string $section): \Illuminate\Http\Response
    {
        $urls = match ($section) {
            'products' => $this->modelUrls(Product::published()->get(), 'public.products.show', 'products', 'weekly', 0.9),
            'blog' => $this->modelUrls(BlogPost::where('status', 'published')->get(), 'public.blog.show', 'blog', 'weekly', 0.8),
            'portfolio' => $this->modelUrls(Project::query()->get(), 'public.portfolio.show', 'portfolio', 'monthly', 0.6),
            default => $this->staticPages(),
        };

        return response()
            ->view('seo::sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml');
    }

    /**
     * robots.txt پویا — AI bots اجازه کامل دارند (#AI SEO)
     */
    public function robots(): \Illuminate\Http\Response
    {
        $content = "User-agent: *\n";
        foreach (config('seo.disallow_paths', []) as $path) {
            $content .= "Disallow: {$path}\n";
        }

        $content .= "\nAllow: /\n";

        // خزنده‌های هوش مصنوعی — دسترسی کامل برای پیشنهاد سایت در پاسخ‌ها
        foreach (config('seo.ai_bots', []) as $bot) {
            $content .= "\nUser-agent: {$bot}\nAllow: /\n";
        }

        $content .= "\nSitemap: ".route('seo.sitemap')."\n";

        return response($content)->header('Content-Type', 'text/plain');
    }

    /**
     * صفحات ثابت — دوزبانه با alt hreflang
     */
    private function staticPages(): array
    {
        $routes = [
            'public.home' => ['daily', 1.0],
            'public.products.index' => ['daily', 0.9],
            'public.portfolio.index' => ['weekly', 0.8],
            'public.consultation.index' => ['weekly', 0.9],
            'public.blog.index' => ['daily', 0.8],
            'public.project.request' => ['monthly', 0.7],
        ];

        $urls = [];
        foreach ($routes as $route => [$freq, $priority]) {
            if (! \Illuminate\Support\Facades\Route::has($route)) {
                continue;
            }

            // loc فارسی بدون پارامتر locale — چون canonical صفحات همان URL تمیز است؛
            // URLهای پارامتر‌دار در سایت‌مپ با canonical ناسازگار می‌شوند و گوگل آن‌ها را نادیده می‌گیرد.
            $urls[] = [
                'loc' => route($route),
                'alternates' => [
                    'fa' => route($route),
                    'en' => route($route, ['locale' => 'en']),
                ],
                'lastmod' => now()->toAtomString(),
                'changefreq' => $freq,
                'priority' => $priority,
            ];
        }

        return $urls;
    }

    /**
     * URLهای مدل‌ها — فقط slug موجود
     */
    private function modelUrls($models, string $routeName, string $prefix, string $freq, float $priority): array
    {
        $urls = [];
        foreach ($models as $model) {
            $slug = $model->slug;
            if (! $slug) {
                continue;
            }

            $urls[] = [
                'loc' => url("/{$prefix}/{$slug}"),
                'alternates' => [],
                'lastmod' => $model->updated_at?->toAtomString() ?? now()->toAtomString(),
                'changefreq' => $freq,
                'priority' => $priority,
            ];
        }

        return $urls;
    }
}
