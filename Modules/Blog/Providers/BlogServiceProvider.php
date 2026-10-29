<?php

namespace Modules\Blog\Providers;

use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Modules\Blog\Livewire\Admin\CommentsManager;
use Modules\Blog\Livewire\Admin\PostForm;
use Modules\Blog\Livewire\Admin\PostShow;
use Modules\Blog\Livewire\Admin\PostsIndex;
use Modules\Blog\Livewire\Admin\TagsManager;
use Modules\Blog\Policies\BlogPostPolicy;
use Modules\Blog\Policies\CommentPolicy;
use Modules\Core\Models\BlogPost;
use Modules\Core\Models\Comment;
use Modules\Core\Providers\BaseServiceProvider;

class BlogServiceProvider extends BaseServiceProvider
{
    protected function moduleName(): string
    {
        return 'Blog';
    }

    public function boot(): void
    {
        parent::boot();

        // دیتابیس ماژول وبلاگ — خودکفا (مایگریشن‌های blog_posts/categories/tags/comments داخل ماژول)
        $this->loadMigrationsFrom(module_path('Blog', 'Database'.DIRECTORY_SEPARATOR.'Migrations'));

        // روتینگ ماژول وبلاگ — خودکفا (ادمین با پیشوند admin/blog)
        $this->loadRoutesFrom(module_route_path('Blog', 'admin.php'));

        // ثبت Policies (مدل‌های ماژول با کشف خودکار لاراول پیدا نمی‌شوند)
        Gate::policy(BlogPost::class, BlogPostPolicy::class);
        Gate::policy(Comment::class, CommentPolicy::class);

        // ثبت کامپوننت‌های Livewire
        Livewire::component('admin.blog.posts-index', PostsIndex::class);
        Livewire::component('admin.blog.post-form', PostForm::class);
        Livewire::component('admin.blog.post-show', PostShow::class);
        Livewire::component('admin.blog.comments-manager', CommentsManager::class);
        Livewire::component('admin.blog.tags-manager', TagsManager::class);
    }

    public function register(): void
    {
        parent::register();
    }
}
