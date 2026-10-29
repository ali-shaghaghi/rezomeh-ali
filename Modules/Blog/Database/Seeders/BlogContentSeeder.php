<?php

namespace Modules\Blog\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\Core\Models\BlogPost;
use Modules\Core\Models\Category;
use Modules\Core\Models\Comment;
use Modules\Core\Models\Tag;

class BlogContentSeeder extends Seeder
{
    /**
     * محتوای نمونه وبلاگ (دسته‌ها، برچسب‌ها، مقالات، نظرات)
     */
    public function run(): void
    {
        // نویسنده پیش‌فرض: اولین کاربر ادمین
        $author = User::first() ?? User::factory()->create();

        // ===== دسته‌بندی‌ها =====
        $categories = [
            ['name' => 'آموزش برنامه‌نویسی', 'slug' => 'programming-tutorials', 'description' => 'مقالات آموزشی برنامه‌نویسی و توسعه وب'],
            ['name' => 'لاراول', 'slug' => 'laravel', 'description' => 'تجربیات کار با فریمورک لاراول'],
            ['name' => 'مسیر شغلی', 'slug' => 'career', 'description' => 'نکات و تجربیات مسیر حرفه‌ای توسعه‌دهنده'],
            ['name' => 'ابزارها', 'slug' => 'tools', 'description' => 'معرفی ابزارهای کاربردی توسعه'],
        ];

        $categoryIds = [];
        foreach ($categories as $cat) {
            $categoryIds[$cat['slug']] = Category::firstOrCreate(['slug' => $cat['slug']], $cat)->id;
        }

        // ===== برچسب‌ها =====
        $tags = ['Laravel', 'PHP', 'Livewire', 'Tailwind', 'Vue.js', 'MySQL', 'DevOps', 'تجربه کاری'];
        $tagIds = [];
        foreach ($tags as $tag) {
            $slug = Str::slug($tag);
            $tagIds[$slug] = Tag::firstOrCreate(['slug' => $slug], ['name' => $tag])->id;
        }

        // ===== مقالات =====
        $posts = [
            [
                'title' => 'شروع کار با لاراول ۱۳: چه چیزهایی جدید است؟',
                'slug' => 'laravel-13-getting-started',
                'excerpt' => 'نگاهی به ویژگی‌های جدید لاراول ۱۳ و نحوه ارتقای پروژه‌های موجود',
                'content' => "لاراول ۱۳ جدیدترین نسخه فریمورک محبوب PHP است که با بهبودهای زیادی همراه شده است.\n\nدر این مقاله با ویژگی‌های کلیدی این نسخه آشنا می‌شویم:\n\n۱. بهبود عملکرد هسته فریمورک\n۲. پشتیبانی بهتر از PHP 8.3\n۳. کامپوننت‌های Livewire 3 پیشرفته‌تر\n\nبرای شروع، کافی است پروژه جدیدی با Composer ایجاد کنید و نسخه ۱۳ را نصب کنید.",
                // ===== English version =====
                'title_en' => 'Getting started with Laravel 13: what’s new?',
                'excerpt_en' => 'A look at the new features of Laravel 13 and how to upgrade existing projects',
                'content_en' => "Laravel 13 is the latest version of the popular PHP framework, packed with improvements.\n\nIn this article we explore the key features of this release:\n\n1. Core framework performance improvements\n2. Better PHP 8.3 support\n3. More advanced Livewire 3 components\n\nTo get started, simply create a new project with Composer and install version 13.",
                'status' => 'published',
                'published_at' => now()->subDays(5),
                'views' => 245,
                'categories' => ['laravel', 'programming-tutorials'],
                'tags' => ['laravel', 'php', 'livewire'],
                'comments' => [
                    ['content' => 'مقاله خیلی خوبی بود، ممنون از توضیحات کاملتون!', 'status' => 'approved'],
                    ['content' => 'منتظر ادامه سری لاراول هستم.', 'status' => 'pending'],
                ],
            ],
            [
                'title' => 'چرا Livewire برای پروژه‌های ایرانی مناسب است؟',
                'slug' => 'why-livewire-for-iranian-projects',
                'excerpt' => 'مقایسه Livewire با SPA و دلایل انتخاب آن برای تیم‌های کوچک',
                'content' => "Livewire به شما اجازه می‌دهد رابط‌های کاربری ریاکتیو بسازید بدون اینکه نیاز به JavaScript پیچیده داشته باشید.\n\nمزایای اصلی:\n\n- یادگیری آسان برای تیم‌های PHP محور\n- حذف نیاز به API جداگانه\n- سازگاری کامل با Blade و Tailwind\n\nدر تجربه شخصی، سرعت توسعه با Livewire حدود ۴۰٪ بیشتر از React + API بوده است.",
                // ===== English version =====
                'title_en' => 'Why Livewire is a great fit for small teams',
                'excerpt_en' => 'Comparing Livewire with a SPA approach and why it wins for small teams',
                'content_en' => "Livewire lets you build reactive user interfaces without writing complex JavaScript.\n\nMain benefits:\n\n- Easy to learn for PHP-centric teams\n- No need for a separate API layer\n- Full compatibility with Blade and Tailwind\n\nIn my own experience, development speed with Livewire was roughly 40% higher than React + API.",
                'status' => 'published',
                'published_at' => now()->subDays(12),
                'views' => 189,
                'categories' => ['laravel', 'programming-tutorials'],
                'tags' => ['laravel', 'livewire', 'tailwind'],
                'comments' => [
                    ['content' => 'کاملاً موافقم! برای تیم ما هم همین تجربه بود.', 'status' => 'approved'],
                ],
            ],
            [
                'title' => 'مسیر تبدیل شدن به توسعه‌دهنده Full-Stack',
                'slug' => 'become-fullstack-developer-roadmap',
                'excerpt' => 'نقشه راه قدم‌به‌قدم برای ورود به دنیای توسعه فول‌استک',
                'content' => "در این مقاله مسیر یادگیری فول‌استک را از صفر مرور می‌کنیم:\n\nمرحله ۱: HTML, CSS, JavaScript پایه\nمرحله ۲: یک فریمورک بک‌اند (پیشنهاد: Laravel)\nمرحله ۳: دیتابیس و SQL\nمرحله ۴: فرانت‌اند مدرن (Tailwind + Alpine/Livewire)\nمرحله ۵: استقرار و DevOps مقدماتی\n\nنکته مهم: پروژه‌محور یاد بگیرید، نه فقط تئوری!",
                // ===== English version =====
                'title_en' => 'The roadmap to becoming a Full-Stack developer',
                'excerpt_en' => 'A step-by-step learning path into full-stack development',
                'content_en' => "In this article we walk the full-stack learning path from scratch:\n\nStep 1: HTML, CSS, basic JavaScript\nStep 2: A backend framework (recommended: Laravel)\nStep 3: Databases and SQL\nStep 4: Modern frontend (Tailwind + Alpine/Livewire)\nStep 5: Deployment and intro to DevOps\n\nKey takeaway: learn by building projects, not just theory!",
                'status' => 'published',
                'published_at' => now()->subDays(20),
                'views' => 512,
                'categories' => ['career', 'programming-tutorials'],
                'tags' => ['laravel', 'devops', 'تجربه-کاری'],
                'comments' => [],
            ],
            [
                'title' => '۵ ابزار ضروری که هر توسعه‌دهنده باید داشته باشد',
                'slug' => '5-essential-developer-tools',
                'excerpt' => 'معرفی ابزارهایی که روزانه از آن‌ها استفاده می‌کنم',
                'content' => "ابزارها نقش مهمی در بهره‌وری دارند. لیست ابزارهای مورد علاقه من:\n\n۱. VS Code + افزونه‌های PHP\n۲. Git + GitHub Desktop\n۳. Docker برای محیط توسعه\n۴. TablePlus برای مدیریت دیتابیس\n۵. Postman برای تست API\n\nهر کدام از این ابزارها جایی در روند کاری من دارند که در مقالات بعدی توضیح می‌دهم.",
                // ===== English version =====
                'title_en' => '5 essential tools every developer should have',
                'excerpt_en' => 'The tools I use every single day',
                'content_en' => "Tools play a huge role in productivity. Here are my favorites:\n\n1. VS Code + PHP extensions\n2. Git + GitHub Desktop\n3. Docker for the dev environment\n4. TablePlus for database management\n5. Postman for API testing\n\nEach of them has a place in my workflow, which I will cover in upcoming articles.",
                'status' => 'draft',
                'published_at' => null,
                'views' => 0,
                'categories' => ['tools'],
                'tags' => ['devops'],
                'comments' => [],
            ],
        ];

        foreach ($posts as $postData) {
            // idempotent — اگر مقاله‌ای با همین slug وجود دارد رد می‌شود
            if (BlogPost::where('slug', $postData['slug'])->exists()) {
                continue;
            }

            $post = BlogPost::create([
                'user_id' => $author->id,
                'title' => $postData['title'],
                'slug' => $postData['slug'],
                'excerpt' => $postData['excerpt'],
                'content' => $postData['content'],
                'status' => $postData['status'],
                'published_at' => $postData['published_at'],
                'views' => $postData['views'],
                'seo_title' => $postData['title'],
                'seo_description' => $postData['excerpt'],
                // ===== نسخه انگلیسی (برای سئو و کاربران خارجی) =====
                'title_en' => $postData['title_en'] ?? null,
                'excerpt_en' => $postData['excerpt_en'] ?? null,
                'content_en' => $postData['content_en'] ?? null,
                'seo_title_en' => $postData['seo_title_en'] ?? $postData['title_en'] ?? null,
                'seo_description_en' => $postData['seo_description_en'] ?? $postData['excerpt_en'] ?? null,
            ]);

            // اتصال دسته‌بندی‌ها
            $post->categories()->sync(array_map(
                fn ($slug) => $categoryIds[$slug],
                $postData['categories']
            ));

            // اتصال برچسب‌ها
            $post->tags()->sync(array_map(
                fn ($slug) => $tagIds[$slug] ?? $tagIds[Str::slug($slug)],
                $postData['tags']
            ));

            // نظرات نمونه
            foreach ($postData['comments'] as $index => $commentData) {
                $commenter = User::where('email', '!=', $author->email)->inRandomOrder()->first()
                    ?? User::create(['name' => 'کاربر نمونه '.($index + 1), 'email' => 'bloguser'.$index.'@example.com', 'password' => 'password', 'is_active' => true]);

                Comment::create([
                    'user_id' => $commenter->id,
                    'blog_post_id' => $post->id,
                    'content' => $commentData['content'],
                    'status' => $commentData['status'],
                ]);
            }
        }
    }
}
