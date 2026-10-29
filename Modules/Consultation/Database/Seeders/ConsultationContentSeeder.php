<?php

namespace Modules\Consultation\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Consultation\Models\ConsultationType;

/**
 * انواع مشاوره (دو‌زبانه) — برای صفحه اصلی /consultation و بخش «مشاوره تخصصی».
 */
class ConsultationContentSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'name' => 'مشاوره فنی و معماری پروژه',
                'name_en' => 'Technical & Architecture Consulting',
                'duration_minutes' => 60,
                'mode' => 'online',
                'price' => 1500000,
                'description' => 'بررسی معماری پروژه، انتخاب تکنولوژی و مسیر توسعه',
                'description_en' => 'Reviewing project architecture, choosing the stack and the development roadmap.',
            ],
            [
                'name' => 'کدریویو و رفع باگ',
                'name_en' => 'Code Review & Debugging',
                'duration_minutes' => 45,
                'mode' => 'online',
                'price' => 1000000,
                'description' => 'بازبینی کد، رفع مشکلات عملکردی و بهبود کیفیت',
                'description_en' => 'Reviewing code, fixing performance issues and improving quality.',
            ],
            [
                'name' => 'مشاوره مسیر شغلی',
                'name_en' => 'Career Path Consulting',
                'duration_minutes' => 60,
                'mode' => 'phone',
                'price' => 800000,
                'description' => 'نقشه راه ورود به دنیای برنامه‌نویسی و ارتقای شغلی',
                'description_en' => 'A roadmap for entering software development and career growth.',
            ],
        ];

        foreach ($types as $i => $t) {
            ConsultationType::firstOrCreate(
                ['name' => $t['name']],
                array_merge($t, ['is_active' => true])
            );
        }
    }
}
