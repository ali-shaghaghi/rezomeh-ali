<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // ماژول Projects — درخواست پروژه جدید کاربر (بررسی ادمین → سفارش/فاکتور نهایی)
    public function up(): void
    {
        Schema::create('project_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_type_id')->nullable()->constrained('project_types')->nullOnDelete();
            $table->string('project_type_name', 150);
            $table->string('title', 190);
            $table->text('description')->nullable();
            $table->json('selected_options')->nullable();  // ویژگی‌های انتخابی از کاتالوگ [{name, price}]
            $table->json('custom_options')->nullable();    // ویژگی‌های دلخواه کاربر [{name, note}]
            $table->json('features_snapshot')->nullable(); // اسنپ‌شات کل ویژگی‌ها هنگام ثبت
            $table->decimal('estimated_price', 15, 2)->default(0); // قیمت تخمینی سیستم
            $table->decimal('final_price', 15, 2)->nullable();     // قیمت نهایی ادمین (فاکتور)
            $table->string('difficulty', 20)->nullable();          // low, medium, high
            $table->unsignedInteger('estimated_days')->nullable(); // زمان انجام
            $table->timestamp('deadline')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'converted'])->default('pending');
            $table->text('admin_note')->nullable();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_requests');
    }
};
