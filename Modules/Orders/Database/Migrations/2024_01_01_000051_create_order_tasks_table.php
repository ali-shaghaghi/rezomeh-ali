<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // تسک‌های هر سفارش (تسک‌محور) — پیشرفت پروژه از جمع این تسک‌ها محاسبه می‌شود
    public function up(): void
    {
        Schema::create('order_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('status', ['todo', 'in_progress', 'done'])->default('todo');
            $table->date('start_date')->nullable(); // شروع بازه کاری (مثلاً ۱ فروردین)
            $table->date('due_date')->nullable();   // پایان بازه کاری (مثلاً ۱۰ فروردین)
            $table->unsignedTinyInteger('progress')->default(0); // 0 تا 100
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_tasks');
    }
};
