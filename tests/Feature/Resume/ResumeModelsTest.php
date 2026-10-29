<?php

namespace Tests\Feature\Resume;

use Carbon\CarbonInterface;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Models\Category;
use Modules\Resume\Database\Seeders\ResumePermissionSeeder;
use Modules\Resume\Models\Education;
use Modules\Resume\Models\Experience;
use Modules\Resume\Models\Language;
use Modules\Resume\Models\Project;
use Modules\Resume\Models\Skill;
use Modules\Resume\Models\Technology;
use Tests\TestCase;

class ResumeModelsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // ساخت نقش‌ها و مجوزها (idempotent)
        $this->seed(RolePermissionSeeder::class);
        $this->seed(ResumePermissionSeeder::class);
    }

    // ===== تست مدل Skill =====

    public function test_skill_can_be_created_with_fillable_fields(): void
    {
        $skill = Skill::create([
            'name' => 'Laravel',
            'slug' => 'laravel',
            'category' => 'backend',
            'proficiency' => 90,
            'is_active' => true,
            'order' => 1,
            'show_in_resume' => true,
        ]);

        $this->assertInstanceOf(Skill::class, $skill);
        $this->assertEquals('backend', $skill->category);
        $this->assertEquals(90, $skill->proficiency);
    }

    public function test_skill_category_label_attribute(): void
    {
        $skill = Skill::create(['name' => 'Vue', 'slug' => 'vue', 'category' => 'frontend', 'proficiency' => 80]);

        $this->assertEquals('فرانت‌اند', $skill->category_label);
    }

    public function test_skill_scopes(): void
    {
        Skill::create(['name' => 'A', 'slug' => 'a', 'category' => 'backend', 'proficiency' => 50, 'is_active' => false, 'show_in_resume' => false]);
        Skill::create(['name' => 'B', 'slug' => 'b', 'category' => 'backend', 'proficiency' => 60, 'is_active' => true, 'show_in_resume' => true]);

        $this->assertEquals(1, Skill::active()->count());
        $this->assertEquals(1, Skill::inResume()->count());
    }

    public function test_skill_belongs_to_many_projects(): void
    {
        $skill = Skill::create(['name' => 'Laravel', 'slug' => 'laravel-x', 'category' => 'backend', 'proficiency' => 90]);
        $project = Project::create([
            'title' => 'تست',
            'slug' => 'test-project',
            'description' => 'تست',
            'status' => 'draft',
        ]);

        $project->skills()->attach($skill->id);

        $this->assertTrue($project->skills->contains($skill));
        $this->assertTrue($skill->projects->contains($project));
    }

    // ===== تست مدل Education =====

    public function test_education_can_be_created_and_casts(): void
    {
        $education = Education::create([
            'institution' => 'دانشگاه تهران',
            'degree' => 'کارشناسی',
            'field_of_study' => 'نرم‌افزار',
            'start_date' => '2018-09-01',
            'end_date' => '2022-07-01',
            'is_current' => false,
        ]);

        $this->assertInstanceOf(CarbonInterface::class, $education->start_date);
        $this->assertTrue($education->end_date->isSameDay('2022-07-01'));
    }

    public function test_education_scopes(): void
    {
        Education::create(['institution' => 'A', 'degree' => 'd', 'field_of_study' => 'f', 'start_date' => '2018-09-01', 'is_active' => false, 'show_in_resume' => false]);
        Education::create(['institution' => 'B', 'degree' => 'd', 'field_of_study' => 'f', 'start_date' => '2019-09-01', 'is_active' => true, 'show_in_resume' => true]);

        // شمارش فقط رکوردهای همین تست (seeder رکوردهای فعال دیگری دارد)
        $this->assertEquals(1, Education::whereIn('institution', ['A', 'B'])->active()->count());
        $this->assertEquals(1, Education::whereIn('institution', ['A', 'B'])->inResume()->count());
    }

    // ===== تست مدل Experience =====

    public function test_experience_technologies_json_cast(): void
    {
        $experience = Experience::create([
            'company' => 'شرکت تست',
            'position' => 'توسعه‌دهنده',
            'employment_type' => 'full-time',
            'start_date' => '2020-01-01',
            'technologies' => ['Laravel', 'Vue'],
        ]);

        $this->assertIsArray($experience->technologies);
        $this->assertContains('Laravel', $experience->technologies);
        $this->assertEquals('تمام‌وقت', $experience->employment_type_label);
    }

    // ===== تست مدل Project (فیلدهای جدید) =====

    public function test_project_new_fields_and_demo_link(): void
    {
        // demo_type = link با URL کامل
        $project = Project::create([
            'title' => 'تست دمو',
            'slug' => 'test-demo',
            'description' => 'تست',
            'demo_url' => 'https://demo.example.com',
            'demo_type' => 'link',
            'short_description' => 'کوتاه',
            'gallery' => ['img1.jpg', 'img2.jpg'],
            'client_name' => 'مشتری تست',
            'project_date' => '2024-01-15',
            'order' => 5,
        ]);

        $this->assertEquals('https://demo.example.com', $project->demo_link);
        $this->assertIsArray($project->gallery);
        $this->assertEquals(5, $project->order);
        $this->assertTrue($project->project_date->isSameDay('2024-01-15'));
    }

    public function test_project_subdomain_demo_link(): void
    {
        // demo_type = subdomain با مقدار نسبی — باید به https متصل شود
        $project = Project::create([
            'title' => 'تست سابدمین',
            'slug' => 'test-sub',
            'description' => 'تست',
            'demo_url' => 'demo.rezomeh.ir',
            'demo_type' => 'subdomain',
        ]);

        $this->assertEquals('https://demo.rezomeh.ir', $project->demo_link);
    }

    public function test_project_without_demo_url_has_null_link(): void
    {
        $project = Project::create([
            'title' => 'بدون دمو',
            'slug' => 'no-demo',
            'description' => 'تست',
        ]);

        $this->assertNull($project->demo_link);
    }

    public function test_project_scopes(): void
    {
        Project::create(['title' => 'منتشر', 'slug' => 'pub', 'description' => 'd', 'status' => 'published', 'featured' => true]);
        Project::create(['title' => 'پیش‌نویس', 'slug' => 'drf', 'description' => 'd', 'status' => 'draft']);

        $this->assertEquals(1, Project::published()->count());
        $this->assertEquals(1, Project::featured()->count());
    }

    // ===== تست روابط کامل =====

    public function test_project_relationships(): void
    {
        $category = Category::create(['name' => 'وب', 'slug' => 'web']);
        $tech = Technology::create(['name' => 'Laravel', 'slug' => 'laravel']);
        $skill = Skill::create(['name' => 'PHP', 'slug' => 'php', 'category' => 'backend', 'proficiency' => 85]);

        $project = Project::create([
            'title' => 'پروژه روابط',
            'slug' => 'rel-test',
            'description' => 'تست روابط',
            'category_id' => $category->id,
        ]);
        $project->technologies()->attach($tech->id);
        $project->skills()->attach($skill->id);

        $this->assertEquals('وب', $project->category->name);
        $this->assertTrue($project->technologies->contains($tech));
        $this->assertTrue($project->skills->contains($skill));
    }

    public function test_deleting_project_detaches_relations(): void
    {
        $skill = Skill::create(['name' => 'Del Skill', 'slug' => 'del-skill', 'category' => 'backend', 'proficiency' => 50]);
        $project = Project::create(['title' => 'حذف', 'slug' => 'del-test', 'description' => 'd']);
        $project->skills()->attach($skill->id);

        $project->skills()->detach();
        $project->delete();

        $this->assertDatabaseMissing('project_skill', ['project_id' => $project->id]);
        $this->assertDatabaseHas('skills', ['id' => $skill->id]); // مهارت حذف نمی‌شود
    }

    // ===== تست مدل Language (فاز ۶.۷) =====

    public function test_language_can_be_created_with_fillable_fields(): void
    {
        $language = Language::create([
            'name' => 'انگلیسی',
            'code' => 'en',
            'level' => 'advanced',
            'proficiency' => 85,
            'is_active' => true,
            'order' => 0,
            'show_in_resume' => true,
        ]);

        $this->assertInstanceOf(Language::class, $language);
        $this->assertEquals('advanced', $language->level);
        $this->assertEquals(85, $language->proficiency);
    }

    public function test_language_level_label_attribute(): void
    {
        $native = Language::create(['name' => 'فارسی', 'code' => 'fa', 'level' => 'native', 'proficiency' => 100]);
        $beginner = Language::create(['name' => 'عربی', 'code' => 'ar', 'level' => 'beginner', 'proficiency' => 30]);

        $this->assertEquals('زبان مادری', $native->level_label);
        $this->assertEquals('مقدماتی', $beginner->level_label);
    }

    public function test_language_scopes(): void
    {
        Language::create(['name' => 'A', 'level' => 'beginner', 'proficiency' => 30, 'is_active' => false, 'show_in_resume' => false]);
        Language::create(['name' => 'B', 'level' => 'advanced', 'proficiency' => 80, 'is_active' => true, 'show_in_resume' => true]);

        $this->assertEquals(1, Language::whereIn('name', ['A', 'B'])->active()->count());
        $this->assertEquals(1, Language::whereIn('name', ['A', 'B'])->inResume()->count());
    }
}
