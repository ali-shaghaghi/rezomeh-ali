<?php

namespace Modules\Products\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Modules\Products\Enums\ProductType;
use Modules\Products\Models\Product;
use Modules\Products\Models\ProductCategory;

class ProductService
{
    /**
     * لیست محصولات منتشرشده برای گرید عمومی با فیلتر و جستجو
     */
    public function publishedList(array $filters = []): LengthAwarePaginator
    {
        $query = Product::query()
            ->published()
            ->with(['category', 'images'])
            ->orderBy('order')
            ->orderByDesc('id');

        // جستجو در نام و توضیحات
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('short_description', 'like', "%{$search}%");
            });
        }

        // فیلتر دسته‌بندی
        if (! empty($filters['category'])) {
            $query->where('category_id', $filters['category']);
        }

        // فیلتر نوع محصول
        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        // مرتب‌سازی
        $sort = $filters['sort'] ?? 'newest';
        $query->when($sort === 'newest', fn ($q) => $q->latest())
            ->when($sort === 'cheapest', fn ($q) => $q->orderBy('price'))
            ->when($sort === 'most_expensive', fn ($q) => $q->orderByDesc('price'))
            ->when($sort === 'popular', fn ($q) => $q->orderByDesc('order'));

        return $query->paginate(config('products.per_page', 12));
    }

    /**
     * محصول با slug (فقط منتشرشده)
     */
    public function findBySlug(string $slug): Product
    {
        return Product::published()->with(['category', 'images'])->where('slug', $slug)->firstOrFail();
    }

    /**
     * محصولاتی که در صفحه رزومه نمایش داده می‌شوند
     */
    public function resumeProducts(int $limit = 6): \Illuminate\Database\Eloquent\Collection
    {
        return Product::showInResume()
            ->with('category')
            ->orderBy('order')
            ->limit($limit)
            ->get();
    }

    /**
     * لیست محصولات برای پنل ادمین
     */
    public function adminList(array $filters = []): LengthAwarePaginator
    {
        $query = Product::query()->with(['category']);

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (! empty($filters['category'])) {
            $query->where('category_id', $filters['category']);
        }

        if (isset($filters['published']) && $filters['published'] !== '') {
            $query->where('is_published', (bool) $filters['published']);
        }

        return $query->orderByDesc('id')->paginate(config('products.per_page', 12));
    }

    /**
     * تولید slug یکتا
     */
    public function generateSlug(string $name): string
    {
        $base = Str::slug($name);
        if ($base === '') {
            $base = Str::random(8);
        }

        $slug = $base;
        $counter = 1;

        while (Product::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }

    /**
     * ذخیره محصول (ایجاد یا ویرایش)
     */
    public function save(Product $product, array $data): Product
    {
        if (! $product->exists) {
            $data['slug'] = $data['slug'] ?? $this->generateSlug($data['name']);
        }

        $product->fill($data);
        $product->save();

        return $product->refresh();
    }

    /**
     * حذف محصول
     */
    public function delete(Product $product): bool
    {
        $product->images()->delete();

        return (bool) $product->delete();
    }

    /**
     * آمار داشبورد محصولات
     */
    public function stats(): array
    {
        return [
            'total' => Product::count(),
            'published' => Product::published()->count(),
            'templates' => Product::where('type', ProductType::Template->value)->count(),
            'categories' => ProductCategory::count(),
            'resume' => Product::showInResume()->count(),
        ];
    }
}
