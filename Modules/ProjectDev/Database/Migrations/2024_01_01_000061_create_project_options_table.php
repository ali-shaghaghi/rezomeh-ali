<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // ماژول Projects — ویژگی‌ها/امکانات قیمت‌گذاری‌شده هر نوع پروژه (یا عمومی)
    public function up(): void
    {
        Schema::create('project_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_type_id')->nullable()->constrained('project_types')->nullOnDelete();
            $table->string('name', 190);
            $table->text('description')->nullable();
            $table->decimal('price', 15, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_options');
    }
};
