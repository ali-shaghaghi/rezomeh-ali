<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // مایگریشن ماژول محصولات — جدول محصولات
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('product_categories')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->text('short_description')->nullable();
            $table->decimal('price', 15, 2)->default(0);
            $table->decimal('sale_price', 15, 2)->nullable();
            $table->string('type')->default('template'); // template, crm, script, other
            $table->string('file_path')->nullable(); // مسیر فایل دانلودی (storage/app/private)
            $table->string('demo_url')->nullable();
            $table->string('version', 32)->default('1.0.0');
            $table->boolean('is_published')->default(true);
            $table->boolean('show_in_resume')->default(false); // نمایش در صفحه رزومه
            $table->string('cover_image')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
