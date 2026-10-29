<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| فیلدهای دو‌زبانه برای محتوای داینامیک سایت
|--------------------------------------------------------------------------
| بخش‌های داینامیکی سایت (پروفایل رزومه، مهارت‌ها، تجربیات، پروژه‌ها،
| مقالات) که نام/توضیحات فارسی دارند، برای زبان انگلیسی نیازمند فیلد
| جداگانه‌اند. هر فیلد *_en اختیاری است؛ اگر خالی باشد، helper
| localized_field() خودش مقدار فارسی را نمایش می‌دهد.
*/

return new class extends Migration
{
    public function up(): void
    {
        // پروفایل رزومه (نام، عنوان شغلی، موقعیت، توضیحات، تایپ‌رایتر)
        Schema::table('resume_profiles', function (Blueprint $table) {
            $table->string('name_en')->nullable()->after('name');
            $table->string('job_title_en')->nullable()->after('job_title');
            $table->string('location_en')->nullable()->after('location');
            $table->text('bio_en')->nullable()->after('bio');
            $table->json('typewriter_texts_en')->nullable()->after('typewriter_texts');
        });

        // مهارت‌ها
        Schema::table('skills', function (Blueprint $table) {
            $table->string('name_en')->nullable()->after('name');
        });

        // تجربیات کاری
        Schema::table('experiences', function (Blueprint $table) {
            $table->string('company_en')->nullable()->after('company');
            $table->string('position_en')->nullable()->after('position');
            $table->text('description_en')->nullable()->after('description');
        });

        // تحصیلات
        Schema::table('educations', function (Blueprint $table) {
            $table->string('institution_en')->nullable()->after('institution');
            $table->string('degree_en')->nullable()->after('degree');
            $table->string('field_of_study_en')->nullable()->after('field_of_study');
        });

        // پروژه‌ها
        Schema::table('projects', function (Blueprint $table) {
            $table->string('title_en')->nullable()->after('title');
            $table->string('short_description_en', 500)->nullable()->after('short_description');
            $table->text('description_en')->nullable()->after('description');
        });
        // مقالات وبلاگ
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->string('title_en')->nullable()->after('title');
            $table->text('excerpt_en')->nullable()->after('excerpt');
            $table->longText('content_en')->nullable()->after('content');
        });
    }

    public function down(): void
    {
        Schema::table('resume_profiles', function (Blueprint $table) {
            $table->dropColumn(['name_en', 'job_title_en', 'location_en', 'bio_en', 'typewriter_texts_en']);
        });

        Schema::table('skills', function (Blueprint $table) {
            $table->dropColumn('name_en');
        });

        Schema::table('experiences', function (Blueprint $table) {
            $table->dropColumn(['company_en', 'position_en', 'description_en']);
        });

        Schema::table('educations', function (Blueprint $table) {
            $table->dropColumn(['institution_en', 'degree_en', 'field_of_study_en']);
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['title_en', 'short_description_en', 'description_en']);
        });

        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropColumn(['title_en', 'excerpt_en', 'content_en']);
        });
    }
};
