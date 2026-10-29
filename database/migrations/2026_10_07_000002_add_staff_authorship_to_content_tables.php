<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * تفکیک مالکیت محتوا از جدول users:
 *
 *  - نویسنده مقاله و پاسخ‌دهنده تیکت می‌تواند کاربر (users) یا عضو تیم (admins) باشد؛
 *    ستون user_id برای نوشته‌های ادمین null می‌شود و admin_id شناسه ادمین را نگه می‌دارد.
 *  - chat_messages.admin_id قبلاً به users اشاره می‌کرد (بعد از جداسازی ادمین‌ها
 *    FOREIGN KEY می‌شکست) — حالا به admins اشاره می‌کند.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ===== وبلاگ: نویسنده می‌تواند کاربر یا عضو تیم باشد =====
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->change();
        });

        Schema::table('blog_posts', function (Blueprint $table) {
            $table->foreignId('admin_id')->nullable()->after('user_id')
                ->constrained('admins')->nullOnDelete();
        });

        // ===== تیکت: پاسخ کارشناس ممکن است از جدول admins بیاید =====
        Schema::table('ticket_replies', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->change();
        });

        Schema::table('ticket_replies', function (Blueprint $table) {
            $table->foreignId('admin_id')->nullable()->after('user_id')
                ->constrained('admins')->nullOnDelete();
        });

        // ===== چت: بازگرداندن FK به جدول admins =====
        // رکوردهای قدیمی که به کاربر حذف‌شده/نامعتبر اشاره می‌کنند اول null می‌شوند
        // تا افزودن قید جدید روی MySQL با داده‌های موجود شکست نخورد.
        DB::table('chat_messages')
            ->whereNotNull('admin_id')
            ->whereNotIn('admin_id', DB::table('admins')->select('id'))
            ->update(['admin_id' => null]);

        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropForeign(['admin_id']);
        });

        Schema::table('chat_messages', function (Blueprint $table) {
            $table->foreign('admin_id')->references('id')->on('admins')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropForeign(['admin_id']);
        });

        Schema::table('chat_messages', function (Blueprint $table) {
            $table->foreign('admin_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::table('ticket_replies', function (Blueprint $table) {
            $table->dropForeign(['admin_id']);
            $table->dropColumn('admin_id');
        });

        Schema::table('ticket_replies', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete()->change();
        });

        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropForeign(['admin_id']);
            $table->dropColumn('admin_id');
        });

        Schema::table('blog_posts', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete()->change();
        });
    }
};
