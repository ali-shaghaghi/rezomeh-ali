<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ستون‌های انگلیسی برای دسته‌بندی‌ها (وبلاگ + نمونه‌کار) و انواع مشاوره
     * تا نسخه انگلیسی سایت برای کاربران خارجی کامل باشد.
     */
    public function up(): void
    {
        // ponytail: این migration قبل از migration‌های 000300+ اجرا می‌شود که
        // جداول product_categories / consultation_types را می‌سازند. بدون این
        // guardها، اجرای fresh (مثلاً sqlite درون‌حافظه تست‌ها) با
        // "no such table" شکست می‌خورد. در دیتابیس اصلی که از قبل اجرا شده،
        // این migration یک no-op امن است.
        if (Schema::hasTable('categories')) {
            Schema::table('categories', function (Blueprint $table) {
                if (! Schema::hasColumn('categories', 'name_en')) {
                    $table->string('name_en')->nullable()->after('name');
                }
                if (! Schema::hasColumn('categories', 'description_en')) {
                    $table->text('description_en')->nullable()->after('description');
                }
            });
        }

        if (Schema::hasTable('product_categories')) {
            Schema::table('product_categories', function (Blueprint $table) {
                if (! Schema::hasColumn('product_categories', 'name_en')) {
                    $table->string('name_en')->nullable()->after('name');
                }
                if (! Schema::hasColumn('product_categories', 'description_en')) {
                    $table->text('description_en')->nullable()->after('description');
                }
            });
        }

        if (Schema::hasTable('consultation_types')) {
            Schema::table('consultation_types', function (Blueprint $table) {
                if (! Schema::hasColumn('consultation_types', 'name_en')) {
                    $table->string('name_en')->nullable()->after('name');
                }
                if (! Schema::hasColumn('consultation_types', 'description_en')) {
                    $table->text('description_en')->nullable()->after('description');
                }
            });
        }

        if (Schema::hasTable('languages')) {
            Schema::table('languages', function (Blueprint $table) {
                if (! Schema::hasColumn('languages', 'name_en')) {
                    $table->string('name_en')->nullable()->after('name');
                }
                if (! Schema::hasColumn('languages', 'description_en')) {
                    $table->string('description_en')->nullable()->after('description');
                }
            });
        }
    }

    public function down(): void
    {
        foreach (['categories', 'product_categories', 'consultation_types', 'languages'] as $t) {
            if (Schema::hasTable($t)) {
                Schema::table($t, function (Blueprint $table) use ($t) {
                    $drop = array_filter(['name_en', 'description_en'], fn ($c) => Schema::hasColumn($t, $c));
                    if (! empty($drop)) {
                        $table->dropColumn($drop);
                    }
                });
            }
        }
    }
};
