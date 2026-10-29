<?php

namespace Modules\Resume\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Models\Category;
use Modules\Resume\Models\Education;
use Modules\Resume\Models\Experience;
use Modules\Resume\Models\Language;
use Modules\Resume\Models\Project;
use Modules\Resume\Models\ResumeProfile;
use Modules\Resume\Models\Skill;
use Modules\Resume\Models\Technology;

class ResumeContentSeeder extends Seeder
{
    public function run(): void
    {
        // ========== دسته‌بندی‌ها ==========
        $categories = collect([
            Category::firstOrCreate(
                ['slug' => 'websites'],
                ['name' => 'وب‌سایت', 'description' => 'طراحی و توسعه وب‌سایت‌های شرکتی و شخصی']
            ),
            Category::firstOrCreate(
                ['slug' => 'web-applications'],
                ['name' => 'وب‌اپلیکیشن', 'description' => 'اپلیکیشن‌های وب پیشرفته و تعاملی']
            ),
            Category::firstOrCreate(
                ['slug' => 'ecommerce'],
                ['name' => 'فروشگاه اینترنتی', 'description' => 'طراحی و توسعه فروشگاه‌های آنلاین']
            ),
            Category::firstOrCreate(
                ['slug' => 'landing-pages'],
                ['name' => 'لندینگ پیج', 'description' => 'صفحات فرود با طراحی جذاب و نرخ تبدیل بالا']
            ),
        ]);

        // ====== تکنولوژی‌ها ======
        $technologies = collect([
            Technology::firstOrCreate(['slug' => 'laravel'], ['name' => 'Laravel', 'icon' => 'laravel']),
            Technology::firstOrCreate(['slug' => 'livewire'], ['name' => 'Livewire', 'icon' => 'livewire']),
            Technology::firstOrCreate(['slug' => 'react'], ['name' => 'React', 'icon' => 'react']),
            Technology::firstOrCreate(['slug' => 'vuejs'], ['name' => 'Vue.js', 'icon' => 'vuejs']),
            Technology::firstOrCreate(['slug' => 'tailwind'], ['name' => 'Tailwind CSS', 'icon' => 'tailwind']),
            Technology::firstOrCreate(['slug' => 'mysql'], ['name' => 'MySQL', 'icon' => 'mysql']),
            Technology::firstOrCreate(['slug' => 'redis'], ['name' => 'Redis', 'icon' => 'redis']),
            Technology::firstOrCreate(['slug' => 'docker'], ['name' => 'Docker', 'icon' => 'docker']),
        ]);

        // ====== نمونه پروژه‌ها ======
        $projects = [
            [
                'name' => 'فروشگاه اینترنتی دیجی‌کالا کلون',
                'name_en' => 'Digikala Clone E-Commerce',
                'slug' => 'digikala-clone',
                'description' => 'پروژه فروشگاه اینترنتی مشابه دیجی‌کالا با قابلیت‌های کامل سبد خرید، پرداخت آنلاین، پنل مدیریت محصولات و سفارشات. شامل سیستم جستجوی پیشرفته با فیلترهای متعدد و مدیریت موجودی انبار.',
                'description_en' => 'A full e-commerce project similar to Digikala, featuring a complete shopping cart, online payment, and an admin panel for products and orders. Includes an advanced search system with multiple filters and inventory management.',
                'category' => 'ecommerce',
                'technologies' => ['laravel', 'livewire', 'tailwind', 'mysql', 'redis'],
                'status' => 'published',
                'featured' => true,
                'github_url' => 'https://github.com/ali-shaghaghi/digikala-clone',
                'demo_url' => null,
                'video_url' => null,
                'seo_title' => 'فروشگاه اینترنتی کلون دیجی‌کالا',
                'seo_description' => 'پروژه کامل فروشگاه اینترنتی با Laravel',
            ],
            [
                'name' => 'سیستم مدیریت پروژه تسک‌باد',
                'name_en' => 'TaskBud — Project Management System',
                'slug' => 'taskbud-project-manager',
                'description' => 'اپلیکیشن وب مدیریت پروژه و تسک‌ها با قابلیت drag and drop، تقویم، چت تیمی، گزارش‌گیری و نمودار گانت. مناسب برای تیم‌های کوچک و بزرگ.',
                'description_en' => 'A project and task management web app with drag-and-drop boards, a shared calendar, team chat, reporting and Gantt charts. Suitable for both small and large teams.',
                'category' => 'web-applications',
                'technologies' => ['laravel', 'react', 'tailwind', 'redis', 'docker'],
                'status' => 'published',
                'featured' => true,
                'github_url' => 'https://github.com/ali-shaghaghi/taskbud',
                'demo_url' => 'https://demo.taskbud.ir',
                'video_url' => null,
                'seo_title' => 'سیستم مدیریت پروژه تسک‌باد',
                'seo_description' => 'اپلیکیشن مدیریت پروژه با React و Laravel',
            ],
            [
                'name' => 'وب‌سایت شخصی وینو',
                'slug' => 'veno-personal-website',
                'description' => 'وب‌سایت شخصی با قالب مدرن و ریسپانسیو شامل رزومه آنلاین، نمونه‌کارها، وبلاگ و فرم ارتباطی. دارای حالت تاریک و سبک‌ها. ساخته شده با معماری ماژولار.',
                'category' => 'websites',
                'technologies' => ['laravel', 'livewire', 'tailwind'],
                'status' => 'published',
                'featured' => false,
                'github_url' => 'https://github.com/ali-shaghaghi/veno',
                'demo_url' => 'https://veno.ir',
                'video_url' => null,
                'seo_title' => 'وب‌سایت شخصی وینو',
                'seo_description' => 'وب‌سایت شخصی ریسپانسیو با Laravel و Livewire',
            ],
            [
                'name' => 'پنل مدیریت رستوران',
                'slug' => 'restaurant-admin-panel',
                'description' => 'پنل مدیریت کامل رستوران با قابلیت مدیریت منو، سفارشات آنلاین، مواد اولیه، صورتحساب‌ها، گزارش‌گیری داینامیک و مدیریت تشکر. مناسب رستوران‌های غذافروشی.',
                'category' => 'web-applications',
                'technologies' => ['laravel', 'livewire', 'mysql', 'redis'],
                'status' => 'draft',
                'featured' => false,
                'github_url' => 'https://github.com/ali-shaghaghi/restaurant-panel',
                'demo_url' => null,
                'video_url' => null,
                'seo_title' => 'پنل مدیریت رستوران',
                'seo_description' => 'سیستم مدیریت رستوران آنلاین',
            ],
            [
                'name' => 'لندینگ پیج استارتاپ کراود',
                'slug' => 'craud-startup-landing',
                'description' => 'لندینگ پیج مدرن و جذاب برای استارتاپ با انیمیشن‌های نرم، فرم جمع‌آوری ایمیل، بخش ویژگی‌ها، سوالات متداول و دکمه‌های دعوت به اقدام. کاملاً ریسپانسیو.',
                'category' => 'landing-pages',
                'technologies' => ['react', 'tailwind'],
                'status' => 'published',
                'featured' => false,
                'github_url' => 'https://github.com/ali-shaghaghi/craud',
                'demo_url' => 'https://craud.ir',
                'video_url' => null,
                'seo_title' => 'لندینگ پیج کراود',
                'seo_description' => 'صفحه فرود استارتاپ کراود',
            ],
            [
                'name' => 'سیستم مدیریت مشتریان CRM',
                'slug' => 'crm-system',
                'description' => 'سیستم مدیریت ارتباط با مشتری (CRM) با قابلیت مدیریت مشتریان، فروش، پیگیری، فرصت‌ها، گزارش‌گیری و وظایف. طراحی شده برای تیم‌های فروش.',
                'category' => 'web-applications',
                'technologies' => ['laravel', 'react', 'docker', 'mysql'],
                'status' => 'archived',
                'featured' => false,
                'github_url' => 'https://github.com/ali-shaghaghi/crm',
                'demo_url' => null,
                'video_url' => null,
                'seo_title' => 'سیستم مدیریت CRM',
                'seo_description' => 'مدیریت ارتباط با مشتری',
            ],
        ];

        foreach ($projects as $data) {
            $project = Project::firstOrCreate(
                ['slug' => $data['slug']],
                [
                    'title' => $data['name'],
                    'description' => $data['description'],
                    'category_id' => $categories->where('slug', $data['category'])->first()?->id,
                    'thumbnail' => null,
                    'cover_image' => null,
                    'github_url' => $data['github_url'],
                    'demo_url' => $data['demo_url'],
                    'video_url' => $data['video_url'],
                    'status' => $data['status'],
                    'featured' => $data['featured'],
                    'seo_title' => $data['seo_title'],
                    'seo_description' => $data['seo_description'],
                    // نسخه انگلیسی (اختیاری — fallback روی فارسی)
                    'title_en' => $data['name_en'] ?? null,
                    'description_en' => $data['description_en'] ?? null,
                    // نسخه انگلیسی (اختیاری — fallback روی فارسی)
                    'title_en' => $data['name_en'] ?? null,
                    'description_en' => $data['description_en'] ?? null,
                ]
            );

            // وصل کردن تکنولوژی‌ها
            $techIds = collect($data['technologies'])
                ->map(fn ($slug) => $technologies->where('slug', $slug)->first()?->id)
                ->filter()
                ->values()
                ->all();

            if ($techIds) {
                $project->technologies()->sync($techIds);
            }
        }

        // ====== مهارت‌ها (فاز ۶) ======
        $skills = [
            ['name' => 'Laravel', 'slug' => 'laravel', 'category' => 'backend', 'proficiency' => 95],
            ['name' => 'Livewire', 'slug' => 'livewire-skill', 'category' => 'backend', 'proficiency' => 90],
            ['name' => 'JavaScript', 'slug' => 'javascript', 'category' => 'frontend', 'proficiency' => 85],
            ['name' => 'Tailwind CSS', 'slug' => 'tailwind-css-skill', 'category' => 'frontend', 'proficiency' => 92],
            ['name' => 'MySQL', 'slug' => 'mysql-skill', 'category' => 'database', 'proficiency' => 88],
            ['name' => 'Redis', 'slug' => 'redis-skill', 'category' => 'database', 'proficiency' => 75],
            ['name' => 'Docker', 'slug' => 'docker-skill', 'category' => 'devops', 'proficiency' => 80],
            ['name' => 'Git', 'slug' => 'git-skill', 'category' => 'devops', 'proficiency' => 93],
            ['name' => 'کار تیمی', 'name_en' => 'Teamwork', 'slug' => 'teamwork', 'category' => 'soft-skills', 'proficiency' => 90],
        ];

        foreach ($skills as $index => $skill) {
            Skill::firstOrCreate(
                ['slug' => $skill['slug']],
                [
                    'name' => $skill['name'],
                'name_en' => $skill['name_en'] ?? null,
                    'category' => $skill['category'],
                    'proficiency' => $skill['proficiency'],
                    'is_active' => true,
                    'order' => $index,
                    'show_in_resume' => true,
                ]
            );
        }

        // ====== تحصیلات (فاز ۶) ======
        $educations = [
            [
                'institution' => 'دانشگاه تهران',
                'institution_en' => 'University of Tehran',
                'degree' => 'کارشناسی',
                'degree_en' => 'Bachelor of Science',
                'field_of_study' => 'مهندسی نرم‌افزار',
                'field_of_study_en' => 'Software Engineering',
                'grade' => '۱۷.۸',
                'start_date' => '2018-09-01',
                'end_date' => '2022-07-01',
                'is_current' => false,
                'description' => 'فارغ‌التحصیل با معدل عالی و پروژه پایانی در حوزه توسعه وب',
                'description_en' => 'Graduated with honors; final project focused on web development.',
                'order' => 0,
            ],
            [
                'institution' => 'دانشگاه صنعتی امیرکبیر',
                'institution_en' => 'Amirkabir University of Technology',
                'degree' => 'کارشناسی ارشد',
                'degree_en' => 'Master of Science',
                'field_of_study' => 'مهندسی کامپیوتر',
                'field_of_study_en' => 'Computer Engineering',
                'grade' => null,
                'start_date' => '2023-09-01',
                'end_date' => null,
                'is_current' => true,
                'description' => 'در حال تحصیل با تمرکز بر معماری نرم‌افزار',
                'description_en' => 'Currently studying with a focus on software architecture.',
                'order' => 1,
            ],
        ];

        foreach ($educations as $education) {
            Education::firstOrCreate(
                ['institution' => $education['institution'], 'degree' => $education['degree']],
                $education
            );
        }

        // ====== تجربیات کاری (فاز ۶) ======
        $experiences = [
            [
                'company' => 'شرکت فناوری آریا',
                'company_en' => 'Aria Technology Co.',
                'position' => 'توسعه‌دهنده ارشد لاراول',
                'position_en' => 'Senior Laravel Developer',
                'employment_type' => 'full-time',
                'location' => 'تهران',
                'location_en' => 'Tehran, Iran',
                'start_date' => '2022-03-01',
                'end_date' => null,
                'is_current' => true,
                'description' => 'توسعه و نگهداری سیستم‌های وب مبتنی بر لاراول، رهبری تیم فنی و طراحی معماری',
                'description_en' => 'Developing and maintaining Laravel-based web systems, leading the technical team and designing software architecture.',
                'technologies' => ['Laravel', 'Livewire', 'MySQL', 'Redis', 'Docker'],
                'company_url' => null,
                'order' => 0,
            ],
            [
                'company' => 'استارتاپ وب‌سازان',
                'company_en' => 'Web Builders Startup',
                'position' => 'برنامه‌نویس فول‌استک',
                'position_en' => 'Full-stack Developer',
                'employment_type' => 'full-time',
                'location' => 'تهران',
                'location_en' => 'Tehran, Iran',
                'start_date' => '2020-06-01',
                'end_date' => '2022-02-01',
                'is_current' => false,
                'description' => 'طراحی و پیاده‌سازی پنل مدیریت، اپلیکیشن‌های وب و API',
                'description_en' => 'Designing and building admin dashboards, web applications and APIs.',
                'technologies' => ['Laravel', 'Vue.js', 'Tailwind CSS'],
                'company_url' => 'https://websazan.example.com',
                'order' => 1,
            ],
            [
                'company' => 'پروژه‌های فریلنس',
                'company_en' => 'Freelance Projects',
                'position' => 'توسعه‌دهنده وب',
                'position_en' => 'Web Developer',
                'employment_type' => 'freelance',
                'location' => 'دورکاری',
                'location_en' => 'Remote',
                'start_date' => '2019-01-01',
                'end_date' => '2020-05-01',
                'is_current' => false,
                'description' => 'طراحی سایت‌های شخصی و شرکتی برای مشتریان مختلف',
                'description_en' => 'Building personal and corporate websites for a variety of clients.',
                'technologies' => ['PHP', 'JavaScript', 'WordPress'],
                'company_url' => null,
                'order' => 2,
            ],
        ];

        foreach ($experiences as $experience) {
            Experience::firstOrCreate(
                ['company' => $experience['company'], 'position' => $experience['position']],
                $experience
            );
        }

        // ====== پروفایل رزومه (فاز ۶ — اطلاعات شخصی) ======
        ResumeProfile::firstOrCreate(
            ['is_active' => true],
            [
                'name' => 'علی شقاقی',
                'name_en' => 'Ali Shaghaghi',
                'job_title' => 'توسعه‌دهنده وب',
                'job_title_en' => 'Web Developer',
                'location' => 'تهران، ایران',
                'location_en' => 'Tehran, Iran',
                'email' => 'hello@alishaghaghi.ir',
                'github_url' => 'https://github.com/ali-shaghaghi',
                'linkedin_url' => null,
                'avatar' => null,
                'typewriter_texts' => [
                    'توسعه‌دهنده وب',
                    'متخصص Laravel و Livewire',
                    'طراح رابط کاربری مدرن',
                    'عاشق تجربه کاربری بی‌نقص',
                ],
                'typewriter_texts_en' => [
                    'Web Developer',
                    'Laravel & Livewire specialist',
                    'Modern UI designer',
                    'Passionate about flawless UX',
                ],
                'bio' => 'توسعه‌دهنده وب با تمرکز بر Laravel و Livewire؛ از لندینگ‌پیج‌های ساده تا داشبوردهای پیچیده. معماری تمیز، سرعت بالا و تجربه روان کاربر اولویت‌های اصلی کار من هستند.',
                'bio_en' => 'A web developer focused on Laravel and Livewire — from simple landing pages to complex dashboards. Clean architecture, high performance and a smooth user experience are my top priorities.',
                'is_active' => true,
            ]
        );

        // ====== زبان‌ها (فاز ۶.۷) ======
        $languages = [
            [
                'name' => 'انگلیسی',
                'name_en' => 'English',
                'code' => 'en',
                'level' => 'advanced',
                'proficiency' => 85,
                'description' => 'قادر به مکالمه فنی و مطالعه مستندات',
                'description_en' => 'Able to hold technical conversations and read documentation.',
                'order' => 0,
            ],
            [
                'name' => 'عربی',
                'name_en' => 'Arabic',
                'code' => 'ar',
                'level' => 'intermediate',
                'proficiency' => 50,
                'description' => null,
                'order' => 1,
            ],
            [
                'name' => 'فارسی',
                'name_en' => 'Persian',
                'code' => 'fa',
                'level' => 'native',
                'proficiency' => 100,
                'description' => 'زبان مادری',
                'description_en' => 'Native language.',
                'order' => 2,
            ],
        ];

        foreach ($languages as $language) {
            Language::firstOrCreate(
                ['name' => $language['name']],
                array_merge($language, ['is_active' => true, 'show_in_resume' => true])
            );
        }
    }
}
