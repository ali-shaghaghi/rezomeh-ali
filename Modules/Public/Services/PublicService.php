<?php

namespace Modules\Public\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Core\Models\BlogPost;
use Modules\ProjectDev\Models\ProjectRequest;
use Modules\Resume\Models\Experience;
use Modules\Resume\Models\Project;
use Modules\Resume\Models\ResumeProfile;
use Modules\Resume\Models\Skill;
use Modules\Resume\Models\Technology;

/**
 * سرویس محتوای عمومی سایت — فقط مطالب «منتشرشده»
 */
class PublicService
{
    /**
     * پروفایل رزومه (singleton — اگر نبود می‌سازد)
     */
    public function profile(): ResumeProfile
    {
        return ResumeProfile::current();
    }

    /**
     * مهارت‌های رزومه — فناوری‌ها (تگ‌ها) + نوارهای سطح تسلط
     */
    public function skills(): array
    {
        return [
            'tags' => Technology::query()->orderBy('name')->get(),
            'bars' => Skill::query()
                ->where('is_active', true)
                ->where('show_in_resume', true)
                ->orderByDesc('proficiency')
                ->get(),
        ];
    }

    /**
     * سوابق کاری فعال رزومه
     */
    public function experiences(): Collection
    {
        return Experience::query()
            ->where('is_active', true)
            ->where('show_in_resume', true)
            ->orderByDesc('start_date')
            ->get();
    }

    /**
     * نمونه‌کارهای منتشرشده — تعداد محدود (صفحه اصلی)
     */
    public function projects(int $limit = 6): Collection
    {
        return Project::query()
            ->with(['relatedProduct', 'category'])
            ->where('status', 'published')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }

    /**
     * نمونه‌کارهای منتشرشده — صفحه‌بندی‌شده + فیلتر دسته‌بندی + جستجو (نمونه‌کارها)
     */
    public function projectsPaginated(int $perPage = 12, ?int $categoryId = null, ?string $search = null): LengthAwarePaginator
    {
        return Project::query()
            ->with(['relatedProduct', 'category'])
            ->where('status', 'published')
            ->when($categoryId, fn ($q) => $q->whereHas('categories', fn ($c) => $c->where('categories.id', $categoryId)))
            ->when($search !== null && $search !== '', fn ($q) => $q->where(function ($w) use ($search) {
                $w->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            }))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * مقالات منتشرشده — صفحه‌بندی‌شده + فیلتر دسته‌بندی + جستجو
     */
    public function posts(int $perPage = 9, ?int $categoryId = null, ?string $search = null): LengthAwarePaginator
    {
        return BlogPost::query()
            ->where('status', 'published')
            ->when($categoryId, fn ($q) => $q->whereHas('categories', fn ($c) => $c->where('categories.id', $categoryId)))
            ->when($search !== null && $search !== '', fn ($q) => $q->where(function ($w) use ($search) {
                $w->where('title', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            }))
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * یک مقاله منتشرشده با نامک
     */
    public function postBySlug(string $slug): ?BlogPost
    {
        return BlogPost::query()
            ->where('slug', $slug)
            ->where('status', 'published')
            ->first();
    }

    /**
     * نظرات تأییدشده مقاله — فقط نظرات ریشه‌ای + پاسخ‌های تأییدشده‌شان (تودرتو)
     */
    public function approvedComments(BlogPost $post): Collection
    {
        return $post->comments()
            ->whereNull('parent_id')
            ->where('status', 'approved')
            ->with([
                'user',
                'admin',
                'replies' => fn ($q) => $q->where('status', 'approved')->with(['user', 'admin']),
            ])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();
    }

    /**
     * ثبت نظر جدید یا پاسخ به نظر (هر دو در انتظار تأیید ادمین)
     */
    public function addComment(BlogPost $post, array $data): void
    {
        $parentId = null;

        if (! empty($data['parent_id'])) {
            $parent = $post->comments()->whereKey($data['parent_id'])->first();

            // فقط به نظر تأییدشده می‌توان پاسخ داد؛ حداکثر یک سطح عمق
            if ($parent && $parent->status === 'approved') {
                $parentId = $parent->parent_id ?? $parent->id;
            } else {
                // نظر والد نامعتبر/تأییدنشده → رد کن تا چیزی بی‌دلیل ساخته نشود
                throw new \RuntimeException('نظر موردنظر برای پاسخ در دسترس نیست.');
            }
        }

        $post->comments()->create([
            'user_id' => auth()->id(),
            'parent_id' => $parentId,
            'content' => $data['content'],
            'status' => 'pending',
        ]);
    }
}