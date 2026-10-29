<?php

namespace Modules\Resume\Providers;

use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Modules\Core\Providers\BaseServiceProvider;
use Modules\Resume\Livewire\Admin\EducationManager;
use Modules\Resume\Livewire\Admin\ExperienceManager;
use Modules\Resume\Livewire\Admin\LanguagesManager;
use Modules\Resume\Livewire\Admin\PortfolioForm;
use Modules\Resume\Livewire\Admin\PortfolioIndex;
use Modules\Resume\Livewire\Admin\PortfolioShow;
use Modules\Resume\Livewire\Admin\ResumeManager;
use Modules\Resume\Livewire\Admin\SkillsManager;
use Modules\Resume\Models\Education;
use Modules\Resume\Models\Experience;
use Modules\Resume\Models\Language;
use Modules\Resume\Models\Project;
use Modules\Resume\Models\Skill;
use Modules\Resume\Policies\EducationPolicy;
use Modules\Resume\Policies\ExperiencePolicy;
use Modules\Resume\Policies\LanguagePolicy;
use Modules\Resume\Policies\ProjectPolicy;
use Modules\Resume\Policies\SkillPolicy;

class ResumeServiceProvider extends BaseServiceProvider
{
    protected function moduleName(): string
    {
        return 'Resume';
    }

    public function boot(): void
    {
        parent::boot();

        // دیتابیس ماژول رزومه — خودکفا (مایگریشن‌ها داخل ماژول)
        $this->loadMigrationsFrom(module_path('Resume', 'Database'.DIRECTORY_SEPARATOR.'Migrations'));

        // روتینگ ماژول رزومه — خودکفا (ادمین با پیشوند admin/resume)
        $this->loadRoutesFrom(module_path('Resume', 'Routes'.DIRECTORY_SEPARATOR.'admin.php'));

        // ثبت Policies (مدل‌های ماژول با کشف خودکار لاراول پیدا نمی‌شوند)
        Gate::policy(Project::class, ProjectPolicy::class);
        Gate::policy(Skill::class, SkillPolicy::class);
        Gate::policy(Education::class, EducationPolicy::class);
        Gate::policy(Experience::class, ExperiencePolicy::class);
        Gate::policy(Language::class, LanguagePolicy::class);

        // ثبت کامپوننت‌های Livewire
        Livewire::component('admin.resume.portfolio-index', PortfolioIndex::class);
        Livewire::component('admin.resume.portfolio-form', PortfolioForm::class);
        Livewire::component('admin.resume.portfolio-show', PortfolioShow::class);
        Livewire::component('admin.resume.skills-manager', SkillsManager::class);
        Livewire::component('admin.resume.education-manager', EducationManager::class);
        Livewire::component('admin.resume.experience-manager', ExperienceManager::class);
        Livewire::component('admin.resume.languages-manager', LanguagesManager::class);
        Livewire::component('admin.resume.manager', ResumeManager::class);
    }

    public function register(): void
    {
        parent::register();
    }
}
