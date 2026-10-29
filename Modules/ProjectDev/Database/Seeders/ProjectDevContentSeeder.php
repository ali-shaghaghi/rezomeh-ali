<?php

namespace Modules\ProjectDev\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\ProjectDev\Models\ProjectOption;
use Modules\ProjectDev\Models\ProjectType;

class ProjectDevContentSeeder extends Seeder
{
    /**
     * کاتالوگ اولیه قیمت‌گذاری — انواع پروژه + ویژگی‌ها و قیمت هرکدام (idempotent)
     */
    public function run(): void
    {
        $catalog = [
            [
                'name' => 'طراحی سایت فروشگاهی (لاراول)',
                'description' => 'فروشگاه اینترنتی کامل با لاراول — سبد خرید، سفارش‌گیری و مدیریت محصولات',
                'base_price' => 25000000,
                'options' => [
                    ['name' => 'طراحی درگاه پرداخت آنلاین', 'price' => 8000000],
                    ['name' => 'پنل ادمین اختصاصی', 'price' => 10000000],
                    ['name' => 'پنل کاربری مشتریان', 'price' => 6000000],
                    ['name' => 'سبد خرید پیشرفته و کد تخفیف', 'price' => 4000000],
                    ['name' => 'گزارش‌گیری فروش و نمودارها', 'price' => 5000000],
                    ['name' => 'اتصال به تاییدیه SMS', 'price' => 2500000],
                ],
            ],
            [
                'name' => 'طراحی سایت شرکتی',
                'description' => 'وب‌سایت معرفی شرکت با خدمات، نمونه‌کارها و فرم تماس',
                'base_price' => 12000000,
                'options' => [
                    ['name' => 'چندزبانه', 'price' => 4000000],
                    ['name' => 'وبلاگ داخلی', 'price' => 3000000],
                    ['name' => 'فرم تماس پیشرفته', 'price' => 2000000],
                    ['name' => 'اسلایدشو و گالری', 'price' => 1500000],
                ],
            ],
            [
                'name' => 'طراحی سایت شخصی / رزومه',
                'description' => 'سایت شخصی با رزومه، نمونه‌کارها و وبلاگ',
                'base_price' => 7000000,
                'options' => [
                    ['name' => 'نمونه‌کار تعاملی', 'price' => 2500000],
                    ['name' => 'وبلاگ شخصی', 'price' => 2000000],
                    ['name' => 'فرم تماس', 'price' => 1000000],
                ],
            ],
            [
                'name' => 'اپلیکیشن تحت وب (SaaS)',
                'description' => 'سامانه تحت وب با پنل کاربران و اشتراک‌گذاری',
                'base_price' => 45000000,
                'options' => [
                    ['name' => 'پنل ادمین اختصاصی', 'price' => 10000000],
                    ['name' => 'سیستم اشتراک و پرداخت دوره‌ای', 'price' => 9000000],
                    ['name' => 'API اختصاصی', 'price' => 6000000],
                    ['name' => 'گزارش‌گیری پیشرفته', 'price' => 5000000],
                ],
            ],
            [
                'name' => 'ربات تلگرام',
                'description' => 'ربات تلگرام با منوی داینامیک و مدیریت محتوا',
                'base_price' => 9000000,
                'options' => [
                    ['name' => 'پنل مدیریت ربات', 'price' => 5000000],
                    ['name' => 'درگاه پرداخت داخل ربات', 'price' => 6000000],
                ],
            ],
        ];

        $order = 0;
        foreach ($catalog as $typeDef) {
            $type = ProjectType::firstOrCreate(
                ['name' => $typeDef['name']],
                [
                    'slug' => \Illuminate\Support\Str::slug($typeDef['name']) ?: 'type-'.\Illuminate\Support\Str::random(6),
                    'description' => $typeDef['description'],
                    'base_price' => $typeDef['base_price'],
                    'is_active' => true,
                    'sort_order' => $order++,
                ]
            );

            foreach ($typeDef['options'] as $i => $optionDef) {
                ProjectOption::firstOrCreate(
                    ['project_type_id' => $type->id, 'name' => $optionDef['name']],
                    [
                        'description' => null,
                        'price' => $optionDef['price'],
                        'is_active' => true,
                        'sort_order' => $i,
                    ]
                );
            }
        }

        // ویژگی‌های عمومی مشترک بین همه انواع پروژه
        $globalOptions = [
            ['name' => 'بهینه‌سازی SEO پایه', 'price' => 2000000],
            ['name' => 'طراحی رابط کاربری اختصاصی (UI)', 'price' => 3500000],
            ['name' => 'طراحی واکنش‌گرا (Responsive)', 'price' => 1500000],
            ['name' => 'پشتیبانی ۳ ماهه پس از تحویل', 'price' => 3000000],
        ];

        foreach ($globalOptions as $i => $optionDef) {
            ProjectOption::firstOrCreate(
                ['project_type_id' => null, 'name' => $optionDef['name']],
                ['description' => null, 'price' => $optionDef['price'], 'is_active' => true, 'sort_order' => $i]
            );
        }
    }
}
