<?php
/**
 * اسکریپت نصب — پروژه Laravel برای هاست cPanel
 * فقط از خط فرمان (Terminal/SSH) اجرا شود:
 *
 *     php install.php
 *
 * اختیاری — اجرای مهاجرت‌ها (اگر database.sql را ایمپورت نکرده‌اید):
 *     php install.php --migrate
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit("This file can only be run from the command line.\n");
}

$root = __DIR__;
chdir($root);

$ok = true;
function say($flag, $msg)
{
    global $ok;
    if ($flag === 'FAIL') {
        $ok = false;
    }
    echo sprintf("[%s] %s\n", $flag, $msg);
}

echo "=== نصب پروژه Laravel ===\n";
echo "مسیر: $root\nPHP: " . PHP_VERSION . "\n\n";

// 1) وجود artisan
is_file($root . '/artisan') ? say(' OK ', 'artisan پیدا شد') : say('FAIL', 'artisan پیدا نشد — داخل پوشه پروژه اجرا کنید');

// 2) وجود .env
if (!is_file($root . '/.env')) {
    say('FAIL', 'فایل .env وجود ندارد — ابتدا آن را بسازید و تنظیم کنید');
} else {
    $env = file_get_contents($root . '/.env');

    str_contains(preg_replace('/\s+/', '', $env), 'YOUR_DB_NAME')
        ? say('FAIL', 'در .env هنوز YOUR_DB_NAME هست — اطلاعات دیتابیس را ویرایش کنید')
        : say(' OK ', 'اطلاعات دیتابیس در .env تنظیم شده');

    preg_match('/^APP_ENV=production$/m', $env)
        ? say(' OK ', 'APP_ENV=production')
        : say('FAIL', 'APP_ENV باید production باشد');

    preg_match('/^APP_DEBUG=false$/m', $env)
        ? say(' OK ', 'APP_DEBUG=false')
        : say('FAIL', 'APP_DEBUG باید false باشد');

    preg_match('/^APP_KEY=base64:.+$/m', $env)
        ? say(' OK ', 'APP_KEY تنظیم شده')
        : say('FAIL', 'APP_KEY خالی است — php artisan key:generate --force');

    str_contains($env, 'YOUR-DOMAIN.COM')
        ? say('WARN', 'APP_URL هنوز YOUR-DOMAIN.COM است — دامنه واقعی را در .env بگذارید و دوباره اجرا کنید')
        : say(' OK ', 'APP_URL تنظیم شده');
}

// 3) vendor و build
is_file($root . '/vendor/autoload.php') ? say(' OK ', 'vendor سالم است') : say('FAIL', 'vendor ناقص است');
is_file($root . '/public/build/manifest.json') ? say(' OK ', 'فایل‌های بیلد فرانت موجودند') : say('WARN', 'public/build نیست — باید جداگانه آپلود شود');

// 4) دسترسی‌نویسی storage و bootstrap/cache
foreach (['storage', 'storage/app', 'storage/app/private', 'storage/app/public', 'storage/framework', 'storage/framework/cache', 'storage/framework/sessions', 'storage/framework/views', 'storage/logs', 'bootstrap/cache'] as $dir) {
    if (!is_dir($root . '/' . $dir)) {
        @mkdir($root . '/' . $dir, 0755, true);
    }
    if (!is_writable($root . '/' . $dir)) {
        @chmod($root . '/' . $dir, 0755);
    }
}
is_writable($root . '/storage') ? say(' OK ', 'storage قابل نوشتن است') : say('FAIL', 'storage فقط‌خواندنی است — دسترسی را 755/775 کنید');
is_writable($root . '/bootstrap/cache') ? say(' OK ', 'bootstrap/cache قابل نوشتن است') : say('FAIL', 'bootstrap/cache فقط‌خواندنی است');

if (!$ok) {
    echo "\n=== خطای بالا را اصلاح کنید و دوباره اجرا کنید ===\n";
    exit(1);
}

// 5) دستورات نهایی
echo "\n--- اجرای دستورات ---\n";

$php = PHP_BINARY ?: 'php';

echo "\n> storage:link\n";
passthru(escapeshellarg($php) . ' artisan storage:link 2>&1', $rc);
if ($rc !== 0) {
    echo "(اگر قبلاً ساخته شده باشد خطای harmless می‌دهد)\n";
}

if (in_array('--migrate', $argv ?? [], true)) {
    echo "\n> migrate --force\n";
    passthru(escapeshellarg($php) . ' artisan migrate --force 2>&1', $rc);
    if ($rc !== 0) {
        say('FAIL', 'مهاجرت ناموفق بود');
        exit(1);
    }
}

echo "\n> optimize (کش config/route/view)\n";
passthru(escapeshellarg($php) . ' artisan optimize 2>&1', $rc);
if ($rc !== 0) {
    say('FAIL', 'ساخت کش ناموفق — معمولاً یعنی .env یا دیتابیس اشتباه است');
    exit(1);
}

echo "\n=== نصب کامل شد ===\n";
echo "قدم بعدی:\n";
echo "1) دامنه را باز کنید و پنل ادمین را چک کنید\n";
echo "2) فایل‌های database.sql و install.php را حذف کنید\n";
echo "3) کرون‌های راهنما (INSTALL-FA.txt بخش ۸) را ثبت کنید\n";
exit(0);
