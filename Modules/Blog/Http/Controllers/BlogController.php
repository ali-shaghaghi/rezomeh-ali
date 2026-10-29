<?php

namespace Modules\Blog\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Modules\Core\Models\BlogPost;
use Modules\Core\Models\Comment;

class BlogController extends Controller
{
    use AuthorizesRequests;

    /**
     * لیست مقالات (Livewire)
     */
    public function index()
    {
        $this->authorize('viewAny', BlogPost::class);

        return view('blog::posts.index');
    }

    /**
     * ایجاد مقاله جدید (Livewire فرم)
     */
    public function create()
    {
        $this->authorize('create', BlogPost::class);

        return view('blog::posts.create');
    }

    /**
     * نمایش مستقل مقاله (Livewire)
     */
    public function show(int $post)
    {
        $post = BlogPost::with(['user', 'categories', 'tags', 'comments.user'])->findOrFail($post);
        $this->authorize('view', $post);

        return view('blog::posts.show', compact('post'));
    }

    /**
     * ویرایش مقاله (Livewire فرم)
     */
    public function edit(int $post)
    {
        $post = BlogPost::with(['categories', 'tags'])->findOrFail($post);
        $this->authorize('update', $post);

        return view('blog::posts.edit', compact('post'));
    }

    /**
     * مدیریت نظرات (Livewire)
     */
    public function comments()
    {
        $this->authorize('viewAny', Comment::class);

        return view('blog::comments.index');
    }
}
