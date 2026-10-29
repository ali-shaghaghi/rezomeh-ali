<?php
/**
 * اسکریپت موقت پشتیبان‌گیری سرور — بعد از اجرا حذف می‌شود.
 * خروجی: /home/h439152/backup/rezomeh_full_YYYYMMDD.zip  (فایل‌ها + database.sql)
 * پیشرفت: /home/h439152/backup/progress.txt
 */
declare(strict_types=1);

$key = 'bk_9f27qa_temp';
if (($_GET['k'] ?? '') !== $key) {
    http_response_code(404);
    die('not found');
}

@ini_set('memory_limit', '1024M');
@set_time_limit(0);
@ini_set('max_execution_time', '0');
ignore_user_abort(true);

$home = '/home/h439152';
$app  = $home . '/rezomeh';
$web  = $home . '/public_html';
$bk   = $home . '/backup';
@mkdir($bk, 0755, true);

$prog  = $bk . '/progress.txt';
$lock  = $bk . '/.lock';
$stamp = date('Ymd_His');

function w(string $m): void
{
    global $prog;
    file_put_contents($prog, date('H:i:s') . '  ' . $m . "\n", FILE_APPEND);
}

if (file_exists($lock) && (time() - (int) filemtime($lock)) < 3000) {
    die('already-running');
}
touch($lock);

try {
    w('=== backup start ===');

    /* ---------- ۱) دامپ دیتابیس ---------- */
    // پارسر خط‌به‌خط (parse_ini_file با بعضی نویسه‌های .env خطا می‌دهد)
    $env = [];
    foreach ((array) @file($app . '/.env') as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || ! preg_match('/^([A-Za-z0-9_]+)\s*=\s*(.*)$/', $line, $m)) {
            continue;
        }
        $env[$m[1]] = trim($m[2], " \t\"'");
    }
    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $env['DB_HOST'] ?? 'localhost', $env['DB_DATABASE'] ?? '');
    $pdo = new PDO($dsn, $env['DB_USERNAME'] ?? '', $env['DB_PASSWORD'] ?? '', [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);

    $sqlFile = $bk . '/database.sql';
    $out     = fopen($sqlFile, 'wb');
    fwrite($out, "-- rezomeh backup " . date('c') . "\nSET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\n\n");
    $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    w('db: ' . count($tables) . ' tables');

    foreach ($tables as $t) {
        $c   = $pdo->query("SHOW CREATE TABLE `$t`")->fetch(PDO::FETCH_NUM);
        $sql = "DROP TABLE IF EXISTS `$t`;\n" . $c[1] . ";\n\n";
        $st  = $pdo->query("SELECT * FROM `$t`");
        $n   = 0;
        $buf = '';
        while ($r = $st->fetch(PDO::FETCH_NUM)) {
            $vals = [];
            foreach ($r as $x) {
                $vals[] = $x === null ? 'NULL' : $pdo->quote((string) $x);
            }
            $buf .= "INSERT INTO `$t` VALUES(" . implode(',', $vals) . ");\n";
            if (++$n % 300 === 0) {
                fwrite($out, $buf);
                $buf = '';
            }
        }
        fwrite($out, $sql . $buf . "\n");
        w("  table {$t}: {$n} rows");
    }
    fwrite($out, "SET FOREIGN_KEY_CHECKS=1;\n");
    fclose($out);
    w('db done: ' . round(filesize($sqlFile) / 1048576, 2) . ' MB');

    /* ---------- ۲) فایل‌ها در قالب ZIP ---------- */
    $zipName = $bk . '/rezomeh_full_' . date('Ymd') . '.zip';
    $zip     = new ZipArchive();
    if ($zip->open($zipName, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        throw new Exception('cannot create zip');
    }
    $zip->addFile($sqlFile, 'database.sql');

    // پوشه‌هایی که قابل بازسازی یا غیرلازم‌اند (با پسوند / یعنی حذف از عبور)
    $pruneDirs = ['.git', 'node_modules', '.idea', '.vscode', 'backup', 'cache', 'sessions', 'testing'];
    $pruneRel  = ['storage/framework', 'storage/logs'];

    $roots = [$app => 'rezomeh', $web => 'public_html'];
    $count = 0;

    foreach ($roots as $root => $prefix) {
        $dirIt = new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS);
        $filt  = new RecursiveCallbackFilterIterator(
            $dirIt,
            static function ($current) use ($root, $pruneDirs, $pruneRel) {
                if ($current->isLink()) {
                    return false; // لینک‌ها (مثل storage) رد می‌شوند — هدف واقعی جداگانه اضافه شده
                }
                $rel = str_replace('\\', '/', substr($current->getPathname(), strlen($root) + 1));
                if ($current->isDir()) {
                    $base = basename($rel);
                    if (in_array($base, $pruneDirs, true)) {
                        return false;
                    }
                    foreach ($pruneRel as $p) {
                        if (str_starts_with($rel, $p . '/') || $rel === $p) {
                            return false;
                        }
                    }
                }
                return true;
            }
        );

        $it = new RecursiveIteratorIterator($filt, RecursiveIteratorIterator::LEAVES_ONLY);
        foreach ($it as $f) {
            if (!$f->isFile()) {
                continue;
            }
            $p   = $f->getPathname();
            $rel = str_replace('\\', '/', substr($p, strlen($root) + 1));
            if (strtolower(substr($rel, -4)) === '.zip') {
                continue;
            }
            if (!$zip->addFile($p, $prefix . '/' . $rel)) {
                w('add-failed: ' . $prefix . '/' . $rel);
            }
            $count++;
            if ($count % 3000 === 0) {
                w("zip: {$count} files...");
            }
        }
    }

    w("zip: {$count} files added — closing (may take a minute)...");
    if (!$zip->close()) {
        throw new Exception('zip close failed');
    }
    clearstatcache();
    w('DONE file=' . basename($zipName) . ' size=' . round(filesize($zipName) / 1048576, 2) . 'MB');
} catch (Throwable $e) {
    w('ERROR: ' . $e->getMessage());
} finally {
    @unlink($lock);
}
