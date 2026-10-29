<?php

namespace Modules\Resume\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Resume\Models\Project;

class PortfolioService
{
    /**
     * لیست پروژه‌ها با جستجو، فیلتر و صفحه‌بندی
     */
    public function list(array $filters = [], int $perPage = 9): LengthAwarePaginator
    {
        $query = Project::with(['category', 'technologies', 'skills', 'relatedProduct']);

        // جستجو در عنوان و توضیحات
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('short_description', 'like', "%{$search}%");
            });
        }

        // فیلتر وضعیت
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // فیلتر دسته‌بندی
        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        // فیلتر ویژه
        if (isset($filters['featured']) && $filters['featured'] !== '') {
            $query->where('featured', (bool) $filters['featured']);
        }

        return $query->ordered()->paginate($perPage);
    }

    /**
     * ذخیره پروژه (ایجاد یا ویرایش)
     */
    public function save(Project $project, array $data): Project
    {
        $project->fill($data);
        $project->save();

        // همگام‌سازی روابط
        if (array_key_exists('technologies', $data)) {
            $project->technologies()->sync($data['technologies'] ?? []);
        }
        if (array_key_exists('skills', $data)) {
            $project->skills()->sync($data['skills'] ?? []);
        }

        return $project->refresh();
    }

    /**
     * حذف امن پروژه (همراه روابط)
     */
    public function delete(Project $project): bool
    {
        $project->technologies()->detach();
        $project->skills()->detach();

        return (bool) $project->delete();
    }

    /**
     * تغییر وضعیت انتشار
     */
    public function toggleStatus(Project $project): Project
    {
        $project->status = $project->status === 'published' ? 'draft' : 'published';
        $project->save();

        return $project;
    }

    /**
     * تغییر وضعیت ویژه
     */
    public function toggleFeatured(Project $project): Project
    {
        $project->featured = ! $project->featured;
        $project->save();

        return $project;
    }

    /**
     * جابجایی ترتیب نمایش پروژه
     */
    public function move(Project $project, string $direction): void
    {
        if ($direction === 'up') {
            $project->order--;
        } elseif ($direction === 'down') {
            $project->order++;
        }
        $project->save();
    }

    /**
     * آدرس دمو برای نمایش
     */
    public function resolveDemoLink(Project $project): ?string
    {
        return $project->demo_link;
    }
}
