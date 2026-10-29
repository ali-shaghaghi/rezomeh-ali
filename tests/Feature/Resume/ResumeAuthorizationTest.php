<?php

namespace Tests\Feature\Resume;

use App\Models\Admin;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Models\Role;
use Modules\Resume\Database\Seeders\ResumePermissionSeeder;
use Modules\Resume\Models\Education;
use Modules\Resume\Models\Experience;
use Modules\Resume\Models\Language;
use Modules\Resume\Models\Project;
use Modules\Resume\Models\Skill;
use Modules\Resume\Services\PortfolioService;
use Modules\Resume\Services\ResumeService;
use Tests\TestCase;

class ResumeAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $superAdmin;

    protected User $editor;

    protected User $plainUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        $this->seed(ResumePermissionSeeder::class);

        // نقش‌های نمونه
        $superRole = Role::where('slug', 'super-admin')->firstOrFail();
        $editorRole = Role::where('slug', 'editor')->firstOrFail();
        $userRole = Role::where('slug', 'user')->firstOrFail();

        $this->superAdmin = $this->makeAdmin('super@test.com', 'super-admin');

        $this->editor = User::create(['name' => 'Editor', 'email' => 'editor@test.com', 'password' => 'password', 'is_active' => true]);
        $this->editor->roles()->attach($editorRole);

        $this->plainUser = User::create(['name' => 'User', 'email' => 'user@test.com', 'password' => 'password', 'is_active' => true]);
        $this->plainUser->roles()->attach($userRole);
    }

    // ===== تست Policies =====

    public function test_super_admin_can_manage_projects(): void
    {
        $project = Project::create(['title' => 'تست', 'slug' => 'auth-test', 'description' => 'd']);

        $this->assertTrue($this->superAdmin->can('viewAny', Project::class));
        $this->assertTrue($this->superAdmin->can('create', Project::class));
        $this->assertTrue($this->superAdmin->can('update', $project));
        $this->assertTrue($this->superAdmin->can('delete', $project));
    }

    public function test_editor_can_manage_resume_sections(): void
    {
        $skill = Skill::create(['name' => 'S', 'slug' => 's', 'category' => 'backend', 'proficiency' => 50]);

        $this->assertTrue($this->editor->can('viewAny', Skill::class));
        $this->assertTrue($this->editor->can('update', $skill));
        $this->assertTrue($this->editor->can('viewAny', Education::class));
        $this->assertTrue($this->editor->can('viewAny', Experience::class));
    }

    public function test_plain_user_cannot_manage_portfolio(): void
    {
        $project = Project::create(['title' => 'تست', 'slug' => 'user-test', 'description' => 'd']);

        $this->assertFalse($this->plainUser->can('viewAny', Project::class));
        $this->assertFalse($this->plainUser->can('create', Project::class));
        $this->assertFalse($this->plainUser->can('delete', $project));
        $this->assertFalse($this->plainUser->can('viewAny', Skill::class));
    }

    // ===== تست دسترسی مسیرها =====

    public function test_guest_is_redirected_from_portfolio(): void
    {
        $this->get('/admin/resume/portfolio')->assertRedirect('/admin/login');
    }

    public function test_admin_can_access_portfolio_pages(): void
    {
        $this->actingAs($this->superAdmin, 'admin');

        $this->get('/admin/resume/portfolio')->assertOk();
        $this->get('/admin/resume/portfolio/create')->assertOk();
        $this->get('/admin/resume/skills')->assertOk();
        $this->get('/admin/resume/education')->assertOk();
        $this->get('/admin/resume/experience')->assertOk();
        $this->get('/admin/resume/languages')->assertOk();
        $this->get('/admin/resume')->assertOk();
    }

    public function test_plain_user_is_forbidden_from_portfolio(): void
    {
        $this->actingAs($this->plainUser);

        // قانون پروژه: دسترسی غیرمجاز = فقط صفحه «یافت نشد» (404)
        $this->get('/admin/resume/portfolio')->assertNotFound();
        $this->get('/admin/resume/skills')->assertNotFound();
        $this->get('/admin/resume/languages')->assertNotFound();
        $this->get('/admin/resume')->assertNotFound();
    }

    // ===== تست Service: Publication / Featured / Visibility =====

    public function test_portfolio_service_toggle_status(): void
    {
        $service = app(PortfolioService::class);
        $project = Project::create(['title' => 'تاگل', 'slug' => 'toggle-test', 'description' => 'd', 'status' => 'draft']);

        $service->toggleStatus($project);
        $this->assertEquals('published', $project->fresh()->status);

        $service->toggleStatus($project);
        $this->assertEquals('draft', $project->fresh()->status);
    }

    public function test_portfolio_service_toggle_featured(): void
    {
        $service = app(PortfolioService::class);
        $project = Project::create(['title' => 'تاگل ویژه', 'slug' => 'toggle-feat', 'description' => 'd', 'featured' => false]);

        $service->toggleFeatured($project);
        $this->assertTrue($project->fresh()->featured);
    }

    public function test_portfolio_service_search_and_filter(): void
    {
        $service = app(PortfolioService::class);

        Project::create(['title' => 'فروشگاه اینترنتی', 'slug' => 'shop', 'description' => 'تست فروشگاه', 'status' => 'published']);
        Project::create(['title' => 'اپلیکیشن مدیریت', 'slug' => 'app', 'description' => 'تست اپ', 'status' => 'draft']);

        // جستجو
        $this->assertEquals(1, $service->list(['search' => 'فروشگاه'])->total());

        // فیلتر وضعیت
        $this->assertEquals(1, $service->list(['status' => 'published'])->total());
        $this->assertEquals(1, $service->list(['status' => 'draft'])->total());
    }

    // ===== تست ResumeService: Visibility =====

    public function test_resume_service_visible_items_respects_visibility(): void
    {
        $service = app(ResumeService::class);

        // پروژه منتشرشده + پیش‌نویس
        Project::create(['title' => 'منتشرشده', 'slug' => 'vis-pub', 'description' => 'd', 'status' => 'published']);
        Project::create(['title' => 'پیش‌نویس', 'slug' => 'vis-drf', 'description' => 'd', 'status' => 'draft']);

        // مهارت فعال/در رزومه + غیرفعال
        Skill::create(['name' => 'نمایشی', 'slug' => 'vis-skill', 'category' => 'backend', 'proficiency' => 90, 'is_active' => true, 'show_in_resume' => true]);
        Skill::create(['name' => 'مخفی', 'slug' => 'hid-skill', 'category' => 'backend', 'proficiency' => 90, 'is_active' => false, 'show_in_resume' => false]);

        // تحصیل خارج از رزومه
        Education::create(['institution' => 'مخفی', 'degree' => 'd', 'field_of_study' => 'f', 'start_date' => '2018-09-01', 'show_in_resume' => false]);
        Education::create(['institution' => 'نمایشی', 'degree' => 'd', 'field_of_study' => 'f', 'start_date' => '2019-09-01', 'show_in_resume' => true]);

        // تجربه فعال
        Experience::create(['company' => 'نمایشی', 'position' => 'dev', 'employment_type' => 'full-time', 'start_date' => '2020-01-01', 'is_active' => true, 'show_in_resume' => true]);

        // زبان فعال/در رزومه + غیرفعال
        Language::create(['name' => 'نمایشی', 'level' => 'advanced', 'proficiency' => 80, 'is_active' => true, 'show_in_resume' => true]);
        Language::create(['name' => 'مخفی', 'level' => 'beginner', 'proficiency' => 30, 'is_active' => false, 'show_in_resume' => false]);

        $visible = $service->visibleItems();

        // فقط پروژه منتشرشده در رزومه دیده می‌شود
        $this->assertEquals(1, $visible['projects']->count());
        $this->assertEquals('منتشرشده', $visible['projects']->first()->title);

        // فقط مهارت فعال و انتخاب‌شده
        $this->assertEquals(1, $visible['skills']->count());

        // فقط تحصیل انتخاب‌شده
        $this->assertEquals(1, $visible['educations']->count());

        // تجربه فعال و انتخاب‌شده
        $this->assertEquals(1, $visible['experiences']->count());

        // فقط زبان فعال و انتخاب‌شده
        $this->assertEquals(1, $visible['languages']->count());
    }
}
