<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // بازنگری سفارشات برای مدیریت ددلاین و برنامه کاری (تسک‌محور)
    public function up(): void
    {
        // بازه شروع کار روی پروژه (مثلاً ۱ فروردین)
        Schema::table('orders', function (Blueprint $table) {
            $table->date('start_date')->nullable()->after('priority');
        });

        // اگر ستون قدیمی timestamp است → تبدیل به date با حفظ داده‌ها
        if (Schema::hasColumn('orders', 'deadline')) {
            // تشخیص نوع ستون به‌صورت cross-driver (sqlite در تست‌ها INFORMATION_SCHEMA ندارد)
            $isDateTime = false;
            if (DB::connection()->getDriverName() === 'mysql') {
                $deadlineType = collect(DB::select("SELECT DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'deadline'"))
                    ->first()?->DATA_TYPE;
                $isDateTime = in_array($deadlineType, ['timestamp', 'datetime']);
            } else {
                // sqlite: نوع از pragma خوانده می‌شود
                $col = collect(DB::select("PRAGMA table_info('orders')"))
                    ->first(fn ($c) => $c->name === 'deadline');
                $type = strtolower((string) ($col->type ?? ''));
                $isDateTime = str_contains($type, 'timestamp') || str_contains($type, 'datetime');
            }

            if ($isDateTime) {
                Schema::table('orders', function (Blueprint $table) {
                    $table->date('deadline_tmp')->nullable()->after('deadline');
                });

                DB::table('orders')->whereNotNull('deadline')->update([
                    'deadline_tmp' => DB::raw('DATE(deadline)'),
                ]);

                Schema::table('orders', function (Blueprint $table) {
                    $table->dropColumn('deadline');
                });

                Schema::table('orders', function (Blueprint $table) {
                    $table->date('deadline')->nullable()->after('priority');
                });

                DB::table('orders')->whereNotNull('deadline_tmp')->update([
                    'deadline' => DB::raw('deadline_tmp'),
                ]);

                Schema::table('orders', function (Blueprint $table) {
                    $table->dropColumn('deadline_tmp');
                });
            }
        }
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['start_date']);
        });
    }
};
