<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // پروفایل رزومه — singleton (فقط یک رکورد؛ id=1)
        Schema::create('resume_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // نام کامل
            $table->string('job_title'); // عنوان شغلی (تغییر رییل با typewriter)
            $table->string('location')->nullable(); // موقعیت مکانی
            $table->string('email')->nullable(); // ایمیل تماس
            $table->string('github_url')->nullable(); // لینک گیت‌هاب
            $table->string('linkedin_url')->nullable(); // لینک لینکدین
            $table->string('resume_file')->nullable(); // فایل رزومه (PDF)
            $table->string('avatar')->nullable(); // تصویر شخص
            $table->json('typewriter_texts')->nullable(); // متن‌های تایپ‌رایتر عنوان شغلی
            $table->text('bio')->nullable(); // توضیحات (بایو)
            $table->boolean('is_active')->default(true); // وضعیت انتشار
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resume_profiles');
    }
};
