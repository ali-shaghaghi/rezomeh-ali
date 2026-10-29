<?php

namespace Modules\Public\Http\Controllers;

use Modules\Core\Models\Category;
use Modules\Public\Services\PublicService;

class BlogController extends \App\Http\Controllers\Controller
{
    public function __construct(
        private readonly PublicService $publicService,
    ) {}

    /**
     * لیست مقالات منتشرشده + فیلتر دسته‌بندی + جستجو
     */
    public function index()
    {
        $categoryId = request()->integer('category');
        $category = $categoryId ? Category::find($categoryId) : null;
        $search = trim((string) request()->query('q', ''));

        $posts = $this->publicService->posts(
            config('public.blog_per_page', 9),
            $categoryId ?: null,
            $search !== '' ? $search : null
        );

        $categories = Category::query()
            ->whereHas('blogPosts', fn ($q) => $q->where('status', 'published'))
            ->orderBy('name')
            ->get();

        return view('public::blog.index', compact('posts', 'categories', 'category', 'search'));
    }

    /**
     * نمایش مقاله + نظرات تأییدشده
     */
    public function show(string $slug)
    {
        $post = $this->publicService->postBySlug($slug);

        abort_unless($post, 404);

        // افزایش بازدید (فقط درخواست GET)
        $post->increment('views');

        $comments = $this->publicService->approvedComments($post);

        $related = $this->publicService->posts(3);

        return view('public::blog.show', compact('post', 'comments', 'related'));
    }

    /**
     * ثبت نظر روی مقاله (کاربر وارد‌شده)
     */
    public function comment(string $slug)
    {
        abort_unless(auth()->check(), 404, 'برای ثبت نظر ابتدا وارد شوید.');

        $post = $this->publicService->postBySlug($slug);

        abort_unless($post, 404);

        $validated = request()->validate([
            'content' => ['required', 'string', 'min:5', 'max:3000'],
            'parent_id' => ['nullable', 'integer', 'exists:comments,id'],
        ], [
            'content.required' => 'متن نظر الزامی است.',
            'content.min' => 'متن نظر باید حداقل ۵ کاراکتر باشد.',
            'content.max' => 'متن نظر نمی‌تواند بیشتر از ۳۰۰۰ کاراکتر باشد.',
            'parent_id.exists' => 'نظر موردنظر برای پاسخ در دسترس نیست.',
        ]);

        try {
            $this->publicService->addComment($post, $validated);
        } catch (\RuntimeException $e) {
            return redirect()
                ->to(route('public.blog.show', $post->slug).'#comments')
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->to(route('public.blog.show', $post->slug).'#comments')
            ->with('success', 'نظر شما ثبت شد و پس از تأیید نمایش داده می‌شود.');
    }
}
