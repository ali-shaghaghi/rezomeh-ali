<?php

namespace Modules\Public\Http\Controllers;

use Modules\Core\Models\Category;
use Modules\Public\Services\PublicService;

class PortfolioController extends \App\Http\Controllers\Controller
{
    public function __construct(
        private readonly PublicService $publicService,
    ) {}

    /**
     * لیست همه نمونه‌کارهای منتشرشده + فیلتر دسته‌بندی
     */
    public function index()
    {
        $categoryId = request()->integer('category');
        $category = $categoryId ? Category::find($categoryId) : null;
        $search = trim((string) request()->query('q', ''));

        $projects = $this->publicService->projectsPaginated(
            config('public.portfolio_per_page', 12),
            $categoryId ?: null,
            $search !== '' ? $search : null
        );

        $categories = Category::query()
            ->whereHas('projects', fn ($q) => $q->where('status', 'published'))
            ->orderBy('name')
            ->get();

        return view('public::portfolio.index', compact('projects', 'categories', 'category', 'search'));
    }
}
