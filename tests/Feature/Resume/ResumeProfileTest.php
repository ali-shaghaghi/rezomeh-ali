<?php

namespace Tests\Feature\Resume;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\Core\Models\Role;
use Modules\Resume\Database\Seeders\ResumePermissionSeeder;
use Modules\Resume\Livewire\Admin\ResumeManager;
use Modules\Resume\Models\ResumeProfile;
use Modules\Resume\Services\ResumeService;
use Tests\TestCase;

class ResumeProfileTest extends TestCase
{
    use RefreshDatabase;

    protected \App\Models\Admin $admin;

    protected User $plainUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        $this->seed(ResumePermissionSeeder::class);

        $superRole = Role::where('slug', 'super-admin')->firstOrFail();
        $userRole = Role::where('slug', 'user')->firstOrFail();

        $this->admin = $this->makeAdmin('resume-admin@test.com', 'super-admin');

        $this->plainUser = User::create(['name' => 'Plain', 'email' => 'plain@test.com', 'password' => 'password', 'is_active' => true]);
        $this->plainUser->roles()->attach($userRole);
    }

    // ===== مدل =====

    public function test_profile_current_creates_default_singleton(): void
    {
        $profile = ResumeProfile::current();

        $this->assertInstanceOf(ResumeProfile::class, $profile);
        $this->assertTrue($profile->is_active);
        $this->assertNotEmpty($profile->name);

        // فراخوانی مجدد همان رکورد را برمی‌گرداند (singleton)
        $this->assertEquals($profile->id, ResumeProfile::current()->id);
        $this->assertEquals(1, ResumeProfile::count());
    }

    public function test_profile_typewriter_texts_cast(): void
    {
        $profile = ResumeProfile::create([
            'name' => 'تست',
            'job_title' => 'توسعه‌دهنده',
            'typewriter_texts' => ['عنوان ۱', 'عنوان ۲'],
        ]);

        $this->assertIsArray($profile->typewriter_texts);
        $this->assertCount(2, $profile->typewriter_texts);
    }

    // ===== سرویس =====

    public function test_resume_service_profile_and_save(): void
    {
        $service = app(ResumeService::class);

        $profile = $service->saveProfile([
            'name' => 'علی شقاقی',
            'job_title' => 'توسعه‌دهنده ارشد',
            'location' => 'تهران',
            'typewriter_texts' => ['توسعه‌دهنده', 'طراح'],
        ]);

        $this->assertEquals('علی شقاقی', $profile->name);
        $this->assertEquals('توسعه‌دهنده ارشد', $profile->job_title);
        $this->assertEquals(['توسعه‌دهنده', 'طراح'], $profile->typewriter_texts);

        // visibleItems شامل پروفایل است
        $visible = $service->visibleItems();
        $this->assertInstanceOf(ResumeProfile::class, $visible['profile']);
        $this->assertEquals('علی شقاقی', $visible['profile']->name);
    }

    // ===== Livewire =====

    public function test_resume_manager_renders_profile_card(): void
    {
        ResumeProfile::current()->update([
            'name' => 'علی شقاقی',
            'job_title' => 'توسعه‌دهنده وب',
            'location' => 'تهران، ایران',
            'email' => 'hello@alishaghaghi.ir',
            'bio' => 'توسعه‌دهنده وب با تجربه',
        ]);

        Livewire::actingAs($this->admin, 'admin')
            ->test(ResumeManager::class)
            ->assertSee('علی شقاقی')
            ->assertSee('توسعه‌دهنده وب')
            ->assertSee('تهران، ایران')
            ->assertSee('hello@alishaghaghi.ir')
            ->assertSee('ویرایش پروفایل');
    }

    public function test_resume_manager_edit_and_save_profile(): void
    {
        $component = Livewire::actingAs($this->admin, 'admin')
            ->test(ResumeManager::class)
            ->call('editProfile')
            ->assertSet('profileEditing', true);

        $component
            ->set('profile_name', 'علی شقاقی')
            ->set('profile_job_title', 'توسعه‌دهنده ارشد وب')
            ->set('profile_location', 'تهران، ایران')
            ->set('profile_email', 'hello@alishaghaghi.ir')
            ->set('profile_github_url', 'https://github.com/ali-shaghaghi')
            ->set('profile_typewriter_texts', "توسعه‌دهنده Laravel\nمتخصص Livewire")
            ->set('profile_bio', 'بایوی تستی')
            ->call('saveProfile')
            ->assertSet('profileEditing', false);

        $profile = ResumeProfile::current();
        $this->assertEquals('علی شقاقی', $profile->name);
        $this->assertEquals('توسعه‌دهنده ارشد وب', $profile->job_title);
        $this->assertEquals('تهران، ایران', $profile->location);
        $this->assertEquals(['توسعه‌دهنده Laravel', 'متخصص Livewire'], $profile->typewriter_texts);
        $this->assertEquals('بایوی تستی', $profile->bio);
    }

    public function test_resume_manager_profile_validation(): void
    {
        Livewire::actingAs($this->admin, 'admin')
            ->test(ResumeManager::class)
            ->call('editProfile')
            ->set('profile_name', '')
            ->set('profile_job_title', '')
            ->set('profile_email', 'not-an-email')
            ->set('profile_github_url', 'not-a-url')
            ->call('saveProfile')
            ->assertHasErrors(['profile_name', 'profile_job_title', 'profile_email', 'profile_github_url']);
    }

    public function test_resume_manager_profile_requires_permission(): void
    {
        $this->actingAs($this->plainUser);
        $this->assertFalse($this->plainUser->hasAnyPermission(['manage_resume', 'view_resume']));

        // کاربر عادی به هاب رزومه دسترسی ندارد — فقط صفحه «یافت نشد» (404)
        $this->get('/admin/resume')->assertNotFound();
    }

    public function test_resume_page_shows_animated_stat_structure(): void
    {
        // صفحه رزومه باید کارت‌های آمار انیمیشنی (الگوی داشبورد) را داشته باشد
        $response = $this->actingAs($this->admin, 'admin')->get('/admin/resume');
        $response->assertOk();

        // data-count برای شمارنده انیمیشنی در کامپوننت رندر می‌شود
        $this->assertStringContainsString('data-count', $response->getContent());
    }
}
