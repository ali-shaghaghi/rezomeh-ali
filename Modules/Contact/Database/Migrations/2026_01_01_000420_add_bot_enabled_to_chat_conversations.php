<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ربات قابل غیرفعال‌سازی per-conversation (#باگ چت).
 * وقتی ادمین bot_enabled را خاموش می‌کند، پاسخ خودکار ربات
 * ارسال نمی‌شود و ادمین شخصاً پاسخ می‌دهد.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('chat_conversations', 'bot_enabled')) {
            Schema::table('chat_conversations', function (Blueprint $table) {
                $table->boolean('bot_enabled')->default(true)->after('status');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('chat_conversations', 'bot_enabled')) {
            Schema::table('chat_conversations', function (Blueprint $table) {
                $table->dropColumn('bot_enabled');
            });
        }
    }
};
