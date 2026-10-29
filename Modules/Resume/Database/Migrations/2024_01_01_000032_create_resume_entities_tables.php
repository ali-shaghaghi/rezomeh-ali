<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // جدول مهارت‌ها
        Schema::create('skills', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->enum('category', ['frontend', 'backend', 'devops', 'database', 'mobile', 'soft-skills', 'other'])->default('other');
            $table->unsignedTinyInteger('proficiency')->default(0); // درصد تسلط ۰ تا ۱۰۰
            $table->string('icon')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('order')->default(0);
            $table->boolean('show_in_resume')->default(true);
            $table->timestamps();
        });

        // جدول تحصیلات
        Schema::create('educations', function (Blueprint $table) {
            $table->id();
            $table->string('institution'); // دانشگاه / موسسه
            $table->string('degree'); // مقطع تحصیلی
            $table->string('field_of_study'); // رشته
            $table->string('grade')->nullable(); // معدل / نمره
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_current')->default(false);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('order')->default(0);
            $table->boolean('show_in_resume')->default(true);
            $table->timestamps();
        });

        // جدول تجربیات کاری
        Schema::create('experiences', function (Blueprint $table) {
            $table->id();
            $table->string('company'); // نام شرکت
            $table->string('position'); // عنوان شغلی
            $table->enum('employment_type', ['full-time', 'part-time', 'freelance', 'remote', 'internship'])->default('full-time');
            $table->string('location')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_current')->default(false);
            $table->text('description')->nullable();
            $table->json('technologies')->nullable();
            $table->string('company_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('order')->default(0);
            $table->boolean('show_in_resume')->default(true);
            $table->timestamps();
        });

        // جدول میانی پروژه ↔ مهارت
        Schema::create('project_skill', function (Blueprint $table) {
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('skill_id')->constrained()->cascadeOnDelete();
            $table->primary(['project_id', 'skill_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_skill');
        Schema::dropIfExists('experiences');
        Schema::dropIfExists('educations');
        Schema::dropIfExists('skills');
    }
};
