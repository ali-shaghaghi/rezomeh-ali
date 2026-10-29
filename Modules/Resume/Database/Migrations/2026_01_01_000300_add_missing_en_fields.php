<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ستون‌های انگلیسی جا‌افتاده در migr قبلی.
 *   - educations.description_en
 *   - experiences.location_en
 * این دو فیلد در ResumeContentSeeder پر می‌شوند ولی در دیتابیس نبودند.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('educations', function (Blueprint $t): void {
            if (! Schema::hasColumn('educations', 'description_en')) {
                $t->text('description_en')->nullable()->after('description');
            }
        });
        Schema::table('experiences', function (Blueprint $t): void {
            if (! Schema::hasColumn('experiences', 'location_en')) {
                $t->string('location_en', 191)->nullable()->after('location');
            }
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('educations', 'description_en')) {
            Schema::table('educations', fn (Blueprint $t) => $t->dropColumn('description_en'));
        }
        if (Schema::hasColumn('experiences', 'location_en')) {
            Schema::table('experiences', fn (Blueprint $t) => $t->dropColumn('location_en'));
        }
    }
};
