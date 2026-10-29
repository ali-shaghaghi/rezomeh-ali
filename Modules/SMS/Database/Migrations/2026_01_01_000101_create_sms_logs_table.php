<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // لاگ ارسال پیامک‌ها — هر تلاش ارسال یک ردیف
    public function up(): void
    {
        Schema::create('sms_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('phone', 20);
            $table->text('message');
            $text = $table->string('provider', 50);
            $text->comment('درایور استفاده‌شده در لحظه ارسال');
            $table->enum('status', ['sent', 'failed'])->default('sent');
            $table->text('response')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->decimal('cost', 10, 4)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_logs');
    }
};