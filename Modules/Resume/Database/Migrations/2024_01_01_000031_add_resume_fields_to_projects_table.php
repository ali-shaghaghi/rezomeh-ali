<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // بازنگری جدول پروژه‌ها طبق طرح فاز ۶ — بدون تغییر ساختار موجود
        Schema::table('projects', function (Blueprint $table) {
            $table->string('short_description', 500)->nullable()->after('slug');
            $table->json('gallery')->nullable()->after('cover_image');
            $table->string('client_name')->nullable()->after('category_id');
            $table->date('project_date')->nullable()->after('client_name');
            $table->integer('order')->default(0)->after('featured');
            $table->enum('demo_type', ['link', 'external_url', 'subdomain'])->default('link')->after('demo_url');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['short_description', 'gallery', 'client_name', 'project_date', 'order', 'demo_type']);
        });
    }
};
