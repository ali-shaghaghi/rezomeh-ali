<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('resume_profiles', function (Blueprint $table) {
            // شبکه‌های اجتماعی ایرانی و بین‌المللی برای بخش رزومه
            $table->string('telegram_url')->nullable()->after('linkedin_url');
            $table->string('whatsapp_url')->nullable()->after('telegram_url');
            $table->string('instagram_url')->nullable()->after('whatsapp_url');
            $table->string('rubika_url')->nullable()->after('instagram_url');
            $table->string('bale_url')->nullable()->after('rubika_url');
            $table->string('eita_url')->nullable()->after('bale_url');
            $table->string('phone')->nullable()->after('eita_url');
        });
    }

    public function down(): void
    {
        Schema::table('resume_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'telegram_url', 'whatsapp_url', 'instagram_url',
                'rubika_url', 'bale_url', 'eita_url', 'phone',
            ]);
        });
    }
};
