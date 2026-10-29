<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| فیلدهای سئوی دو‌زبانه برای مقالات
|--------------------------------------------------------------------------
| برای سئوی نسخه انگلیسی (خارجی) هر مقاله، عنوان و توضیح متای انگلیسی
| نیاز است. این فیلدها اختیاری‌اند؛ در صورت خالی بودن، نسخه فارسی نمایش
| داده می‌شود.
*/

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->string('seo_title_en')->nullable()->after('seo_title');
            $table->string('seo_description_en', 500)->nullable()->after('seo_description');
        });
    }

    public function down(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropColumn(['seo_title_en', 'seo_description_en']);
        });
    }
};
