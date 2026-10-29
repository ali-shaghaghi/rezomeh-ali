<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * مشاوران از جدول ادمین‌ها (اعضای تیم) خوانده می‌شوند:
 *
 *  - admin_id: اتصال مشاور به عضو تیم (admins) — از جمله خود صاحب سایت.
 *  - user_id برای مشاوران فقط-ادمین null می‌شود (الگوی staff_authorship).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consultants', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->change();
        });

        Schema::table('consultants', function (Blueprint $table) {
            $table->foreignId('admin_id')->nullable()->after('user_id')
                ->constrained('admins')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('consultants', function (Blueprint $table) {
            $table->dropColumn('admin_id');
        });

        Schema::table('consultants', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete()->change();
        });
    }
};
