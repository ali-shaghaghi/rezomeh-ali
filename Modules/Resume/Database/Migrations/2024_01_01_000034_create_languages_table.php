<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // جدول زبان‌های خارجی رزومه (فاز ۶.۷)
        Schema::create('languages', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // نام زبان (فارسی)
            $table->string('code', 10)->nullable(); // کد زبان (en, fa, ar, ...)
            $table->enum('level', ['beginner', 'intermediate', 'advanced', 'fluent', 'native'])->default('intermediate');
            $table->unsignedTinyInteger('proficiency')->default(0); // درصد تسلط ۰ تا ۱۰۰
            $table->string('description')->nullable(); // توضیح (مثل مدرک آیلتس)
            $table->boolean('is_active')->default(true);
            $table->integer('order')->default(0);
            $table->boolean('show_in_resume')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('languages');
    }
};
