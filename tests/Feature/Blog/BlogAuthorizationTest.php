<?php

namespace Tests\Feature\Blog;

use App\Models\Admin;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Blog\Database\Seeders\BlogPermissionSeeder;
use Modules\Blog\Services\BlogService;
use Modules\Core\Models\BlogPost;
use Modules\Core\Models\Category;
use Modules\Core\Models\Comment;
use Modules\Core\Models\Role;
use Modules\Core\Models\Tag;
use Tests\TestCase;

class BlogAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $superAdmin;

    protected User $author;

    protected User $editor;

    protected User $plainUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        $this->seed(BlogPermissionSeeder::class);

        // نقش‌های نمونه
        $superRole = Role::where('slug', 'super-admin')->firstOrFail();
        $editorRole = Role::where('slug', 'editor')->firstOrFail();
        $userRole = Role::where('slug', 'user')->firstOrFail();

        $this->superAdmin = $this->makeAdmin('super@test.com', 'super-admin');

        // نویسنده محتوا (کاربر عادی) — برای FK جدول users
        $this->author = User::create(['name' => 'Author', 'email' => 'blog-author@test.com', 'password' => 'password', 'is_active' => true]);
        $this->author->roles()->attach($userRole);

        $this->editor = User::create(['name' => 'Editor', 'email' => 'editor@test.com', 'password' => 'password', 'is_active' => true]);
        $this->editor->roles()->attach($editorRole);

        $this->plainUser = User::create(['name' => 'User', 'email' => 'user@test.com', 'password' => 'password', 'is_active' => true]);
        $this->plainUser->roles()->attach($userRole);
    }

    // ===== تست Policies =====

    public function test_super_admin_can_manage_blog_posts(): void
    {
        $post = BlogPost::create(['user_id' => $this->author->id, 'title' => 'تست', 'slug' => 'auth-test', 'content' => 'محتوا']);

        $this->assertTrue($this->superAdmin->can('viewAny', BlogPost::class));
        $this->assertTrue($this->superAdmin->can('create', BlogPost::class));
        $this->assertTrue($this->superAdmin->can('update', $post));
        $this->assertTrue($this->superAdmin->can('delete', $post));
    }

    public function test_editor_can_manage_blog(): void
    {
        $post = BlogPost::create(['user_id' => $this->editor->id, 'title' => 'تست', 'slug' => 'editor-test', 'content' => 'محتوا']);

        $this->assertTrue($this->editor->can('viewAny', BlogPost::class));
        $this->assertTrue($this->editor->can('create', BlogPost::class));
        $this->assertTrue($this->editor->can('update', $post));
    }

    public function test_plain_user_cannot_manage_blog(): void
    {
        $post = BlogPost::create(['user_id' => $this->author->id, 'title' => 'تست', 'slug' => 'user-test', 'content' => 'محتوا']);

        $this->assertFalse($this->plainUser->can('viewAny', BlogPost::class));
        $this->assertFalse($this->plainUser->can('create', BlogPost::class));
        $this->assertFalse($this->plainUser->can('delete', $post));
    }

    // ===== تست دسترسی مسیرها =====

    public function test_guest_is_redirected_from_blog(): void
    {
        $this->get('/admin/blog')->assertRedirect('/admin/login');
    }

    public function test_admin_can_access_blog_pages(): void
    {
        $this->actingAs($this->superAdmin, 'admin');

        $post = BlogPost::create(['user_id' => $this->author->id, 'title' => 'تست', 'slug' => 'route-test', 'content' => 'محتوا']);

        $this->get('/admin/blog')->assertOk();
        $this->get('/admin/blog/create')->assertOk();
        $this->get('/admin/blog/'.$post->id)->assertOk();
        $this->get('/admin/blog/'.$post->id.'/edit')->assertOk();
        $this->get('/admin/blog/categories')->assertOk();
        $this->get('/admin/blog/tags')->assertOk();
        $this->get('/admin/blog/comments')->assertOk();
    }

    public function test_plain_user_is_forbidden_from_blog(): void
    {
        $this->actingAs($this->plainUser);

        // قانون پروژه: دسترسی غیرمجاز = فقط صفحه «یافت نشد» (404)
        $this->get('/admin/blog')->assertNotFound();
        $this->get('/admin/blog/tags')->assertNotFound();
        $this->get('/admin/blog/comments')->assertNotFound();
    }

    // ===== تست Service =====

    public function test_blog_service_toggle_status_sets_published_at(): void
    {
        $service = app(BlogService::class);
        $post = BlogPost::create(['user_id' => $this->author->id, 'title' => 'تاگل', 'slug' => 'toggle-test', 'content' => 'محتوا', 'status' => 'draft']);

        $service->toggleStatus($post);
        $this->assertEquals('published', $post->fresh()->status);
        $this->assertNotNull($post->fresh()->published_at);

        $service->toggleStatus($post);
        $this->assertEquals('draft', $post->fresh()->status);
        // تاریخ انتشار حفظ می‌شود
        $this->assertNotNull($post->fresh()->published_at);
    }

    public function test_blog_service_search_and_filter(): void
    {
        $service = app(BlogService::class);

        BlogPost::create(['user_id' => $this->author->id, 'title' => 'آموزش لاراول', 'slug' => 'laravel-tutorial', 'content' => 'محتوا', 'status' => 'published']);
        BlogPost::create(['user_id' => $this->author->id, 'title' => 'تجربیات کاری', 'slug' => 'work-exp', 'content' => 'محتوا', 'status' => 'draft']);

        // جستجو
        $this->assertEquals(1, $service->list(['search' => 'لاراول'])->total());

        // فیلتر وضعیت
        $this->assertEquals(1, $service->list(['status' => 'published'])->total());
        $this->assertEquals(1, $service->list(['status' => 'draft'])->total());
    }

    public function test_blog_service_syncs_categories_and_tags(): void
    {
        $service = app(BlogService::class);

        $cat = Category::create(['name' => 'تست دسته', 'slug' => 'test-cat']);
        $tag = Tag::create(['name' => 'تست برچسب', 'slug' => 'test-tag']);

        $post = new BlogPost;
        $saved = $service->save($post, [
            'user_id' => $this->author->id,
            'title' => 'همگام‌سازی',
            'slug' => 'sync-test',
            'content' => 'محتوا',
            'status' => 'published',
            'categories' => [$cat->id],
            'tags' => [$tag->id],
        ]);

        $this->assertEquals(1, $saved->categories->count());
        $this->assertEquals(1, $saved->tags->count());
        $this->assertEquals('تست دسته', $saved->categories->first()->name);
    }

    public function test_blog_service_comments_filter_and_status(): void
    {
        $service = app(BlogService::class);

        $post = BlogPost::create(['user_id' => $this->author->id, 'title' => 'نظرات', 'slug' => 'comments-test', 'content' => 'محتوا', 'status' => 'published']);
        $comment1 = Comment::create(['user_id' => $this->plainUser->id, 'blog_post_id' => $post->id, 'content' => 'نظر اول', 'status' => 'pending']);
        $comment2 = Comment::create(['user_id' => $this->plainUser->id, 'blog_post_id' => $post->id, 'content' => 'نظر دوم', 'status' => 'approved']);

        // فیلتر وضعیت
        $this->assertEquals(1, $service->commentsList(['status' => 'pending'])->total());
        $this->assertEquals(1, $service->commentsList(['status' => 'approved'])->total());

        // تغییر وضعیت
        $service->setCommentStatus($comment1, 'approved');
        $this->assertEquals('approved', $comment1->fresh()->status);
    }
}
