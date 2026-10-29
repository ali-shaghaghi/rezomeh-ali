<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // مایگریشن ماژول تنظیمات — جدول key-value تنظیمات
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('group')->default('general'); // general | smtp | payment | ai | social
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->boolean('is_public')->default(false); // قابل نمایش در فرانت‌اند؟
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
