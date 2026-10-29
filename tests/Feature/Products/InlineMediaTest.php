<?php

namespace Tests\Feature\Products;

use App\Models\Admin;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Modules\Blog\Livewire\Admin\PostForm;
use Modules\Core\Models\Role;
use Modules\Gallery\Models\Asset;
use Modules\Gallery\Services\GalleryService;
use Modules\Products\Enums\ProductType;
use Modules\Products\Livewire\Admin\ProductsIndex;
use Modules\Products\Models\Product;
use Tests\TestCase;

/**
 * تصاویر درون‌متنی مقاله/توضیحات محصول (#درخواست کاربر):
 * توکن قدیمی [[media|لایوت|مسیر]] پشتیبانی می‌شود؛ درج تصویر جدید فقط از
 * گالری است (لینک هش‌شده /gallery/{hash}) و آپلود درگ/پیست وارد گالری می‌شود.
 */
class InlineMediaTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        $superRole = Role::where('slug', 'super-admin')->firstOrFail();
        $this->admin = Admin::create([
            'name' => 'Media Admin',
            'email' => 'media-admin@test.com',
            'phone' => '09120009998',
            'password' => 'password',
            'is_active' => true,
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ]);
        $this->admin->roles()->attach($superRole);
    }

    // ===== هیلپر رندر =====

    public function test_render_inline_media_builds_layout_figures(): void
    {
        $html = render_inline_media(
            "بند اول\n[[media|full|storage/inline/full.jpg]]\n[[media|left|storage/inline/l.jpg]]\n[[media|right|storage/inline/r.jpg]]"
        );

        $this->assertStringContainsString('uc-media uc-media-full', $html);
        $this->assertStringContainsString('uc-media uc-media-left', $html);
        $this->assertStringContainsString('uc-media uc-media-right', $html);
        $this->assertStringContainsString(e(asset('storage/inline/full.jpg')), $html);
        // متن معمولی با nl2br رندر می‌شود و توکنی باقی نمی‌ماند
        $this->assertStringContainsString('<br', $html);
        $this->assertStringNotContainsString('[[media|', $html);
    }

    public function test_render_inline_media_escapes_text_but_keeps_figures(): void
    {
        $html = render_inline_media('<script>alert(1)</script> [[media|left|storage/inline/x.jpg]]');

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
        $this->assertStringContainsString('<figure', $html);
    }

    public function test_render_inline_media_ignores_non_storage_paths(): void
    {
        $html = render_inline_media('[[media|full|https://evil.example/x.jpg]]');

        // دامنهٔ خارجی هرگز به img تبدیل نمی‌شود — توکن فقط به‌صورت متن می‌ماند
        $this->assertStringNotContainsString('<figure', $html);
        $this->assertStringNotContainsString('evil.example', $html, 'مسیر خارجی نباید به خروجی راه یابد');
    }

    public function test_strip_inline_media_hides_tokens_from_previews(): void
    {
        $out = strip_inline_media('متن قبل [[media|full|storage/inline/a.jpg]] متن بعد');

        $this->assertStringNotContainsString('[[media|', $out);
        $this->assertStringContainsString('متن قبل', $out);
        $this->assertStringContainsString('متن بعد', $out);
    }

    // ===== آپلود فوری در ادمین =====

    public function test_product_description_upload_dispatches_event_and_stores_in_gallery(): void
    {
        Storage::fake(GalleryService::DISK);

        Livewire::actingAs($this->admin, 'admin')
            ->test(ProductsIndex::class)
            ->set('inlineUpload', [UploadedFile::fake()->image('shot.png', 120, 80)])
            ->assertDispatched('inline-media-uploaded')
            ->assertSet('inlineUpload', null);

        // فایل دیگر در storage خام ذخیره نمی‌شود — وارد گالری می‌شود با لینک هش‌شده
        $asset = Asset::firstOrFail();
        Storage::disk(GalleryService::DISK)->assertExists($asset->uploader_path);
        $this->assertStringContainsString('/gallery/' . $asset->public_hash, $asset->srcAttribute());
    }

    public function test_product_description_en_uses_separate_event(): void
    {
        Storage::fake(GalleryService::DISK);

        Livewire::actingAs($this->admin, 'admin')
            ->test(ProductsIndex::class)
            ->set('inlineUploadEn', [UploadedFile::fake()->image('en.png', 120, 80)])
            ->assertDispatched('inline-media-uploaded-en')
            ->assertSet('inlineUploadEn', null);

        $this->assertDatabaseCount('gallery_assets', 1);
    }

    public function test_blog_post_content_upload_dispatches_event_and_stores_in_gallery(): void
    {
        Storage::fake(GalleryService::DISK);

        Livewire::actingAs($this->admin, 'admin')
            ->test(PostForm::class)
            ->set('inlineUpload', [UploadedFile::fake()->image('post.jpg', 100, 100)])
            ->assertDispatched('inline-media-uploaded')
            ->assertSet('inlineUpload', null);

        $asset = Asset::firstOrFail();
        Storage::disk(GalleryService::DISK)->assertExists($asset->uploader_path);
        $this->assertNotNull($asset->public_hash);
    }

    public function test_blog_post_content_en_uses_separate_event(): void
    {
        Storage::fake(GalleryService::DISK);

        Livewire::actingAs($this->admin, 'admin')
            ->test(PostForm::class)
            ->set('inlineUploadEn', [UploadedFile::fake()->image('post-en.png', 100, 100)])
            ->assertDispatched('inline-media-uploaded-en')
            ->assertSet('inlineUploadEn', null);

        $this->assertDatabaseCount('gallery_assets', 1);
    }

    // ===== لایوت‌ها و محتوای HTML ویرایشگر غنی جدید (Quill) =====

    public function test_render_inline_media_supports_center_layout(): void
    {
        $html = render_inline_media('[[media|center|storage/inline/c.jpg]]');

        $this->assertStringContainsString('uc-media uc-media-center', $html);
        $this->assertStringContainsString(e(asset('storage/inline/c.jpg')), $html);
        $this->assertStringNotContainsString('[[media|', $html);
    }

    public function test_render_inline_media_outputs_rich_editor_html_unescaped(): void
    {
        $html = render_inline_media(
            '<p>متن مقاله</p><figure class="uc-media uc-media-left"><img src="/storage/inline/a.jpg" alt=""></figure>'
        );

        $this->assertStringContainsString('<p>متن مقاله</p>', $html);
        $this->assertStringContainsString('uc-media uc-media-left', $html);
        $this->assertStringNotContainsString('&lt;p&gt;', $html, 'HTML معتبر ویرایشگر نباید escape شود');
    }

    public function test_render_inline_media_sanitizes_rich_editor_html(): void
    {
        $html = render_inline_media(
            '<p>سلام</p><script>alert(1)</script><img src="x.jpg" onerror="alert(2)">'
        );

        $this->assertStringContainsString('<p>سلام</p>', $html);
        $this->assertStringNotContainsString('<script', $html, 'تگ اجرایی نباید به خروجی برسد');
        $this->assertStringNotContainsString('onerror', $html, 'رویداد inline نباید به خروجی برسد');
    }

    public function test_product_description_html_is_sanitized_on_save(): void
    {
        $product = Product::create([
            'name' => 'محصول تست ویرایشگر',
            'slug' => 'rich-editor-test-product',
            'price' => 1000,
            'type' => ProductType::Template->value,
            'description' => '<p>توضیح</p><script>alert(1)</script><a href="javascript:alert(2)">لینک</a>',
        ]);

        $this->assertStringNotContainsString('<script', $product->description);
        $this->assertStringNotContainsString('javascript:', $product->description);
        $this->assertStringContainsString('<p>توضیح</p>', $product->description);
    }

    public function test_rich_editor_component_renders_with_gallery_picker(): void
    {
        // در اپ واقعی $errors توسط middleware روی همه ویوها share می‌شود — همان‌جا اینجا شبیه‌سازی می‌شود
        \Illuminate\Support\Facades\View::share('errors', new \Illuminate\Support\ViewErrorBag(new \Illuminate\Support\MessageBag));

        $html = Blade::render('<x-rich-editor target="description" />');

        $this->assertStringContainsString('uc-rich-editor', $html);
        $this->assertStringContainsString('uc-editor-surface', $html);
        $this->assertStringContainsString('x-data="ucEditor', $html);
        // درج تصویر فقط از گالری — دکمهٔ آپلود محلی حذف شده
        $this->assertStringContainsString('گالری', $html);
        $this->assertStringNotContainsString('📷', $html, 'دکمهٔ آپلود محلی از ویرایشگر حذف شده');
        // لایوت‌های درخواستی کاربر در نوار ابزار هستند
        $this->assertStringContainsString('کامل', $html);
        $this->assertStringContainsString('وسط', $html);
        $this->assertStringContainsString('چپ', $html);
        $this->assertStringContainsString('راست', $html);
    }
}
