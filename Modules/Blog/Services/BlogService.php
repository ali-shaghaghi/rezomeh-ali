<?php

namespace Modules\Blog\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Modules\Core\Models\BlogPost;
use Modules\Core\Models\Comment;
use Modules\Core\Models\Tag;

class BlogService
{
    /**
     * لیست مقالات با جستجو، فیلتر و صفحه‌بندی
     */
    public function list(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = BlogPost::with(['user', 'categories', 'tags']);

        // جستجو در عنوان و خلاصه
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%");
            });
        }

        // فیلتر وضعیت
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // فیلتر دسته‌بندی
        if (! empty($filters['category_id'])) {
            $query->whereHas('categories', fn ($q) => $q->where('categories.id', $filters['category_id']));
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * ذخیره مقاله (ایجاد یا ویرایش) + همگام‌سازی دسته‌ها و برچسب‌ها
     */
    public function save(BlogPost $post, array $data): BlogPost
    {
        // تنظیم تاریخ انتشار هنگام انتشار برای اولین بار
        if (($data['status'] ?? 'draft') === 'published' && ! $post->published_at) {
            $data['published_at'] = now();
        }

        $post->fill($data);
        $post->save();

        // همگام‌سازی روابط
        if (array_key_exists('categories', $data)) {
            $post->categories()->sync($data['categories'] ?? []);
        }
        if (array_key_exists('tags', $data)) {
            $post->tags()->sync($data['tags'] ?? []);
        }

        return $post->refresh();
    }

    /**
     * حذف امن مقاله (همراه روابط)
     */
    public function delete(BlogPost $post): bool
    {
        $post->categories()->detach();
        $post->tags()->detach();

        return (bool) $post->delete();
    }

    /**
     * تغییر وضعیت انتشار
     */
    public function toggleStatus(BlogPost $post): BlogPost
    {
        if ($post->status === 'published') {
            $post->status = 'draft';
        } else {
            $post->status = 'published';
            if (! $post->published_at) {
                $post->published_at = now();
            }
        }
        $post->save();

        return $post;
    }

    /**
     * ایجاد برچسب از نام (اگر وجود نداشته باشد می‌سازد)
     */
    public function findOrCreateTag(string $name): int
    {
        $slug = Str::slug($name) ?: Str::slug(Str::ascii($name)) ?: 'tag-'.Str::random(6);

        $tag = Tag::firstOrCreate(
            ['slug' => $slug],
            ['name' => $name]
        );

        return $tag->id;
    }

    /**
     * لیست نظرات با فیلتر وضعیت
     */
    public function commentsList(array $filters = [], int $perPage = 9): LengthAwarePaginator
    {
        $query = Comment::with(['user', 'admin', 'parent.user', 'parent.admin', 'blogPost']);

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('content', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('admin', fn ($a) => $a->where('name', 'like', "%{$search}%"));
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * تغییر وضعیت نظر (تایید / رد)
     */
    public function setCommentStatus(Comment $comment, string $status): Comment
    {
        $comment->update(['status' => $status]);

        return $comment;
    }
}
