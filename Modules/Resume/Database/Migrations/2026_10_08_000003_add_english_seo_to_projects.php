<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * فیلدهای انگلیسی نمونه‌کار: اسلاگ انگلیسی + سئوی انگلیسی
 * (عنوان/توضیح کوتاه/توضیح کامل انگلیسی از قبل وجود داشتند.)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('slug_en')->nullable()->unique()->after('slug');
            $table->string('seo_title_en')->nullable()->after('seo_title');
            $table->string('seo_description_en', 500)->nullable()->after('seo_description');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropUnique(['slug_en']);
            $table->dropColumn(['slug_en', 'seo_title_en', 'seo_description_en']);
        });
    }
};
