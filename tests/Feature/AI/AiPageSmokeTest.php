<?php

namespace Tests\Feature\AI;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Modules\AI\Livewire\Admin\ArticleWriter;
use Modules\AI\Livewire\Admin\ProjectAssistant;
use Modules\AI\Livewire\Admin\TelegramPublisher;
use Modules\Core\Models\BlogPost;
use Modules\Core\Models\Role;
use Modules\Notifications\Database\Seeders\NotificationsPermissionSeeder;
use Tests\TestCase;

class AiPageSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected \App\Models\Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        $this->seed(NotificationsPermissionSeeder::class);

        $admin = $this->makeAdmin('ai-admin@test.com', 'super-admin');
        $this->admin = $admin;

        $this->actingAs($admin, 'admin');
    }

    public function test_ai_center_page_renders(): void
    {
        $this->get(route('admin.ai.index'))->assertOk();
    }

    public function test_article_writer_component_renders(): void
    {
        Livewire::test(ArticleWriter::class)->assertOk();
    }

    public function test_project_assistant_component_renders(): void
    {
        Livewire::test(ProjectAssistant::class)->assertOk();
    }

    public function test_telegram_publisher_component_renders(): void
    {
        Livewire::test(TelegramPublisher::class)->assertOk();
    }

    // ===== تست اتصال تلگرام — خطای شبکه نباید صفحه را بترکاند =====

    public function test_connection_test_without_token_shows_friendly_message(): void
    {
        Livewire::test(TelegramPublisher::class)
            ->call('testConnection')
            ->assertSet('connectionMessage', 'ابتدا Bot Token را وارد و ذخیره کنید.')
            ->assertOk();
    }

    public function test_connection_test_survives_network_failure(): void
    {
        Http::fake(function (): void {
            throw new ConnectionException('cURL error 7: Failed to connect');
        });

        Livewire::test(TelegramPublisher::class)
            ->set('botToken', '123456:ABC-defGhIJKlmNoPQRsTUVwxyZ')
            ->call('testConnection')
            ->assertOk()
            ->assertSet('connectionMessage', 'دسترسی به سرور تلگرام ممکن نیست — اتصال اینترنت/پروکسی را بررسی کنید.');
    }

    // ===== انتشار — کپشن باید برای HTML ایمن شود =====

    public function test_publish_escapes_ai_caption_for_html_parse_mode(): void
    {
        Http::fake([
            'api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 1]]),
        ]);

        $captured = null;
        Http::fake(function (Request $request) use (&$captured) {
            $captured = $request['text'];

            return Http::response(['ok' => true]);
        });

        Livewire::test(TelegramPublisher::class)
            ->set('botToken', '123456:ABC-defGhIJKlmNoPQRsTUVwxyZ')
            ->set('chatId', '@mychannel')
            ->call('saveConnection')
            ->set('caption', 'تخفیف ویژه <50%> برای مشتریان & دوستان!')
            ->call('publish')
            ->assertSet('successMessage', 'پست با موفقیت در تلگرام منتشر شد.');

        $this->assertStringNotContainsString('<50%>', (string) $captured);
        $this->assertStringContainsString('&lt;50%&gt;', (string) $captured);
    }

    public function test_publish_without_connection_shows_friendly_error(): void
    {
        Livewire::test(TelegramPublisher::class)
            ->set('contentType', 'latest_post')
            ->call('publish')
            ->assertSet('errorMessage', 'ابتدا اتصال تلگرام را ذخیره کنید.');
    }

    // ===== ذخیره پیش‌نویس — مقاله تک‌زبانه + slug یکتا =====

    public function test_saves_en_only_draft(): void
    {
        $component = Livewire::test(ArticleWriter::class)
            ->set('result', json_encode([
                'title_en' => 'Laravel Testing Guide',
                'title_fa' => '',
                'content_en' => 'Body text',
            ], JSON_UNESCAPED_UNICODE))
            ->call('saveAsDraft');

        $this->assertDatabaseHas('blog_posts', [
            'slug' => 'laravel-testing-guide',
            'status' => 'draft',
            'title' => 'Laravel Testing Guide',
        ]);

        // نویسنده ادمین است: شناسه ادمین در admin_id و هرگز در user_id (FK به users) ثبت نمی‌شود
        $post = BlogPost::where('slug', 'laravel-testing-guide')->firstOrFail();
        $this->assertNull($post->user_id);
        $this->assertSame($this->admin->id, $post->admin_id);
    }

    public function test_saves_fa_draft_with_unique_slug_on_repeat(): void
    {
        $payload = json_encode(['title_fa' => 'راهنمای تست در لاراول', 'content_fa' => 'متن'], JSON_UNESCAPED_UNICODE);

        Livewire::test(ArticleWriter::class)->set('result', $payload)->call('saveAsDraft');
        Livewire::test(ArticleWriter::class)->set('result', $payload)->call('saveAsDraft');

        $this->assertEquals(2, BlogPost::where('title', 'راهنمای تست در لاراول')->count());

        $slugs = BlogPost::where('title', 'راهنمای تست در لاراول')->pluck('slug');
        $this->assertCount(2, $slugs->unique());
        $this->assertNotEquals('', $slugs->first());
    }
}
