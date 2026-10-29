<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * یکبار جابه‌جایی تاریخ‌های ذخیره‌شده از UTC به وقت ایران (Asia/Tehran = UTC+3:30).
 *
 * با تغییر app.timezone به Asia/Tehran، ردیف‌های قدیمی که با UTC ذخیره شده‌اند
 * باید ۲۱۰ دقیقه جلو بروند تا ساعت نمایش‌داده‌شده در لاگ‌ها درست باشد.
 * فقط ستون‌های datetime/timestamp جابه‌جا می‌شوند؛ ستون‌های تاریخ خالص (date)
 * و ساعت خالص (time) مثل تاریخ اسلات‌های مشاوره دست نمی‌خورند.
 */
return new class extends Migration
{
    private const MINUTES = 210; // 3 ساعت و 30 دقیقه

    public function up(): void
    {
        $this->shift(self::MINUTES);
    }

    public function down(): void
    {
        $this->shift(-self::MINUTES);
    }

    private function shift(int $minutes): void
    {
        $driver = DB::connection()->getDriverName();

        foreach (Schema::getTables() as $tableInfo) {
            $table = is_array($tableInfo) ? ($tableInfo['name'] ?? null) : $tableInfo;

            if (! is_string($table) || $table === '') {
                continue;
            }

            foreach (Schema::getColumns($table) as $column) {
                $type = strtolower((string) ($column['type_name'] ?? $column['type'] ?? ''));

                // فقط datetime/timestamp واقعی — نه date و نه time
                if (! str_starts_with($type, 'datetime') && ! str_starts_with($type, 'timestamp')) {
                    continue;
                }

                $name = (string) $column['name'];
                $sql = $this->compile($driver, $table, $name, $minutes);

                if ($sql !== null) {
                    DB::statement($sql);
                }
            }
        }
    }

    private function compile(string $driver, string $table, string $column, int $minutes): ?string
    {
        $abs = abs($minutes);
        $sign = $minutes < 0 ? '-' : '+';

        return match ($driver) {
            'mysql', 'mariadb' => sprintf(
                'UPDATE `%s` SET `%s` = DATE_ADD(`%s`, INTERVAL %d MINUTE) WHERE `%s` IS NOT NULL',
                $table, $column, $column, $minutes, $column
            ),
            'sqlite' => sprintf(
                "UPDATE \"%s\" SET \"%s\" = datetime(\"%s\", '%s%d minutes') WHERE \"%s\" IS NOT NULL",
                $table, $column, $column, $sign, $abs, $column
            ),
            'pgsql' => sprintf(
                "UPDATE \"%s\" SET \"%s\" = \"%s\" %s INTERVAL '%d minutes' WHERE \"%s\" IS NOT NULL",
                $table, $column, $column, $sign, $abs, $column
            ),
            'sqlsrv' => sprintf(
                'UPDATE [%s] SET [%s] = DATEADD(minute, %d, [%s]) WHERE [%s] IS NOT NULL',
                $table, $column, $minutes, $column, $column
            ),
            default => null,
        };
    }
};
