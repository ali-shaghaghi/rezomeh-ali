<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // اتصال نمونه‌کار به محصول + تصویر کاور نسخه انگلیسی (#درخواست کاربر)
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // تصویر کاور نسخه انگلیسی سایت (در صورت خالی بودن به کاور اصلی fallback می‌شود)
            $table->string('cover_image_en')->nullable()->after('cover_image');

            // محصول مرتبط — دکمه «جزئیات پروژه» کاربر را به صفحه همین محصول می‌برد
            $table->foreignId('related_product_id')
                ->nullable()
                ->after('category_id')
                ->constrained('products')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropConstrainedForeignId('related_product_id');
            $table->dropColumn('cover_image_en');
        });
    }
};
