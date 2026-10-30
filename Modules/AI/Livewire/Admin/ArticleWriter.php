<?php

namespace Modules\AI\Livewire\Admin;

use Illuminate\Support\Str;
use Livewire\Component;
use App\Models\Admin;
use Modules\AI\Services\AiService;
use Modules\Core\Models\BlogPost;
use Modules\Core\Models\Category;

/**
 * ابزار ۱ — تولید مقاله دو‌زبانه بر اساس قالب سایت.
 * خروجی مستقیم قابل ذخیره به‌عنوان پیش‌نویس وبلاگ است.
 */
class ArticleWriter extends Component
{
    public string $topic = '';

    public string $keywords = '';

    public string $category = '';

    public string $tone = 'professional'; // professional | friendly | educational

    public string $language = 'both'; // fa | en | both

    public string $result = '';

    public string $errorMessage = '';

    public bool $generating = false;

    public function generate(): void
    {
        $this->validate([
            'topic' => ['required', 'string', 'min:3', 'max:200'],
            'keywords' => ['nullable', 'string', 'max:300'],
        ]);

        $this->generating = true;
        $this->errorMessage = '';

        try {
            $toneLabels = ['professional' => 'حرفه‌ای و رسمی', 'friendly' => 'دوستانه و محاوره‌ای', 'educational' => 'آموزشی و گام‌به‌گام'];
            $langInstruction = match ($this->language) {
                'fa' => 'فقط به زبان فارسی بنویس. بخش انگلیسی را خالی بگذار.',
                'en' => 'Write only in English. Leave the Persian section empty.',
                default => 'هم فارسی هم انگلیسی بنویس (هر دو نسخه کامل).',
            };

            $system = 'تو یک نویسنده محتوای سئو شده برای وب‌سایت «'.config('seo.brand.name').'» هستی — وب‌سایت یک توسعه‌دهنده وب و مشاور فنی که خدمات طراحی سایت، ساخت CRM، انجام پروژه برنامه‌نویسی و مشاوره کسب‌وکار ارائه می‌دهد. '
                .'مقاله را با ساختار استاندارد سایت بنویس: عنوان جذاب سئو شده (حداکثر ۶۰ کاراکتر)، توضیح متا (حداکثر ۱۶۰ کاراکتر)، مقدمه، ۳-۵ تیتر H2 با محتوای پاراگرافی، نتیجه‌گیری و پیشنهاد CTA (دعوت به ثبت درخواست پروژه یا رزرو مشاوره). '
                ."لحن: {$toneLabels[$this->tone]}. {$langInstruction} "
                .'خروجی را دقیقاً در این قالب JSON بده (بدون markdown): {"title_fa": "", "title_en": "", "meta_description_fa": "", "meta_description_en": "", "excerpt_fa": "", "excerpt_en": "", "content_fa": "", "content_en": "", "tags": ["", ""]}';

            $user = "موضوع مقاله: {$this->topic}";
            if ($this->keywords) {
                $user .= "\nکلمات کلیدی هدف: {$this->keywords}";
            }
            if ($this->category) {
                $user .= "\nدسته‌بندی مرتبط: {$this->category}";
            }

            $raw = app(AiService::class)->complete($system, $user);

            // استخراج JSON از پاسخ (گاهی داخل ```json است)
            if (preg_match('/\{.*\}/s', $raw, $m)) {
                $this->result = $m[0];
            } else {
                $this->result = $raw;
            }
        } catch (\Throwable $e) {
            $this->errorMessage = $e->getMessage();
        } finally {
            $this->generating = false;
        }
    }

    /**
     * ذخیره خروجی به‌عنوان پیش‌نویس وبلاگ — مقاله تک‌زبانه (فقط fa یا فقط en) هم پذیرفته می‌شود.
     */
    public function saveAsDraft(): void
    {
        $data = json_decode($this->result, true);

        $titleFa = trim((string) ($data['title_fa'] ?? ''));
        $titleEn = trim((string) ($data['title_en'] ?? ''));

        if (! is_array($data) || ($titleFa === '' && $titleEn === '')) {
            $this->errorMessage = 'خروجی معتبر برای ذخیره وجود ندارد.';

            return;
        }

        // slug از عنوان انگلیسی، وگرنه از فارسی (Str::slug روی فارسی هم transliteration می‌دهد)
        $baseSlug = Str::slug($titleEn !== '' ? $titleEn : $titleFa);
        $baseSlug = $baseSlug !== '' ? $baseSlug : 'post-'.Str::random(6);

        // slug یکتاست — تکراری نباشد پسوند بگیر (وگرنه QueryException می‌خورد)
        $slug = $baseSlug;
        $attempt = 1;
        while (BlogPost::where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.(++$attempt);
        }

        // نویسنده: کاربر (users) یا عضو تیم (admins) — شناسه ادمین هرگز در users درج نمی‌شود
        $identity = auth()->user();
        $isAdmin = $identity instanceof Admin;

        BlogPost::create([
            'user_id' => $isAdmin ? null : auth()->id(),
            'admin_id' => $isAdmin ? $identity->id : null,
            'title' => $titleFa !== '' ? $titleFa : $titleEn,
            'title_en' => $titleEn !== '' ? $titleEn : null,
            'slug' => $slug,
            'excerpt' => $data['excerpt_fa'] ?? null,
            'excerpt_en' => $data['excerpt_en'] ?? null,
            'content' => $data['content_fa'] ?? '',
            'content_en' => $data['content_en'] ?? null,
            'seo_title' => $titleFa !== '' ? $titleFa : null,
            'seo_description' => $data['meta_description_fa'] ?? null,
            'seo_title_en' => $titleEn !== '' ? $titleEn : null,
            'seo_description_en' => $data['meta_description_en'] ?? null,
            'status' => 'draft',
        ]);

        session()->flash('success', 'مقاله به‌عنوان پیش‌نویس در وبلاگ ذخیره شد.');
        $this->result = '';
        $this->topic = '';
    }

    public function render()
    {
        return view('ai::livewire.admin.article-writer', [
            'categories' => Category::orderBy('name')->get(),
        ]);
    }
}
