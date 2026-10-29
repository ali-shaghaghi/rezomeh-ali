<?php

namespace Modules\SEO\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\SEO\Models\SeoMeta;

/**
 * سیدر SEO — متاهای بهینه برای صفحات اصلی (#ماژول سئو).
 *
 * اجرا: php artisan db:seed --class="Modules\SEO\Database\Seeders\SeoMetaSeeder"
 *
 * متاهای پیش‌فرض حرفه‌ای برای صفحات کلیدی تا بدون دانش سئو هم صفحات
 * از پایه بهینه باشند؛ ادمین بعداً از پنل /admin/seo ویرایش می‌کند.
 */
class SeoMetaSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->defaults() as $route => $locales) {
            foreach ($locales as $locale => $data) {
                // updateOrCreate تا اجرای دوباره سیدر داده تکراری نسازد
                SeoMeta::updateOrCreate(
                    ['route_name' => $route, 'locale' => $locale],
                    $data
                );
            }
        }

        $this->command?->info('SEO metas seeded: '.count($this->defaults()).' pages × 2 locales');
    }

    /**
     * @return array<string, array<string, array<string, mixed>>>
     */
    private function defaults(): array
    {
        return [
            'public.home' => $this->home(),
            'public.products.index' => $this->products(),
            'public.portfolio.index' => $this->portfolio(),
            'public.consultation.index' => $this->consultation(),
            'public.blog.index' => $this->blog(),
            'public.project.request' => $this->projectRequest(),
        ];
    }

    private function home(): array
    {
        return [
            'fa' => [
                'title' => 'علی شقاقی | طراحی سایت، ساخت CRM و مشاوره برنامه‌نویسی',
                'description' => 'طراحی سایت حرفه‌ای، ساخت CRM اختصاصی، انجام پروژه‌های برنامه‌نویسی و مشاوره آنلاین برای کسب‌وکارها. نمونه‌کار واقعی و محصولات آماده با لایسنس.',
                'keywords' => 'طراحی سایت, ساخت CRM, انجام پروژه برنامه نویسی, مشاوره برنامه نویسی, علی شقاقی, توسعه دهنده وب',
                'schema_type' => 'ProfessionalService',
                'noindex' => false,
                'faq_json' => [
                    ['q' => 'هزینه طراحی سایت چقدر است؟', 'a' => 'بسته به نوع پروژه متفاوت است؛ سایت شرکتی از حدود ۱۵ میلیون تومان و CRM اختصاصی از ۵۰ میلیون تومان شروع می‌شود. برای قیمت دقیق، درخواست پروژه ثبت کنید یا مشاوره رزرو کنید.'],
                    ['q' => 'چطور می‌توانم پروژه برنامه‌نویسی سفارش دهم؟', 'a' => 'از صفحه «انجام پروژه» فرم درخواست را پر کنید. ظرف ۲۴ ساعت بررسی و قیمت‌گذاری می‌شود و پس از تایید، پرداخت اقساطی ۳۰/۳۰/۴۰ انجام می‌شود.'],
                    ['q' => 'آیا مشاوره آنلاین هم دارید؟', 'a' => 'بله، جلسات مشاوره آنلاین برای انتخاب تکنولوژی، معماری پروژه و دیجیتال‌سازی کسب‌وکار از صفحه مشاوره قابل رزرو است.'],
                ],
            ],
            'en' => [
                'title' => 'Ali Shaghaghi | Web Development, Custom CRM & Consulting',
                'description' => 'Professional web development, custom CRM solutions, project outsourcing and online business consulting. Real portfolio and ready-to-use digital products.',
                'keywords' => 'web development, custom CRM, hire web developer, software consulting, project outsourcing',
                'schema_type' => 'ProfessionalService',
                'noindex' => false,
                'faq_json' => [
                    ['q' => 'How much does a custom website cost?', 'a' => 'Pricing depends on scope: corporate websites start around $500 and custom CRM systems from $1,500. Submit a project request for an exact quote within 24 hours.'],
                    ['q' => 'Can I hire you for a programming project?', 'a' => 'Yes — fill out the project request form. Every request is reviewed and priced within 24 hours, with a 30/30/40 installment payment plan.'],
                    ['q' => 'Do you offer online consulting?', 'a' => 'Yes, online consulting sessions for tech stack selection, project architecture and digital transformation can be booked from the consulting page.'],
                ],
            ],
        ];
    }

    private function products(): array
    {
        return [
            'fa' => [
                'title' => 'خرید قالب سایت، CRM آماده و اسکریپت | علی شقاقی',
                'description' => 'فروش محصولات دیجیتال: قالب‌های آماده سایت، سیستم CRM سازمانی و اسکریپت‌های کاربردی با لایسنس معتبر، آپدیت و پشتیبانی. دانلود فوری پس از خرید.',
                'keywords' => 'خرید قالب سایت, خرید CRM, قالب آماده, اسکریپت فروشگاه, محصولات دیجیتال, لایسنس',
                'schema_type' => 'ItemList',
                'noindex' => false,
                'faq_json' => [
                    ['q' => 'محصولات چطور تحویل داده می‌شوند؟', 'a' => 'بلافاصله پس از پرداخت موفق، لینک دانلود در پنل کاربری شما فعال می‌شود و لایسنس محصول صادر می‌گردد.'],
                    ['q' => 'لایسنس محصولات چند نوع است؟', 'a' => 'سه نوع: تک‌کاربره، چندکاربره (تا ۵ نصب) و مادام‌العمر. قیمت هر نوع در صفحه محصول نمایش داده می‌شود.'],
                ],
            ],
            'en' => [
                'title' => 'Buy Website Templates, CRM Systems & Scripts | Ali Shaghaghi',
                'description' => 'Digital products store: ready-made website templates, business CRM systems and useful scripts with valid license, updates and support. Instant download.',
                'keywords' => 'buy website template, buy CRM software, php scripts, digital products, license',
                'schema_type' => 'ItemList',
                'noindex' => false,
                'faq_json' => [
                    ['q' => 'How are products delivered?', 'a' => 'Right after successful payment, your download link activates in your dashboard and the product license is issued instantly.'],
                    ['q' => 'What license types are available?', 'a' => 'Three types: single-use, multi-use (up to 5 installs) and lifetime. Pricing per type is shown on each product page.'],
                ],
            ],
        ];
    }

    private function portfolio(): array
    {
        return [
            'fa' => [
                'title' => 'نمونه‌کارهای برنامه‌نویسی و طراحی سایت | علی شقاقی',
                'description' => 'پروژه‌های واقعی انجام‌شده: فروشگاه اینترنتی، CRM سازمانی، سایت شرکتی و پلتفرم‌های وب با تکنولوژی‌های Laravel، Vue و Tailwind.',
                'keywords' => 'نمونه کار برنامه نویسی, پروژه انجام شده, طراحی سایت نمونه, پورتفولیو برنامه نویس',
                'schema_type' => 'CollectionPage',
                'noindex' => false,
                'faq_json' => [],
            ],
            'en' => [
                'title' => 'Web Development Portfolio & Case Studies | Ali Shaghaghi',
                'description' => 'Real completed projects: e-commerce stores, enterprise CRM systems, corporate websites and web platforms built with Laravel, Vue and Tailwind.',
                'keywords' => 'web development portfolio, case studies, laravel projects',
                'schema_type' => 'CollectionPage',
                'noindex' => false,
                'faq_json' => [],
            ],
        ];
    }

    private function consultation(): array
    {
        return [
            'fa' => [
                'title' => 'رزرو مشاوره آنلاین برنامه‌نویسی و کسب‌وکار | علی شقاقی',
                'description' => 'مشاوره آنلاین تخصصی برای کسب‌وکارها: انتخاب تکنولوژی، معماری پروژه نرم‌افزاری، دیجیتال‌سازی و انتخاب CRM مناسب. رزرو آنلاین در چند دقیقه.',
                'keywords' => 'مشاوره آنلاین برنامه نویسی, مشاوره کسب و کار, مشاور فنی, انتخاب تکنولوژی, مشاوره CRM',
                'schema_type' => 'Service',
                'noindex' => false,
                'faq_json' => [
                    ['q' => 'جلسه مشاوره چگونه برگزار می‌شود؟', 'a' => 'پس از رزرو آنلاین، لینک جلسه آنلاین برای شما ارسال می‌شود. جلسه با تحلیل نیازهای کسب‌وکار شما شروع و با نقشه راه عملی پایان می‌یابد.'],
                    ['q' => 'مشاوره برای چه کسب‌وکارهایی مناسب است؟', 'a' => 'برای هر کسب‌وکاری که به سایت، نرم‌افزار اختصاصی، CRM یا دیجیتال‌سازی فرآیندها نیاز دارد — از فروشگاه کوچک تا شرکت سازمانی.'],
                    ['q' => 'قبل از سفارش پروژه باید مشاوره بگیرم؟', 'a' => 'مشاوره اختیاری است اما توصیه می‌شود؛ چون با تحلیل دقیق نیازها، هزینه و زمان پروژه بهینه می‌شود و از بازکارکرد جلوگیری می‌کند.'],
                ],
            ],
            'en' => [
                'title' => 'Book Online Software & Business Consulting | Ali Shaghaghi',
                'description' => 'Online consulting for businesses: technology selection, software architecture, digital transformation and CRM choice. Book your session in minutes.',
                'keywords' => 'online software consulting, business technology consulting, CRM consulting, software architecture',
                'schema_type' => 'Service',
                'noindex' => false,
                'faq_json' => [
                    ['q' => 'How does the consulting session work?', 'a' => 'After booking online, you receive a session link. The session starts with analyzing your business needs and ends with a practical roadmap.'],
                    ['q' => 'Who is this consulting for?', 'a' => 'Any business that needs a website, custom software, CRM or process digitalization — from small shops to enterprise companies.'],
                ],
            ],
        ];
    }

    private function blog(): array
    {
        return [
            'fa' => [
                'title' => 'مقالات برنامه‌نویسی و آموزش فنی | وبلاگ علی شقاقی',
                'description' => 'مقالات آموزشی برنامه‌نویسی: Laravel، PHP، معماری نرم‌افزار، تجربیات واقعی پروژه‌ها و نکات فنی کاربردی برای توسعه‌دهندگان.',
                'keywords' => 'مقالات برنامه نویسی, آموزش لاراول, آموزش php, وبلاگ فنی, تجربه پروژه',
                'schema_type' => 'Blog',
                'noindex' => false,
                'faq_json' => [],
            ],
            'en' => [
                'title' => 'Programming Articles & Tutorials | Ali Shaghaghi Blog',
                'description' => 'Technical articles: Laravel, PHP, software architecture, real project experiences and practical tips for developers.',
                'keywords' => 'programming articles, laravel tutorial, php tips, software architecture',
                'schema_type' => 'Blog',
                'noindex' => false,
                'faq_json' => [],
            ],
        ];
    }

    private function projectRequest(): array
    {
        return [
            'fa' => [
                'title' => 'سفارش پروژه برنامه‌نویسی | انجام پروژه با علی شقاقی',
                'description' => 'ثبت درخواست پروژه برنامه‌نویسی: طراحی سایت، CRM اختصاصی، نرم‌افزار تحت وب. بررسی و قیمت‌گذاری ظرف ۲۴ ساعت، پرداخت اقساطی ۳۰/۳۰/۴۰.',
                'keywords' => 'سفارش پروژه برنامه نویسی, انجام پروژه سایت, ساخت نرم افزار, قیمت طراحی سایت',
                'schema_type' => 'Service',
                'noindex' => false,
                'faq_json' => [
                    ['q' => 'پروژه من در چه مدت آماده می‌شود؟', 'a' => 'بسته به حجم پروژه، بین ۳ تا ۱۰ روز کاری برای پروژه‌های معمول و بیشتر برای سیستم‌های سازمانی. زمان دقیق پس از بررسی اعلام می‌شود.'],
                    ['q' => 'پرداخت چگونه است؟', 'a' => 'پلن ۳۰/۳۰/۴۰: ۳۰٪ پیش‌پرداخت، ۳۰٪ در تحویل اولیه و ۴۰٪ در تحویل نهایی. پرداخت از طریق درگاه آنلاین امن انجام می‌شود.'],
                ],
            ],
            'en' => [
                'title' => 'Hire a Web Developer | Project Request | Ali Shaghaghi',
                'description' => 'Submit your programming project request: website development, custom CRM, web software. Reviewed and priced within 24 hours, 30/30/40 installments.',
                'keywords' => 'hire web developer, software project request, custom website development',
                'schema_type' => 'Service',
                'noindex' => false,
                'faq_json' => [
                    ['q' => 'How long does a project take?', 'a' => 'Depending on scope, 3 to 10 business days for standard projects; longer for enterprise systems. Exact timeline is given after review.'],
                    ['q' => 'What is the payment plan?', 'a' => 'A 30/30/40 plan: 30% upfront, 30% at first delivery and 40% at final handover, paid through a secure online gateway.'],
                ],
            ],
        ];
    }
}
