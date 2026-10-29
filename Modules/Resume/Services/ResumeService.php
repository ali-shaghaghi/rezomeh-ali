<?php

namespace Modules\Resume\Services;

use Illuminate\Http\UploadedFile;
use Modules\Resume\Models\Education;
use Modules\Resume\Models\Experience;
use Modules\Resume\Models\Language;
use Modules\Resume\Models\Project;
use Modules\Resume\Models\ResumeProfile;
use Modules\Resume\Models\Skill;

class ResumeService
{
    /**
     * آمار کلی رزومه برای هاب مرکزی
     */
    public function stats(): array
    {
        return [
            'projects' => Project::count(),
            'published_projects' => Project::published()->count(),
            'skills' => Skill::count(),
            'active_skills' => Skill::active()->count(),
            'educations' => Education::count(),
            'experiences' => Experience::count(),
            'languages' => Language::count(),
        ];
    }

    /**
     * پروفایل فعال رزومه (singleton)
     */
    public function profile(): ResumeProfile
    {
        return ResumeProfile::current();
    }

    /**
     * ذخیره پروفایل رزومه (singleton — همیشه رکورد فعال به‌روزرسانی می‌شود)
     */
    public function saveProfile(array $data): ResumeProfile
    {
        $profile = ResumeProfile::current();

        // آپلود تصویر شخص
        if (! empty($data['avatar']) && $data['avatar'] instanceof UploadedFile) {
            $data['avatar'] = $data['avatar']->store('resume/profile', 'public');
        }

        // آپلود فایل رزومه (PDF)
        if (! empty($data['resume_file']) && $data['resume_file'] instanceof UploadedFile) {
            $data['resume_file'] = $data['resume_file']->store('resume/files', 'public');
        }

        $profile->update($data);

        return $profile->refresh();
    }

    /**
     * آیتم‌های قابل نمایش در رزومه عمومی
     */
    public function visibleItems(): array
    {
        return [
            'profile' => $this->profile(),
            'projects' => Project::with(['category', 'technologies'])
                ->published()
                ->ordered()
                ->get(),
            'skills' => Skill::active()->inResume()->ordered()->get(),
            'educations' => Education::active()->inResume()->ordered()->get(),
            'experiences' => Experience::active()->inResume()->ordered()->get(),
            'languages' => Language::active()->inResume()->ordered()->get(),
        ];
    }
}
