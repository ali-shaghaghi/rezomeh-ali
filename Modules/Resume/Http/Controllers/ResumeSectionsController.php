<?php

namespace Modules\Resume\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Modules\Resume\Models\Education;
use Modules\Resume\Models\Experience;
use Modules\Resume\Models\Language;
use Modules\Resume\Models\Skill;

class ResumeSectionsController extends Controller
{
    use AuthorizesRequests;

    /**
     * مدیریت مهارت‌ها (Livewire)
     */
    public function skills()
    {
        $this->authorize('viewAny', Skill::class);

        return view('resume::skills.index');
    }

    /**
     * مدیریت تحصیلات (Livewire)
     */
    public function education()
    {
        $this->authorize('viewAny', Education::class);

        return view('resume::education.index');
    }

    /**
     * مدیریت تجربیات (Livewire)
     */
    public function experience()
    {
        $this->authorize('viewAny', Experience::class);

        return view('resume::experience.index');
    }

    /**
     * مدیریت زبان‌ها (Livewire)
     */
    public function languages()
    {
        $this->authorize('viewAny', Language::class);

        return view('resume::languages.index');
    }

    /**
     * هاب مرکزی رزومه من (Livewire)
     */
    public function resume()
    {
        // هاب رزومه دسترسی view_resume یا manage_resume می‌خواهد
        abort_unless(
            auth()->user()?->hasAnyPermission(['manage_resume', 'view_resume']),
            404,
            'چنین صفحه‌ای در سیستم وجود ندارد.'
        );

        return view('resume::resume.index');
    }
}
