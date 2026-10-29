<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * محصولات دو‌زبانه شوند: name_en / description_en / short_description_en.
 * جدول product_categories از قبل name_en/description_en دارد.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            if (! Schema::hasColumn('products', 'name_en')) {
                $table->string('name_en', 191)->nullable()->after('name');
            }
            if (! Schema::hasColumn('products', 'description_en')) {
                $table->text('description_en')->nullable()->after('description');
            }
            if (! Schema::hasColumn('products', 'short_description_en')) {
                $table->text('short_description_en')->nullable()->after('short_description');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn(['name_en', 'description_en', 'short_description_en']);
        });
    }
};
