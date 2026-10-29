<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * پاسخ‌دهی به نظرات (threading):
 *
 *  - parent_id: نظر زیرین به کدام نظر ریشه پاسخ می‌دهد (حداکثر یک سطح عمق).
 *  - admin_id: پاسخ ادمین در پنل مدیریت — user_id برای نوشته ادمین null می‌شود
 *    (همان الگوی blog_posts/ticket_replies در مایگریشن staff_authorship).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->change();
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->after('user_id')
                ->constrained('comments')->cascadeOnDelete();

            $table->foreignId('admin_id')->nullable()->after('parent_id')
                ->constrained('admins')->nullOnDelete();

            $table->index(['blog_post_id', 'parent_id']);
        });
    }

    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->dropIndex(['blog_post_id', 'parent_id']);
            $table->dropColumn(['parent_id', 'admin_id']);
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete()->change();
        });
    }
};
