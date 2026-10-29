<?php

namespace Tests\Feature\Public;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Models\BlogPost;
use Modules\Core\Models\Category;
use Modules\Core\Models\Comment;
use Modules\Core\Models\Tag;
use Modules\Resume\Database\Seeders\ResumePermissionSeeder;
use Modules\Resume\Models\Education;
use Modules\Resume\Models\Experience;
use Modules\Resume\Models\Project;
use Modules\Resume\Models\ResumeProfile;
use Modules\Resume\Models\Skill;
use Modules\Resume\Models\Technology;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        $this->seed(ResumePermissionSeeder::class);
    }

    private function seedResumeContent(): void
    {
        ResumeProfile::current()->update([
            'name' => 'علی شقاقی',
            'job_title' => 'توسعه‌دهنده وب',
            'bio' => 'توسعه‌دهنده فول‌استک',
            'location' => 'تهران',
            'email' => 'ali@example.com',
            'typewriter_texts' => ['توسعه‌دهنده وب', 'طراح رابط کاربری'],
        ]);

        Skill::create(['name' => 'Laravel', 'slug' => 'laravel', 'proficiency' => 90, 'is_active' => true, 'show_in_resume' => true]);
        Technology::create(['name' => 'PHP', 'slug' => 'php']);
        Experience::create(['company' => 'شرکت نمونه', 'position' => 'برنامه‌نویس', 'start_date' => '2020-01-01', 'is_current' => true, 'is_active' => true, 'show_in_resume' => true]);
        Project::create(['title' => 'پروژه نمونه', 'slug' => 'sample-project', 'description' => 'توضیح پروژه', 'status' => 'published']);
    }

    public function test_home_page_renders_with_resume_content(): void
    {
        $this->seedResumeContent();

        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('علی شقاقی')
            ->assertSee('توسعه‌دهنده فول‌استک')
            ->assertSee('Laravel')
            ->assertSee('شرکت نمونه')
            ->assertSee('پروژه نمونه')
            ->assertSee('id="skills"', false)
            ->assertSee('id="experience"', false)
            ->assertSee('id="projects"', false)
            // بخش‌های جدید صفحه اصلی (طبق بازطراحی): بنر سفارش، محصولات، مشاوره
            ->assertSee('id="order-project"', false)
            ->assertSee('id="products"', false)
            ->assertSee('id="consultation"', false)
            // بخش قدیمی «همکاری کنیم؟» حذف شده است
            ->assertDontSee('id="contact"', false);
    }

    public function test_home_page_renders_without_any_content(): void
    {
        // پروفایل singleton خودکار ساخته می‌شود — صفحه نباید ۵۰۰ بدهد
        $response = $this->get('/');

        $response->assertOk();
    }

    public function test_home_page_shows_only_published_projects(): void
    {
        $this->seedResumeContent();
        Project::create(['title' => 'پروژه پیش‌نویس', 'slug' => 'draft-project', 'description' => 'x', 'status' => 'draft']);

        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('پروژه نمونه')
            ->assertDontSee('پروژه پیش‌نویس');
    }

    public function test_home_page_shows_latest_published_posts(): void
    {
        $this->seedResumeContent();
        $user = User::factory()->create();
        BlogPost::create(['user_id' => $user->id, 'title' => 'مقاله منتشرشده', 'slug' => 'published-post', 'excerpt' => 'خلاصه', 'content' => 'محتوا', 'status' => 'published', 'published_at' => now()]);

        $response = $this->get('/');

        $response->assertOk()->assertSee('مقاله منتشرشده');
    }

    public function test_blog_index_lists_only_published_posts(): void
    {
        $user = User::factory()->create();
        BlogPost::create(['user_id' => $user->id, 'title' => 'مقاله عمومی', 'slug' => 'public-post', 'excerpt' => 'خلاصه', 'content' => 'محتوا', 'status' => 'published', 'published_at' => now()]);
        BlogPost::create(['user_id' => $user->id, 'title' => 'مقاله خصوصی', 'slug' => 'private-post', 'excerpt' => 'خلاصه', 'content' => 'محتوا', 'status' => 'draft']);

        $response = $this->get('/blog');

        $response->assertOk()
            ->assertSee('مقاله عمومی')
            ->assertDontSee('مقاله خصوصی');
    }

    public function test_blog_index_filters_by_category(): void
    {
        $user = User::factory()->create();
        $cat = Category::create(['name' => 'دسته وب', 'slug' => 'web']);
        $post = BlogPost::create(['user_id' => $user->id, 'title' => 'پست دسته‌دار', 'slug' => 'cat-post', 'excerpt' => 'x', 'content' => 'y', 'status' => 'published', 'published_at' => now()]);
        $post->categories()->attach($cat->id);

        $response = $this->get('/blog?category='.$cat->id);

        $response->assertOk()->assertSee('پست دسته‌دار');

        $response2 = $this->get('/blog?category=99999');

        $response2->assertOk()->assertDontSee('پست دسته‌دار');
    }

    public function test_blog_show_displays_post_and_increments_views(): void
    {
        $user = User::factory()->create();
        $post = BlogPost::create(['user_id' => $user->id, 'title' => 'عنوان مقاله', 'slug' => 'test-slug', 'excerpt' => 'خلاصه', 'content' => 'محتوای کامل', 'status' => 'published', 'published_at' => now(), 'views' => 0]);

        $response = $this->get('/blog/test-slug');

        $response->assertOk()
            ->assertSee('عنوان مقاله')
            ->assertSee('محتوای کامل');

        $this->assertDatabaseHas('blog_posts', ['id' => $post->id, 'views' => 1]);
    }

    public function test_blog_show_returns_404_for_unknown_slug(): void
    {
        $this->get('/blog/unknown-slug')->assertNotFound();
    }

    public function test_blog_show_returns_404_for_draft_post(): void
    {
        $user = User::factory()->create();
        BlogPost::create(['user_id' => $user->id, 'title' => 'پیش‌نویس', 'slug' => 'draft-slug', 'excerpt' => 'x', 'content' => 'y', 'status' => 'draft']);

        $this->get('/blog/draft-slug')->assertNotFound();
    }

    public function test_blog_show_displays_only_approved_comments(): void
    {
        $user = User::factory()->create();
        $post = BlogPost::create(['user_id' => $user->id, 'title' => 'پست', 'slug' => 'comments-post', 'excerpt' => 'x', 'content' => 'y', 'status' => 'published', 'published_at' => now()]);

        Comment::create(['user_id' => $user->id, 'blog_post_id' => $post->id, 'content' => 'نظر تأییدشده', 'status' => 'approved']);
        Comment::create(['user_id' => $user->id, 'blog_post_id' => $post->id, 'content' => 'نظر در انتظار', 'status' => 'pending']);

        $response = $this->get('/blog/comments-post');

        $response->assertOk()
            ->assertSee('نظر تأییدشده')
            ->assertDontSee('نظر در انتظار');
    }

    public function test_guest_cannot_submit_comment(): void
    {
        $user = User::factory()->create();
        BlogPost::create(['user_id' => $user->id, 'title' => 'پست', 'slug' => 'guest-post', 'excerpt' => 'x', 'content' => 'y', 'status' => 'published', 'published_at' => now()]);

        $response = $this->post('/blog/guest-post/comment', ['content' => 'نظر مهمان']);

        // قانون پروژه: مهمان اجازه ثبت نظر ندارد — فقط صفحه «یافت نشد» (404)
        $response->assertNotFound();
        $this->assertDatabaseCount('comments', 0);
    }

    public function test_authenticated_user_can_submit_comment(): void
    {
        $user = User::factory()->create();
        $post = BlogPost::create(['user_id' => $user->id, 'title' => 'پست نظر', 'slug' => 'comment-post', 'excerpt' => 'x', 'content' => 'y', 'status' => 'published', 'published_at' => now()]);

        $response = $this->actingAs($user)->post('/blog/comment-post/comment', ['content' => 'نظر کاربر']);

        $response->assertRedirect();
        $this->assertDatabaseHas('comments', [
            'blog_post_id' => $post->id,
            'user_id' => $user->id,
            'content' => 'نظر کاربر',
            'status' => 'pending',
        ]);
    }

    public function test_comment_validation_requires_content(): void
    {
        $user = User::factory()->create();
        BlogPost::create(['user_id' => $user->id, 'title' => 'پست', 'slug' => 'validate-post', 'excerpt' => 'x', 'content' => 'y', 'status' => 'published', 'published_at' => now()]);

        // نکته: session('errors') بسته به نسخه لاراول ممکن است ViewErrorBag یا
        // آرایه‌ای با شکل ['default' => ['messages' => [...]]] برگرداند
        $hasContentError = function () {
            $errors = session('errors');

            if ($errors instanceof \Illuminate\Support\ViewErrorBag) {
                return $errors->has('content');
            }

            if (is_array($errors)) {
                foreach ($errors as $bag) {
                    $messages = is_array($bag) ? ($bag['messages'] ?? $bag) : [];

                    if (array_key_exists('content', $messages)) {
                        return true;
                    }
                }
            }

            return false;
        };

        // نظر خالی → رد
        $this->actingAs($user)->post('/blog/validate-post/comment', ['content' => '']);
        $this->assertTrue($hasContentError(), 'خطای اعتبارسنجی content ثبت نشد');

        // نظر کوتاه (کمتر از ۵ کاراکتر) → رد
        $this->actingAs($user)->post('/blog/validate-post/comment', ['content' => 'abc']);
        $this->assertTrue($hasContentError(), 'خطای min:5 ثبت نشد');

        $this->assertDatabaseCount('comments', 0);
    }

    public function test_contact_form_component_validates_input(): void
    {
        $this->seedResumeContent();

        \Livewire\Livewire::test(\Modules\Public\Livewire\ContactForm::class)
            ->set('name', 'ع')
            ->set('email', 'invalid-email')
            ->set('subject', '')
            ->set('message', 'short')
            ->call('send')
            ->assertHasErrors(['name', 'email', 'subject', 'message']);
    }

    public function test_contact_form_component_sends_mail(): void
    {
        $this->seedResumeContent();

        // با MAIL_MAILER=array (phpunit.xml) ارسال واقعی خطا نمی‌دهد
        \Livewire\Livewire::test(\Modules\Public\Livewire\ContactForm::class)
            ->set('name', 'کاربر تست')
            ->set('email', 'user@example.com')
            ->set('subject', 'موضوع تست')
            ->set('message', 'این یک پیام آزمایشی است')
            ->call('send')
            ->assertSet('sent', true)
            ->assertHasNoErrors();
    }
}
