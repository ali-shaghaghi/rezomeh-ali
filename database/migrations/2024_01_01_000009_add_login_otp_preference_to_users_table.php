<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // کد ورود یک‌بارمصرف (OTP ایمیل) — اختیاری (پیش‌فرض: غیرفعال)
            $table->boolean('login_otp_enabled')->default(false)->after('email_verification_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('login_otp_enabled');
        });
    }
};
