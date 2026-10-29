<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ستون «روت اختصاصی ورود» هر ادمین ( admins.login_route_token ).
     *
     * مسیر ورود ادمین هش‌شده است (/admin/login/{token}) و هر ادمین می‌تواند
     * توکن اختصاصی خودش را داشته باشد تا لینک ورودش قابل ارسال/بازتولید باشد.
     * (مدل Admin و سرویس AdminLoginToken به این ستون نگاه می‌کنند؛ قبلاً مهاجرت
     * نداشت و روی نصب تازه ستون ساخته نمی‌شد.)
     */
    public function up(): void
    {
        if (! Schema::hasColumn('admins', 'login_route_token')) {
            Schema::table('admins', function (Blueprint $table) {
                $table->string('login_route_token', 64)->nullable()->unique()->after('password');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('admins', 'login_route_token')) {
            Schema::table('admins', function (Blueprint $table) {
                $table->dropUnique(['login_route_token']);
                $table->dropColumn('login_route_token');
            });
        }
    }
};
