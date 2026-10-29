<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ذخیره دائمی چت آنلاین (#۲۱).
 *
 * پیام‌ها قبلاً فقط در session نگه‌داری می‌شدند، در نتیجه ادمین نمی‌توانست
 * گفتگوها را ببیند یا پاسخ بدهد. این جدول‌ها گفتگوها را دائمی می‌کنند
 * تا ادمین در پنل مدیریت آن‌ها را ببیند و یا خودش پاسخ بدهد.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('chat_conversations')) {
            Schema::create('chat_conversations', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                // guest: session_id — logged-in: user_id (هر دو ممکن است پر باشند)
                $table->string('session_id')->nullable()->index();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
                $table->string('guest_name')->nullable();
                $table->timestamp('last_activity_at')->nullable();
                // closed: کاربر گفتگو را بست / archived: ادمین بایگانی کرد
                $table->string('status')->default('open'); // open | closed | archived
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('chat_messages')) {
            Schema::create('chat_messages', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('conversation_id');
                $table->foreign('conversation_id')->references('id')->on('chat_conversations')->cascadeOnDelete();
                // user | bot | admin — bot و admin هردو به کاربر نمایش داده می‌شوند
                $table->string('from');
                $table->text('text');
                $table->unsignedBigInteger('admin_id')->nullable()->index();
                $table->foreign('admin_id')->references('id')->on('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['conversation_id', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
        Schema::dropIfExists('chat_conversations');
    }
};
