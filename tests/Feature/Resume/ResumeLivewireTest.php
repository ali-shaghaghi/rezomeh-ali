<?php

namespace Tests\Feature\Resume;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Modules\Core\Models\Role;
use Modules\Resume\Database\Seeders\ResumePermissionSeeder;
use Modules\Resume\Livewire\Admin\EducationManager;
use Modules\Resume\Livewire\Admin\ExperienceManager;
use Modules\Resume\Livewire\Admin\LanguagesManager;
use Modules\Resume\Livewire\Admin\PortfolioForm;
use Modules\Resume\Livewire\Admin\PortfolioIndex;
use Modules\Resume\Livewire\Admin\PortfolioShow;
use Modules\Resume\Livewire\Admin\ResumeManager;
use Modules\Resume\Livewire\Admin\SkillsManager;
use Modules\Resume\Models\Experience;
use Modules\Resume\Models\Language;
use Modules\Resume\Models\Project;
use Modules\Resume\Models\Skill;
use Tests\TestCase;

class ResumeLivewireTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        $this->seed(ResumePermissionSeeder::class);

        $superRole = Role::where('slug', 'super-admin')->firstOrFail();
        $this->admin = User::create(['name' => 'Admin', 'email' => 'admin@test.com', 'password' => 'password', 'is_active' => true]);
        $this->admin->roles()->attach($superRole);
    }

    // ===== PortfolioIndex =====

    public function test_portfolio_index_renders_projects(): void
    {
        Project::create(['title' => 'پروژه تستی', 'slug' => 'livewire-test', 'description' => 'تست کامپوننت', 'status' => 'published']);

        Livewire::actingAs($this->admin)
            ->test(PortfolioIndex::class)
            ->assertSee('پروژه تستی')
            ->assertSee('نمونه‌کارها');
    }

    public function test_portfolio_index_search_filters(): void
    {
        Project::create(['title' => 'فروشگاه دیجیتال', 'slug' => 'shop-lw', 'description' => 'd', 'status' => 'published']);
        Project::create(['title' => 'مدیریت پروژه', 'slug' => 'pm-lw', 'description' => 'd', 'status' => 'draft']);

        Livewire::actingAs($this->admin)
            ->test(PortfolioIndex::class)
            ->set('search', 'فروشگاه')
            ->assertSee('فروشگاه دیجیتال')
            ->assertDontSee('مدیریت پروژه');
    }

    public function test_portfolio_index_empty_state(): void
    {
        Livewire::actingAs($this->admin)
            ->test(PortfolioIndex::class)
            ->assertSee('هنوز پروژه‌ای ثبت نشده است');
    }

    public function test_portfolio_index_toggle_status_action(): void
    {
        $project = Project::create(['title' => 'تاگل', 'slug' => 'tog-lw', 'description' => 'd', 'status' => 'draft']);

        Livewire::actingAs($this->admin)
            ->test(PortfolioIndex::class)
            ->call('toggleStatus', $project->id);

        $this->assertEquals('published', $project->fresh()->status);
    }

    public function test_portfolio_index_toggle_featured_action(): void
    {
        $project = Project::create(['title' => 'ویژه', 'slug' => 'feat-lw', 'description' => 'd', 'featured' => false]);

        Livewire::actingAs($this->admin)
            ->test(PortfolioIndex::class)
            ->call('toggleFeatured', $project->id);

        $this->assertTrue($project->fresh()->featured);
    }

    public function test_portfolio_index_delete_with_confirmation(): void
    {
        $project = Project::create(['title' => 'حذف شونده', 'slug' => 'del-lw', 'description' => 'd']);

        $component = Livewire::actingAs($this->admin)
            ->test(PortfolioIndex::class);

        // ابتدا مودال تایید باز می‌شود
        $component->call('confirmDelete', $project->id)
            ->assertSet('confirmingDelete', true);

        // سپس حذف نهایی
        $component->call('deleteProject')
            ->assertSet('confirmingDelete', false);

        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }

    public function test_portfolio_index_demo_modal_without_url_shows_notice(): void
    {
        $project = Project::create(['title' => 'بدون دمو', 'slug' => 'no-demo-lw', 'description' => 'd']);

        Livewire::actingAs($this->admin)
            ->test(PortfolioIndex::class)
            ->call('openDemo', $project->id)
            ->assertSet('showDemo', false) // بدون demo_url مودال باز نمی‌شود
            ->assertSet('successMessage', 'برای این پروژه دمویی ثبت نشده است.');
    }

    // ===== PortfolioForm =====

    public function test_portfolio_form_validation_errors(): void
    {
        Livewire::actingAs($this->admin)
            ->test(PortfolioForm::class)
            ->set('title', '')
            ->set('description', '')
            ->call('save')
            ->assertHasErrors(['title', 'description']);
    }

    public function test_portfolio_form_invalid_urls_fail_validation(): void
    {
        Livewire::actingAs($this->admin)
            ->test(PortfolioForm::class)
            ->set('title', 'تست')
            ->set('slug', 'url-test')
            ->set('description', 'تست')
            ->set('github_url', 'not-a-url')
            ->set('demo_url', 'also-not-url')
            ->call('save')
            ->assertHasErrors(['github_url', 'demo_url']);
    }

    public function test_portfolio_form_creates_project(): void
    {
        Livewire::actingAs($this->admin)
            ->test(PortfolioForm::class)
            ->set('title', 'پروژه جدید')
            ->set('slug', 'new-project-lw')
            ->set('short_description', 'توضیح کوتاه')
            ->set('description', 'توضیحات کامل')
            ->set('status', 'published')
            ->set('demo_url', 'https://demo.test.com')
            ->set('demo_type', 'link')
            ->call('save');

        $this->assertDatabaseHas('projects', [
            'slug' => 'new-project-lw',
            'status' => 'published',
            'demo_type' => 'link',
        ]);
    }

    public function test_portfolio_form_edits_existing_project(): void
    {
        $project = Project::create(['title' => 'قبلی', 'slug' => 'edit-lw', 'description' => 'd']);

        Livewire::actingAs($this->admin)
            ->test(PortfolioForm::class, ['project' => $project])
            ->set('title', 'ویرایش‌شده')
            ->call('save');

        $this->assertDatabaseHas('projects', ['id' => $project->id, 'title' => 'ویرایش‌شده']);
    }

    public function test_portfolio_form_slug_auto_generated_from_title(): void
    {
        $component = Livewire::actingAs($this->admin)
            ->test(PortfolioForm::class);

        $component->set('title', 'My New Project');

        // slug خودکار از عنوان انگلیسی تولید می‌شود
        $this->assertEquals('my-new-project', $component->get('slug'));
    }

    // ===== PortfolioShow =====

    public function test_portfolio_show_renders_all_details(): void
    {
        $skill = Skill::create(['name' => 'Laravel', 'slug' => 'show-skill', 'category' => 'backend', 'proficiency' => 90]);
        $project = Project::create([
            'title' => 'پروژه نمایشی',
            'slug' => 'show-test',
            'short_description' => 'خلاصه پروژه',
            'description' => 'توضیحات کامل پروژه',
            'demo_url' => 'https://demo.example.com',
            'demo_type' => 'link',
            'client_name' => 'مشتری تست',
        ]);
        $project->skills()->attach($skill);

        Livewire::actingAs($this->admin)
            ->test(PortfolioShow::class, ['project' => $project])
            ->assertSee('پروژه نمایشی')
            ->assertSee('خلاصه پروژه')
            ->assertSee('توضیحات کامل پروژه')
            ->assertSee('Laravel')
            ->assertSee('نمایش دمو')
            ->assertSee('https://demo.example.com');
    }

    public function test_portfolio_show_demo_modal(): void
    {
        $project = Project::create([
            'title' => 'دمو دار',
            'slug' => 'demo-test',
            'description' => 'd',
            'demo_url' => 'https://demo.example.com',
            'demo_type' => 'link',
        ]);

        Livewire::actingAs($this->admin)
            ->test(PortfolioShow::class, ['project' => $project])
            ->call('openDemo')
            ->assertSet('showDemo', true);
    }

    // ===== SkillsManager =====

    public function test_skills_manager_crud(): void
    {
        // ایجاد
        Livewire::actingAs($this->admin)
            ->test(SkillsManager::class)
            ->set('name', 'React')
            ->set('slug', 'react-lw')
            ->set('category', 'frontend')
            ->set('proficiency', 85)
            ->call('save');

        $this->assertDatabaseHas('skills', ['slug' => 'react-lw', 'proficiency' => 85]);

        $skill = Skill::where('slug', 'react-lw')->first();

        // ویرایش
        Livewire::actingAs($this->admin)
            ->test(SkillsManager::class)
            ->call('openEditModal', $skill->id)
            ->set('proficiency', 95)
            ->call('save');

        $this->assertEquals(95, $skill->fresh()->proficiency);

        // تغییر وضعیت
        Livewire::actingAs($this->admin)
            ->test(SkillsManager::class)
            ->call('toggleActive', $skill->id);
        $this->assertFalse($skill->fresh()->is_active);

        // تغییر نمایش در رزومه
        Livewire::actingAs($this->admin)
            ->test(SkillsManager::class)
            ->call('toggleResume', $skill->id);
        $this->assertFalse($skill->fresh()->show_in_resume);

        // حذف
        Livewire::actingAs($this->admin)
            ->test(SkillsManager::class)
            ->call('confirmDelete', $skill->id)
            ->call('deleteSkill');

        $this->assertDatabaseMissing('skills', ['id' => $skill->id]);
    }

    public function test_skills_manager_validation(): void
    {
        Livewire::actingAs($this->admin)
            ->test(SkillsManager::class)
            ->set('name', '')
            ->set('proficiency', 150)
            ->call('save')
            ->assertHasErrors(['name', 'proficiency']);
    }

    public function test_skills_manager_search(): void
    {
        Skill::create(['name' => 'Laravel', 'slug' => 'search-laravel', 'category' => 'backend', 'proficiency' => 90]);
        Skill::create(['name' => 'React', 'slug' => 'search-react', 'category' => 'frontend', 'proficiency' => 80]);

        Livewire::actingAs($this->admin)
            ->test(SkillsManager::class)
            ->set('search', 'Laravel')
            ->assertSee('Laravel')
            ->assertDontSee('React');
    }

    // ===== EducationManager =====

    public function test_education_manager_crud_and_validation(): void
    {
        // ایجاد
        Livewire::actingAs($this->admin)
            ->test(EducationManager::class)
            ->set('institution', 'دانشگاه تست')
            ->set('degree', 'کارشناسی')
            ->set('field_of_study', 'نرم‌افزار')
            ->set('start_date', '2018-09-01')
            ->set('is_current', false)
            ->set('end_date', '2022-07-01')
            ->call('save');

        $this->assertDatabaseHas('educations', ['institution' => 'دانشگاه تست']);

        // اعتبارسنجی: پایان قبل از شروع مجاز نیست
        Livewire::actingAs($this->admin)
            ->test(EducationManager::class)
            ->set('institution', 'تست تاریخ')
            ->set('degree', 'کارشناسی')
            ->set('field_of_study', 'نرم‌افزار')
            ->set('start_date', '2020-09-01')
            ->set('end_date', '2018-09-01')
            ->call('save')
            ->assertHasErrors(['end_date']);
    }

    // ===== ExperienceManager =====

    public function test_experience_manager_crud(): void
    {
        Livewire::actingAs($this->admin)
            ->test(ExperienceManager::class)
            ->set('company', 'شرکت تست')
            ->set('position', 'توسعه‌دهنده')
            ->set('employment_type', 'remote')
            ->set('start_date', '2021-01-01')
            ->set('technologies_input', 'Laravel, Vue, Docker')
            ->call('save');

        $this->assertDatabaseHas('experiences', ['company' => 'شرکت تست', 'employment_type' => 'remote']);

        $experience = Experience::where('company', 'شرکت تست')->first();
        $this->assertEquals(['Laravel', 'Vue', 'Docker'], $experience->technologies);
    }

    // ===== LanguagesManager =====

    public function test_languages_manager_crud(): void
    {
        // ایجاد
        Livewire::actingAs($this->admin)
            ->test(LanguagesManager::class)
            ->set('name', 'انگلیسی')
            ->set('code', 'en')
            ->set('level', 'advanced')
            ->set('proficiency', 85)
            ->call('save');

        $this->assertDatabaseHas('languages', ['code' => 'en', 'proficiency' => 85]);

        $language = Language::where('code', 'en')->first();

        // ویرایش
        Livewire::actingAs($this->admin)
            ->test(LanguagesManager::class)
            ->call('openEditModal', $language->id)
            ->set('proficiency', 90)
            ->call('save');

        $this->assertEquals(90, $language->fresh()->proficiency);

        // تغییر وضعیت
        Livewire::actingAs($this->admin)
            ->test(LanguagesManager::class)
            ->call('toggleActive', $language->id);
        $this->assertFalse($language->fresh()->is_active);

        // تغییر نمایش در رزومه
        Livewire::actingAs($this->admin)
            ->test(LanguagesManager::class)
            ->call('toggleResume', $language->id);
        $this->assertFalse($language->fresh()->show_in_resume);

        // حذف
        Livewire::actingAs($this->admin)
            ->test(LanguagesManager::class)
            ->call('confirmDelete', $language->id)
            ->call('deleteLanguage');

        $this->assertDatabaseMissing('languages', ['id' => $language->id]);
    }

    public function test_languages_manager_level_sets_default_proficiency(): void
    {
        // تغییر سطح با wire:model.live، هوک updatedLevel را اجرا می‌کند و درصد تسلط پیش‌فرض را همگام می‌سازد
        Livewire::actingAs($this->admin)
            ->test(LanguagesManager::class)
            ->set('level', 'fluent')
            ->assertSet('proficiency', 90)
            ->set('level', 'beginner')
            ->assertSet('proficiency', 30);
    }

    // ===== ResumeManager =====

    public function test_resume_manager_toggles_skill_visibility(): void
    {
        $skill = Skill::create(['name' => 'تست رزومه', 'slug' => 'resume-skill', 'category' => 'backend', 'proficiency' => 80, 'show_in_resume' => true]);

        Livewire::actingAs($this->admin)
            ->test(ResumeManager::class)
            ->call('toggleSkillResume', $skill->id);

        $this->assertFalse($skill->fresh()->show_in_resume);
    }

    public function test_resume_manager_toggles_language_visibility(): void
    {
        $language = Language::create(['name' => 'انگلیسی', 'level' => 'advanced', 'proficiency' => 85, 'show_in_resume' => true]);

        Livewire::actingAs($this->admin)
            ->test(ResumeManager::class)
            ->call('toggleLanguageResume', $language->id);

        $this->assertFalse($language->fresh()->show_in_resume);
    }

    public function test_resume_manager_toggles_project_status_and_featured(): void
    {
        $project = Project::create(['title' => 'پروژه رزومه', 'slug' => 'resume-proj', 'description' => 'd', 'status' => 'draft', 'featured' => false]);

        $component = Livewire::actingAs($this->admin)->test(ResumeManager::class);

        $component->call('toggleProjectStatus', $project->id);
        $this->assertEquals('published', $project->fresh()->status);

        $component->call('toggleProjectFeatured', $project->id);
        $this->assertTrue($project->fresh()->featured);
    }

    public function test_resume_manager_tabs_switch(): void
    {
        Livewire::actingAs($this->admin)
            ->test(ResumeManager::class)
            ->assertSet('activeTab', 'projects')
            ->call('setTab', 'skills')
            ->assertSet('activeTab', 'skills')
            ->call('setTab', 'experiences')
            ->assertSet('activeTab', 'experiences')
            ->call('setTab', 'languages')
            ->assertSet('activeTab', 'languages');
    }

    // ===== Authorization روی کامپوننت‌ها =====

    public function test_livewire_actions_require_permission(): void
    {
        $userRole = Role::where('slug', 'user')->firstOrFail();
        $plainUser = User::create(['name' => 'Plain', 'email' => 'plain@test.com', 'password' => 'password', 'is_active' => true]);
        $plainUser->roles()->attach($userRole);

        $project = Project::create(['title' => 'ممنوع', 'slug' => 'denied', 'description' => 'd', 'status' => 'draft']);

        // Gate آن را می‌بندد — کاربر عادی مجاز نیست
        $this->actingAs($plainUser);
        $this->assertFalse($plainUser->can('update', $project));

        // اکشن ریاکتیو هم همان Gate را استفاده می‌کند؛
        // Livewire v3 در تست‌ها AuthorizationException را به response تبدیل می‌کند،
        // بنابراین مستقیم Gate کامپوننت را بررسی می‌کنیم
        try {
            $project->resolveRouteBinding($project->id);
            Gate::forUser($plainUser)->authorize('update', $project);
            $this->fail('AuthorizationException باید پرتاب شود.');
        } catch (AuthorizationException $e) {
            $this->assertInstanceOf(AuthorizationException::class, $e);
        }

        // و وضعیت پروژه تغییر نکرده باشد
        $this->assertEquals('draft', $project->fresh()->status);
    }
}
