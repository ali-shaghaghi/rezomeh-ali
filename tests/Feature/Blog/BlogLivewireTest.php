<?php

namespace Tests\Feature\Blog;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\Blog\Database\Seeders\BlogPermissionSeeder;
use Modules\Blog\Livewire\Admin\CommentsManager;
use Modules\Blog\Livewire\Admin\PostForm;
use Modules\Blog\Livewire\Admin\PostsIndex;
use Modules\Blog\Livewire\Admin\TagsManager;
use Modules\Core\Models\BlogPost;
use Modules\Core\Models\Comment;
use Modules\Core\Models\Role;
use Modules\Core\Models\Tag;
use Tests\TestCase;

class BlogLivewireTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        $this->seed(BlogPermissionSeeder::class);

        $superRole = Role::where('slug', 'super-admin')->firstOrFail();
        $this->admin = User::create(['name' => 'Admin', 'email' => 'admin@test.com', 'password' => 'password', 'is_active' => true]);
        $this->admin->roles()->attach($superRole);
    }

    // ===== PostsIndex =====

    public function test_posts_index_renders_posts(): void
    {
        BlogPost::create(['user_id' => $this->admin->id, 'title' => 'مقاله تستی', 'slug' => 'livewire-test', 'content' => 'تست کامپوننت', 'status' => 'published']);

        Livewire::actingAs($this->admin)
            ->test(PostsIndex::class)
            ->assertSee('مقاله تستی')
            ->assertSee('مقالات وبلاگ');
    }

    public function test_posts_index_search_filters(): void
    {
        BlogPost::create(['user_id' => $this->admin->id, 'title' => 'آموزش لاراول', 'slug' => 'laravel-lw', 'content' => 'd', 'status' => 'published']);
        BlogPost::create(['user_id' => $this->admin->id, 'title' => 'تجربیات شغلی', 'slug' => 'career-lw', 'content' => 'd', 'status' => 'draft']);

        Livewire::actingAs($this->admin)
            ->test(PostsIndex::class)
            ->set('search', 'لاراول')
            ->assertSee('آموزش لاراول')
            ->assertDontSee('تجربیات شغلی');
    }

    public function test_posts_index_empty_state(): void
    {
        Livewire::actingAs($this->admin)
            ->test(PostsIndex::class)
            ->assertSee('هنوز مقاله‌ای ثبت نشده است');
    }

    public function test_posts_index_toggle_status_action(): void
    {
        $post = BlogPost::create(['user_id' => $this->admin->id, 'title' => 'تاگل', 'slug' => 'tog-lw', 'content' => 'd', 'status' => 'draft']);

        Livewire::actingAs($this->admin)
            ->test(PostsIndex::class)
            ->call('toggleStatus', $post->id);

        $this->assertEquals('published', $post->fresh()->status);
        $this->assertNotNull($post->fresh()->published_at);
    }

    public function test_posts_index_delete_with_confirmation(): void
    {
        $post = BlogPost::create(['user_id' => $this->admin->id, 'title' => 'حذف شونده', 'slug' => 'del-lw', 'content' => 'd']);

        $component = Livewire::actingAs($this->admin)
            ->test(PostsIndex::class)
            ->call('confirmDelete', $post->id)
            ->assertSet('confirmingDelete', true)
            ->call('deletePost');

        $this->assertFalse($component->get('confirmingDelete'));
        $this->assertDatabaseMissing('blog_posts', ['id' => $post->id]);
    }

    // ===== PostForm =====

    public function test_post_form_creates_post(): void
    {
        Livewire::actingAs($this->admin)
            ->test(PostForm::class)
            ->set('title', 'مقاله جدید از فرم')
            ->set('slug', 'form-created')
            ->set('content', 'محتوای کامل مقاله تستی')
            ->set('status', 'published')
            ->call('save');

        $this->assertDatabaseHas('blog_posts', [
            'slug' => 'form-created',
            'status' => 'published',
        ]);

        $post = BlogPost::where('slug', 'form-created')->first();
        $this->assertNotNull($post->published_at);
    }

    public function test_post_form_generates_slug_from_title(): void
    {
        Livewire::actingAs($this->admin)
            ->test(PostForm::class)
            ->set('title', 'Laravel Tips')
            ->assertSet('slug', 'laravel-tips');
    }

    public function test_post_form_validation_requires_title(): void
    {
        Livewire::actingAs($this->admin)
            ->test(PostForm::class)
            ->set('title', '')
            ->call('save')
            ->assertHasErrors(['title' => 'required']);
    }

    public function test_post_form_rejects_duplicate_slug(): void
    {
        BlogPost::create(['user_id' => $this->admin->id, 'title' => 'موجود', 'slug' => 'dup-slug', 'content' => 'd']);

        Livewire::actingAs($this->admin)
            ->test(PostForm::class)
            ->set('title', 'تکراری')
            ->set('slug', 'dup-slug')
            ->set('content', 'محتوا')
            ->call('save')
            ->assertHasErrors(['slug' => 'unique']);
    }

    public function test_post_form_saves_english_version_and_seo(): void
    {
        Livewire::actingAs($this->admin)
            ->test(PostForm::class)
            ->set('title', 'مقاله دو‌زبانه')
            ->set('slug', 'bilingual-post')
            ->set('content', 'محتوای فارسی')
            ->set('status', 'published')
            ->set('title_en', 'Bilingual post')
            ->set('excerpt_en', 'English excerpt')
            ->set('content_en', 'English content')
            ->set('seo_title_en', 'English SEO title')
            ->set('seo_description_en', 'English SEO description')
            ->call('save');

        $post = BlogPost::where('slug', 'bilingual-post')->first();
        $this->assertNotNull($post);
        $this->assertSame('Bilingual post', $post->title_en);
        $this->assertSame('English excerpt', $post->excerpt_en);
        $this->assertSame('English content', $post->content_en);
        $this->assertSame('English SEO title', $post->seo_title_en);
        $this->assertSame('English SEO description', $post->seo_description_en);
        $this->assertTrue($post->hasEnglishVersion());
    }

    public function test_post_form_edit_prefills_english_fields(): void
    {
        $post = BlogPost::create([
            'user_id' => $this->admin->id,
            'title' => 'مقاله',
            'slug' => 'en-prefill',
            'content' => 'محتوا',
            'title_en' => 'Prefilled EN',
            'seo_title_en' => 'SEO EN',
        ]);

        Livewire::actingAs($this->admin)
            ->test(PostForm::class, ['post' => $post])
            ->assertSet('title_en', 'Prefilled EN')
            ->assertSet('seo_title_en', 'SEO EN');
    }

    public function test_localized_seo_falls_back_to_persian(): void
    {
        $post = BlogPost::create([
            'user_id' => $this->admin->id,
            'title' => 'مقاله فارسی',
            'slug' => 'seo-fallback',
            'content' => 'محتوا',
            'seo_title' => 'عنوان سئو فارسی',
        ]);

        // No English SEO field set → English locale falls back to Persian
        app()->setLocale('en');
        $this->assertSame('عنوان سئو فارسی', $post->localizedSeoTitle());
        $this->assertFalse($post->hasEnglishVersion());

        // Persian locale uses Persian directly
        app()->setLocale('fa');
        $this->assertSame('عنوان سئو فارسی', $post->localizedSeoTitle());
    }

    // ===== TagsManager =====

    public function test_tags_manager_creates_tag(): void
    {
        Livewire::actingAs($this->admin)
            ->test(TagsManager::class)
            ->call('openCreateModal')
            ->set('name', 'برچسب تست')
            ->set('slug', 'test-tag-lw')
            ->call('save')
            ->assertNotSet('modalOpen', true);

        $this->assertDatabaseHas('tags', ['slug' => 'test-tag-lw']);
    }

    public function test_tags_manager_edit_and_delete(): void
    {
        $tag = Tag::create(['name' => 'قبل ویرایش', 'slug' => 'before-edit']);

        Livewire::actingAs($this->admin)
            ->test(TagsManager::class)
            ->call('openEditModal', $tag->id)
            ->assertSet('editing', true)
            ->set('name', 'بعد ویرایش')
            ->call('save');

        $this->assertEquals('بعد ویرایش', $tag->fresh()->name);

        Livewire::actingAs($this->admin)
            ->test(TagsManager::class)
            ->call('confirmDelete', $tag->id)
            ->call('deleteTag');

        $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
    }

    // ===== CommentsManager =====

    public function test_comments_manager_approves_and_rejects(): void
    {
        $post = BlogPost::create(['user_id' => $this->admin->id, 'title' => 'مقاله', 'slug' => 'cm-post', 'content' => 'd']);
        $comment = Comment::create(['user_id' => $this->admin->id, 'blog_post_id' => $post->id, 'content' => 'نظر تست', 'status' => 'pending']);

        Livewire::actingAs($this->admin)
            ->test(CommentsManager::class)
            ->assertSee('نظر تست')
            ->call('approve', $comment->id);

        $this->assertEquals('approved', $comment->fresh()->status);

        Livewire::actingAs($this->admin)
            ->test(CommentsManager::class)
            ->call('reject', $comment->id);

        $this->assertEquals('rejected', $comment->fresh()->status);
    }
}
