<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * consultation_types دو‌زبانه شود: name_en / description_en
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consultation_types', function (Blueprint $table): void {
            if (! Schema::hasColumn('consultation_types', 'name_en')) {
                $table->string('name_en', 150)->nullable()->after('name');
            }
            if (! Schema::hasColumn('consultation_types', 'description_en')) {
                $table->text('description_en')->nullable()->after('description');
            }
        });
    }

    public function down(): void
    {
        Schema::table('consultation_types', function (Blueprint $table): void {
            $table->dropColumn(['name_en', 'description_en']);
        });
    }
};
