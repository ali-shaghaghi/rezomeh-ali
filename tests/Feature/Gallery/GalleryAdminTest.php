<?php

declare(strict_types=1);

namespace Tests\Feature\Gallery;

use App\Models\Admin;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Modules\Blog\Database\Seeders\BlogPermissionSeeder;
use Modules\Gallery\Livewire\Admin\AssetGrid;
use Modules\Gallery\Models\Asset;
use Modules\Gallery\Services\GalleryService;
use Tests\TestCase;

/**
 * تست‌های ماژول گالری: صفحهٔ ادمین، آپلود، لینک هش‌شده، ویرایش سئو، حذف.
 */
class GalleryAdminTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        $this->admin = $this->makeAdmin('gallery-admin@test.com', 'super-admin');
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/admin/gallery')->assertRedirect('/admin/login');
    }

    public function test_admin_can_view_gallery_page(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->get('/admin/gallery')
            ->assertOk()
            ->assertViewIs('gallery::admin.index')
            ->assertSee('گالری تصاویر');
    }

    public function test_gallery_link_exists_in_admin_sidebar(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->get('/admin/dashboard')
            ->assertOk()
            ->assertSee(route('admin.gallery.index'), false)
            ->assertSee('گالری تصاویر');
    }

    public function test_upload_creates_asset_with_hashed_public_url(): void
    {
        Storage::fake(GalleryService::DISK);

        Livewire::test(AssetGrid::class)
            ->set('uploads', [UploadedFile::fake()->create('photo.jpg', 64, 'image/jpeg')])
            ->assertSet('uploads', []);

        $asset = Asset::firstOrFail();

        $this->assertNotNull($asset->public_hash);
        $this->assertStringStartsWith('uploads/', $asset->uploader_path);
        $this->assertStringContainsString('/gallery/' . $asset->public_hash, $asset->srcAttribute());

        Storage::disk(GalleryService::DISK)->assertExists($asset->uploader_path);
    }

    public function test_public_hash_url_serves_the_file(): void
    {
        Storage::fake(GalleryService::DISK);

        $asset = app(GalleryService::class)->createFromUploadedFile(
            UploadedFile::fake()->create('served.jpg', 32, 'image/jpeg')
        );

        $this->get('/gallery/' . $asset->public_hash)->assertOk();

        $this->get('/gallery/' . str_repeat('ab', 32))->assertNotFound();
    }

    public function test_edit_saves_seo_fields_and_unpublishes(): void
    {
        Storage::fake(GalleryService::DISK);

        $asset = app(GalleryService::class)->createFromUploadedFile(
            UploadedFile::fake()->create('seo.jpg', 16, 'image/jpeg')
        );

        Livewire::test(AssetGrid::class)
            ->call('startEdit', $asset->id)
            ->assertSet('editingId', $asset->id)
            ->set('editAlt', 'توضیح جدید تصویر')
            ->set('editTitle', 'عنوان سئو')
            ->set('editDescription', 'توضیح سئو')
            ->set('editPublished', false)
            ->call('saveEdit')
            ->assertSet('editingId', null);

        $asset->refresh();

        $this->assertSame('توضیح جدید تصویر', $asset->alt);
        $this->assertSame('عنوان سئو', $asset->meta_title);
        $this->assertSame('توضیح سئو', $asset->meta_description);
        $this->assertNull($asset->public_hash);

        // انتشار مجدد → لینک هش‌شده فعال می‌شود
        Livewire::test(AssetGrid::class)->call('togglePublish', $asset->id);

        $this->assertNotNull($asset->refresh()->public_hash);
    }

    public function test_delete_removes_file_and_row(): void
    {
        Storage::fake(GalleryService::DISK);

        $asset = app(GalleryService::class)->createFromUploadedFile(
            UploadedFile::fake()->create('gone.jpg', 16, 'image/jpeg')
        );
        $path = $asset->uploader_path;

        Livewire::test(AssetGrid::class)
            ->call('confirmDelete', $asset->id)
            ->assertSet('confirmDeleteId', $asset->id)
            ->call('destroyAsset');

        $this->assertDatabaseMissing('gallery_assets', ['id' => $asset->id]);
        Storage::disk(GalleryService::DISK)->assertMissing($path);
    }

    public function test_blog_post_form_loads_with_gallery_editor(): void
    {
        $this->seed(BlogPermissionSeeder::class);

        $this->actingAs($this->admin, 'admin')
            ->get('/admin/blog/create')
            ->assertOk()
            ->assertSee('uc-rich-editor', false);
    }
}
