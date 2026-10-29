<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * متاهای SEO داینامیک per-page (#ماژول سئو).
 * route_name کلید صفحه است (مثل public.products.index) — فارسی و انگلیسی جدا.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_metas', function (Blueprint $table) {
            $table->id();
            $table->string('route_name')->index();       // public.products.index
            $table->string('locale', 5)->default('fa');  // fa | en
            $table->string('title')->nullable();          // حداکثر ۶۰ کاراکتر
            $table->text('description')->nullable();      // حداکثر ۱۶۰ کاراکتر
            $table->text('keywords')->nullable();         // جداشده با کاما
            $table->string('og_image')->nullable();       // تصویر OG در storage
            $table->string('schema_type')->nullable();    // Product | Service | Article | ...
            $table->text('faq_json')->nullable();         // FAQPage schema — JSON [{q,a}]
            $table->boolean('noindex')->default(false);   // مسدود از ایندکس
            $table->timestamps();

            $table->unique(['route_name', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_metas');
    }
};
