<?php

namespace Modules\Resume\Livewire\Admin;

use Livewire\Component;
use Livewire\WithFileUploads;
use Modules\Resume\Models\Education;
use Modules\Resume\Models\Experience;
use Modules\Resume\Models\Language;
use Modules\Resume\Models\Project;
use Modules\Resume\Models\ResumeProfile;
use Modules\Resume\Models\Skill;
use Modules\Resume\Services\ResumeService;

class ResumeManager extends Component
{
    use WithFileUploads;

    // تب فعال
    public string $activeTab = 'projects';

    // ===== پروفایل رزومه (singleton) =====
    public bool $profileEditing = false;

    public string $profile_name = '';

    public string $profile_job_title = '';

    public string $profile_location = '';

    public string $profile_email = '';

    public string $profile_github_url = '';

    public string $profile_linkedin_url = '';

    public string $profile_bio = '';

    public string $profile_typewriter_texts = ''; // هر خط یک عنوان شغلی

    // ===== فیلدهای انگلیسی (اختیاری — در صورت خالی بودن فارسی نمایش داده می‌شود) =====

    public string $profile_name_en = '';

    public string $profile_job_title_en = '';

    public string $profile_location_en = '';

    public string $profile_bio_en = '';

    public string $profile_typewriter_texts_en = ''; // هر خط یک عنوان شغلی انگلیسی

    // ===== شبکه‌های اجتماعی (آیکون اختصاصی هر کدام در رزومه) =====

    public string $profile_telegram_url = '';

    public string $profile_whatsapp_url = '';

    public string $profile_instagram_url = '';

    public string $profile_rubika_url = '';

    public string $profile_bale_url = '';

    public string $profile_eita_url = '';

    public string $profile_phone = '';

    public $profile_avatar = null;

    public $profile_resume_file = null;

    // انتخاب تصویر شخص از گالری (مسیر نسبی gallery/{hash}) — آپلود مستقیم در profile_avatar می‌ماند
    public ?string $profileAvatarSrc = null;

    public ?string $existingAvatar = null;

    public ?string $existingResumeFile = null;

    public bool $savingProfile = false;

    // نوتیفیکیشن
    public bool $successToast = false;

    public string $successMessage = '';

    public function render()
    {
        $service = app(ResumeService::class);

        // تصاویر منتشرشدهٔ گالری — برای مودال انتخاب ریل‌تایم تصویر شخص
        $avatarPickerAssetsJson = json_encode(
            app(\Modules\Gallery\Services\GalleryService::class)->publishedAssets()
                ->filter(fn ($a) => ! $a->is_video)
                ->map(fn ($a) => [
                    'id' => $a->id,
                    'src' => $a->srcAttribute(),
                    'thumb' => $a->thumbAttribute() ?: $a->srcAttribute(),
                    'alt' => $a->alt,
                ])->values(),
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        return view('resume::livewire.admin.resume-manager', [
            'stats' => $service->stats(),
            'profile' => $service->profile(),
            'projects' => Project::with('category')->ordered()->get(),
            'skills' => Skill::ordered()->get(),
            'educations' => Education::ordered()->get(),
            'experiences' => Experience::ordered()->get(),
            'languages' => Language::ordered()->get(),
            'avatarPickerAssetsJson' => $avatarPickerAssetsJson,
        ]);
    }

    // تغییر تب
    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    // ===== پروفایل رزومه =====

    // بازکردن فرم ویرایش پروفایل
    public function editProfile(): void
    {
        $this->requireResumePermission();

        $profile = ResumeProfile::current();
        $this->profile_name = $profile->name ?? '';
        $this->profile_job_title = $profile->job_title ?? '';
        $this->profile_location = $profile->location ?? '';
        $this->profile_email = $profile->email ?? '';
        $this->profile_github_url = $profile->github_url ?? '';
        $this->profile_linkedin_url = $profile->linkedin_url ?? '';
        $this->profile_bio = $profile->bio ?? '';
        $this->profile_typewriter_texts = implode(PHP_EOL, $profile->typewriter_texts ?? []);
        // انگلیسی
        $this->profile_name_en = $profile->name_en ?? '';
        $this->profile_job_title_en = $profile->job_title_en ?? '';
        $this->profile_location_en = $profile->location_en ?? '';
        $this->profile_bio_en = $profile->bio_en ?? '';
        $this->profile_typewriter_texts_en = implode(PHP_EOL, $profile->typewriter_texts_en ?? []);
        // شبکه‌های اجتماعی
        $this->profile_telegram_url = $profile->telegram_url ?? '';
        $this->profile_whatsapp_url = $profile->whatsapp_url ?? '';
        $this->profile_instagram_url = $profile->instagram_url ?? '';
        $this->profile_rubika_url = $profile->rubika_url ?? '';
        $this->profile_bale_url = $profile->bale_url ?? '';
        $this->profile_eita_url = $profile->eita_url ?? '';
        $this->profile_phone = $profile->phone ?? '';

        // تصویر شخص: مسیر گالری (gallery/{hash}) یا فایل آپلودی قدیمی (storage)
        if ($profile->avatar && str_starts_with(ltrim($profile->avatar, '/'), 'gallery/')) {
            $this->profileAvatarSrc = ltrim($profile->avatar, '/');
            $this->existingAvatar = null;
        } else {
            $this->profileAvatarSrc = null;
            $this->existingAvatar = $profile->avatar;
        }
        $this->existingResumeFile = $profile->resume_file;
        $this->profileEditing = true;
    }

    // انتخاب تصویر شخص از گالری (مودال ریل‌تایم) — فقط مسیر نسبی gallery/{hash} ذخیره می‌شود
    public function pickAvatarImage(string $src): void
    {
        $path = preg_replace('#^https?://[^/]+#', '', $src);
        $path = ltrim((string) $path, '/');

        if (! preg_match('#^gallery/[A-Za-z0-9_\-]{16,128}$#', $path)) {
            return;
        }

        $this->profileAvatarSrc = $path;
        $this->profile_avatar = null; // انتخاب از گالری جایگزین آپلود مستقیم می‌شود
        $this->existingAvatar = null;
    }

    // آپلود مستقیم فایل، انتخاب گالری قبلی را کنار می‌زند
    public function updatedProfileAvatar(): void
    {
        if (! empty($this->profile_avatar)) {
            $this->profileAvatarSrc = null;
        }
    }

    // ذخیره پروفایل
    public function saveProfile(): void
    {
        $this->requireResumePermission();

        $validated = $this->validate([
            'profile_name' => 'required|string|max:255',
            'profile_job_title' => 'required|string|max:255',
            'profile_location' => 'nullable|string|max:255',
            'profile_email' => 'nullable|email|max:255',
            'profile_github_url' => 'nullable|url|max:255',
            'profile_linkedin_url' => 'nullable|url|max:255',
            'profile_bio' => 'nullable|string|max:2000',
            'profile_typewriter_texts' => 'nullable|string|max:2000',
            // انگلیسی — همه اختیاری
            'profile_name_en' => 'nullable|string|max:255',
            'profile_job_title_en' => 'nullable|string|max:255',
            'profile_location_en' => 'nullable|string|max:255',
            'profile_bio_en' => 'nullable|string|max:2000',
            'profile_typewriter_texts_en' => 'nullable|string|max:2000',
            'profile_avatar' => 'nullable|image|max:2048',
            'profileAvatarSrc' => ['nullable', 'string', 'regex:#^gallery/[A-Za-z0-9_\-]{16,128}$#'],
            'profile_resume_file' => 'nullable|file|mimes:pdf|max:5120',
            // شبکه‌های اجتماعی — همه اختیاری
            'profile_telegram_url' => 'nullable|string|max:255',
            'profile_whatsapp_url' => 'nullable|string|max:255',
            'profile_instagram_url' => 'nullable|string|max:255',
            'profile_rubika_url' => 'nullable|string|max:255',
            'profile_bale_url' => 'nullable|string|max:255',
            'profile_eita_url' => 'nullable|string|max:255',
            'profile_phone' => 'nullable|string|max:32',
        ]);

        // تبدیل متن‌های تایپ‌رایتر (هر خط یک عنوان)
        $typewriter = collect(preg_split('/\r\n|\r|\n/', $validated['profile_typewriter_texts'] ?? ''))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->all();

        $typewriterEn = collect(preg_split('/\r\n|\r|\n/', $validated['profile_typewriter_texts_en'] ?? ''))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->all();

        $data = [
            'name' => $validated['profile_name'],
            'job_title' => $validated['profile_job_title'],
            'location' => $validated['profile_location'] ?: null,
            'email' => $validated['profile_email'] ?: null,
            'github_url' => $validated['profile_github_url'] ?: null,
            'linkedin_url' => $validated['profile_linkedin_url'] ?: null,
            'bio' => $validated['profile_bio'] ?: null,
            'typewriter_texts' => $typewriter ?: null,
            // انگلیسی
            'name_en' => $validated['profile_name_en'] ?: null,
            'job_title_en' => $validated['profile_job_title_en'] ?: null,
            'location_en' => $validated['profile_location_en'] ?: null,
            'bio_en' => $validated['profile_bio_en'] ?: null,
            'typewriter_texts_en' => $typewriterEn ?: null,
            // شبکه‌های اجتماعی
            'telegram_url' => $validated['profile_telegram_url'] ?? null,
            'whatsapp_url' => $validated['profile_whatsapp_url'] ?? null,
            'instagram_url' => $validated['profile_instagram_url'] ?? null,
            'rubika_url' => $validated['profile_rubika_url'] ?? null,
            'bale_url' => $validated['profile_bale_url'] ?? null,
            'eita_url' => $validated['profile_eita_url'] ?? null,
            'phone' => $validated['profile_phone'] ?? null,
        ];

        // فایل‌ها فقط وقتی در آرایه data قرار می‌گیرند که فایل جدیدی آپلود شده باشد.
        // در غیر این صورت update() روی avatar/resume_file مقدار null می‌نوشت و
        // تصویر/رزومه قبلی از روی رکورد پاک می‌شد (#۵).
        // اولویت: آپلود مستقیم ← انتخاب از گالری ← مسیر قبلی (حفظ می‌شود)
        if (!empty($this->profile_avatar)) {
            $data['avatar'] = $this->profile_avatar;
        } elseif ($this->profileAvatarSrc) {
            $data['avatar'] = $this->profileAvatarSrc;
        }
        if (!empty($this->profile_resume_file)) {
            $data['resume_file'] = $this->profile_resume_file;
        }

        $profile = app(ResumeService::class)->saveProfile($data);

        // پاک‌کردن فایل موقت Livewire تا در دفعه بعد مقدار سپری‌نشده (stale) محسوب نشود
        $this->profile_avatar = null;
        $this->profile_resume_file = null;

        if ($profile->avatar && str_starts_with(ltrim($profile->avatar, '/'), 'gallery/')) {
            $this->profileAvatarSrc = ltrim($profile->avatar, '/');
            $this->existingAvatar = null;
        } else {
            $this->profileAvatarSrc = null;
            $this->existingAvatar = $profile->avatar;
        }
        $this->existingResumeFile = $profile->resume_file;
        $this->profileEditing = false;
        $this->notifySuccess('پروفایل رزومه با موفقیت به‌روزرسانی شد.');
    }

    // حذف تصویر شخص
    public function removeAvatar(): void
    {
        $this->requireResumePermission();

        $profile = ResumeProfile::current();
        $profile->update(['avatar' => null]);
        $this->existingAvatar = null;
        $this->profileAvatarSrc = null;
        $this->notifySuccess('تصویر شخص حذف شد.');
    }

    // حذف فایل رزومه
    public function removeResumeFile(): void
    {
        $this->requireResumePermission();

        $profile = ResumeProfile::current();
        $profile->update(['resume_file' => null]);
        $this->existingResumeFile = null;
        $this->notifySuccess('فایل رزومه حذف شد.');
    }

    // بررسی دسترسی مدیریت رزومه
    private function requireResumePermission(): void
    {
        abort_unless(
            auth()->user()?->hasAnyPermission(['manage_resume', 'view_resume']),
            404,
            'چنین صفحه‌ای در سیستم وجود ندارد.'
        );
    }

    // ===== پروژه‌ها =====
    // طبق معماری: برای پروژه‌ها مبنای رزومه = status(published) + featured + order (بدون show_in_resume)

    // تغییر وضعیت ویژه (Featured) — پروژه برگزیده
    public function toggleProjectFeatured(int $id): void
    {
        $project = Project::findOrFail($id);
        $this->authorize('update', $project);

        $project->update(['featured' => ! $project->featured]);
        $this->notifySuccess($project->featured
            ? 'پروژه «'.$project->title.'» به پروژه‌های برگزیده اضافه شد.'
            : 'پروژه «'.$project->title.'» از برگزیدگان حذف شد.');
    }

    // تغییر وضعیت انتشار از هاب رزومه (پروژه منتشرشده در رزومه عمومی دیده می‌شود)
    public function toggleProjectStatus(int $id): void
    {
        $project = Project::findOrFail($id);
        $this->authorize('update', $project);

        $project->update(['status' => $project->status === 'published' ? 'draft' : 'published']);
        $this->notifySuccess($project->status === 'published'
            ? 'پروژه «'.$project->title.'» به رزومه عمومی اضافه شد (منتشر شد).'
            : 'پروژه «'.$project->title.'» از رزومه عمومی حذف شد (پیش‌نویس).');
    }

    // تغییر ترتیب پروژه
    public function moveProject(int $id, string $direction): void
    {
        $project = Project::findOrFail($id);
        $this->authorize('update', $project);

        $project->order = max(0, $project->order + ($direction === 'up' ? -1 : 1));
        $project->save();
        $this->notifySuccess('ترتیب پروژه «'.$project->title.'» تغییر کرد.');
    }

    // ===== مهارت‌ها =====

    public function toggleSkillResume(int $id): void
    {
        $skill = Skill::findOrFail($id);
        $this->authorize('update', $skill);

        $skill->update(['show_in_resume' => ! $skill->show_in_resume]);
        $this->notifySuccess($skill->show_in_resume
            ? 'مهارت «'.$skill->name.'» به رزومه اضافه شد.'
            : 'مهارت «'.$skill->name.'» از رزومه حذف شد.');
    }

    public function toggleSkillActive(int $id): void
    {
        $skill = Skill::findOrFail($id);
        $this->authorize('update', $skill);

        $skill->update(['is_active' => ! $skill->is_active]);
        $this->notifySuccess($skill->is_active
            ? 'مهارت «'.$skill->name.'» فعال شد.'
            : 'مهارت «'.$skill->name.'» غیرفعال شد.');
    }

    public function moveSkill(int $id, string $direction): void
    {
        $skill = Skill::findOrFail($id);
        $this->authorize('update', $skill);

        $skill->order = max(0, $skill->order + ($direction === 'up' ? -1 : 1));
        $skill->save();
    }

    // ===== تحصیلات =====

    public function toggleEducationResume(int $id): void
    {
        $education = Education::findOrFail($id);
        $this->authorize('update', $education);

        $education->update(['show_in_resume' => ! $education->show_in_resume]);
        $this->notifySuccess($education->show_in_resume
            ? 'مقطع «'.$education->institution.'» به رزومه اضافه شد.'
            : 'مقطع «'.$education->institution.'» از رزومه حذف شد.');
    }

    public function toggleEducationActive(int $id): void
    {
        $education = Education::findOrFail($id);
        $this->authorize('update', $education);

        $education->update(['is_active' => ! $education->is_active]);
        $this->notifySuccess($education->is_active
            ? 'مقطع «'.$education->institution.'» فعال شد.'
            : 'مقطع «'.$education->institution.'» غیرفعال شد.');
    }

    public function moveEducation(int $id, string $direction): void
    {
        $education = Education::findOrFail($id);
        $this->authorize('update', $education);

        $education->order = max(0, $education->order + ($direction === 'up' ? -1 : 1));
        $education->save();
    }

    // ===== تجربیات =====

    public function toggleExperienceResume(int $id): void
    {
        $experience = Experience::findOrFail($id);
        $this->authorize('update', $experience);

        $experience->update(['show_in_resume' => ! $experience->show_in_resume]);
        $this->notifySuccess($experience->show_in_resume
            ? 'سابقه «'.$experience->company.'» به رزومه اضافه شد.'
            : 'سابقه «'.$experience->company.'» از رزومه حذف شد.');
    }

    public function toggleExperienceActive(int $id): void
    {
        $experience = Experience::findOrFail($id);
        $this->authorize('update', $experience);

        $experience->update(['is_active' => ! $experience->is_active]);
        $this->notifySuccess($experience->is_active
            ? 'سابقه «'.$experience->company.'» فعال شد.'
            : 'سابقه «'.$experience->company.'» غیرفعال شد.');
    }

    public function moveExperience(int $id, string $direction): void
    {
        $experience = Experience::findOrFail($id);
        $this->authorize('update', $experience);

        $experience->order = max(0, $experience->order + ($direction === 'up' ? -1 : 1));
        $experience->save();
    }

    // ===== زبان‌ها =====

    public function toggleLanguageResume(int $id): void
    {
        $language = Language::findOrFail($id);
        $this->authorize('update', $language);

        $language->update(['show_in_resume' => ! $language->show_in_resume]);
        $this->notifySuccess($language->show_in_resume
            ? 'زبان «'.$language->name.'» به رزومه اضافه شد.'
            : 'زبان «'.$language->name.'» از رزومه حذف شد.');
    }

    public function toggleLanguageActive(int $id): void
    {
        $language = Language::findOrFail($id);
        $this->authorize('update', $language);

        $language->update(['is_active' => ! $language->is_active]);
        $this->notifySuccess($language->is_active
            ? 'زبان «'.$language->name.'» فعال شد.'
            : 'زبان «'.$language->name.'» غیرفعال شد.');
    }

    public function moveLanguage(int $id, string $direction): void
    {
        $language = Language::findOrFail($id);
        $this->authorize('update', $language);

        $language->order = max(0, $language->order + ($direction === 'up' ? -1 : 1));
        $language->save();
    }

    private function notifySuccess(string $message): void
    {
        $this->successMessage = $message;
        $this->successToast = true;
    }
}
