# رزومه — سایت شخصی، فروشگاه و وبلاگ

سایت کامل مبتنی بر **Laravel 13** و **Livewire 3** با معماری ماژولار (هر بخش یک ماژول مستقل در `Modules/`). شامل رزومه و نمونه‌کار، فروشگاه محصولات دیجیتال با درگاه پرداخت، وبلاگ، رزرو آنلاین مشاوره، سیستم تیکت، پنل کاربر و پنل ادمین با ورود دومرحله‌ای، به‌همراه سئوی کامل.

## امکانات

- رزومه، مهارت‌ها، رزومهٔ تحصیلی/شغلی و نمونه‌کارها
- فروشگاه محصولات دیجیتال + چکاوت چندمرحله‌ای + درگاه پرداخت
- وبلاگ با دسته‌بندی، برچسب و سیستم نظرات
- رزرو آنلاین مشاوره (ویزارد رزرو با پرداخت آنلاین)
- پنل کاربر: سفارش‌ها، تیکت‌ها، مشاوره‌ها و محصولات خریداری‌شده
- پنل ادمین: داشبورد، مدیریت محتوا، کاربران، تنظیمات و رسانه‌ها
- ورود ادمین با مسیر هش‌شده + کد پیامکی + تأیید دومرحله‌ای
- سئو: متاتگ داینامیک، Canonical، Sitemap پویا، robots.txt و دادهٔ ساختاریافته
- تیکت پشتیبانی، ارسال پیامک و اعلان‌ها

## پیش‌نیازها

- هاست لینوکس با **cPanel** (مثل پارس‌پک) — دامنهٔ متصل‌شده به هاست
- **PHP 8.4** با افزونه‌های: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`, `curl`, `zip`, `gd`
- **MySQL 8** یا MariaDB
- دسترسی به ترمینال هاست (برای Composer و artisan)

---

## مراحل نصب روی هاست واقعی (پارس‌پک)

> در ادامه `USER` نام کاربری هاست شماست (مثلاً `h439152`) و `domain.com` دامنهٔ شما.

### مرحله ۱ — ساخت دیتابیس

1. از Client Area وارد بخش هاست → **Manage** شوید.
2. از منوی cPanel وارد **MySQL® Databases** شوید.
3. یک دیتابیس بسازید (مثلاً `USER_rezomeh`).
4. یک کاربر دیتابیس بسازید (نام و رمز قوی یادداشت کنید).
5. کاربر را با **Add User To Database → ALL PRIVILEGES** به دیتابیس متصل کنید.

### مرحله ۲ — آپلود و اکسترکت فایل‌ها

1. وارد **File Manager** شوید و در ریشهٔ هاست (home) یک پوشه به نام `rezomeh` بسازید.
2. فایل ZIP پروژه را آپلود و **Extract** کنید تا محتوای آن در `/home/USER/rezomeh` قرار گیرد.

### مرحله ۳ — اتصال ریشهٔ وب به پوشهٔ `public`

**روش الف — لینک نمادین (پیشنهادی):** از ترمینال هاست:

```bash
rm -rf public_html
ln -s /home/USER/rezomeh/public /home/USER/public_html
```

**روش ب — کپی (اگر لینک نمادین کار نکرد):**

1. محتوای پوشهٔ `rezomeh/public` را در `public_html` کپی کنید.
2. در فایل `public_html/index.php` دو خط `require` را مطابق شکل تغییر دهید:

```php
require __DIR__.'/../rezomeh/vendor/autoload.php';
$app = require_once __DIR__.'/../rezomeh/bootstrap/app.php';
```

### مرحله ۴ — تنظیم فایل `.env`

فایل `.env` در ریشهٔ پروژه را ویرایش کنید:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=USER_rezomeh
DB_USERNAME=USER_rezomeh
DB_PASSWORD=<رمز-دیتابیس>

SESSION_SECURE_COOKIE=true
```

> فایل `.env` حاوی رمز دیتابیس است؛ هرگز آن را در پوشهٔ `public` قرار ندهید یا منتشر نکنید.

### مرحله ۵ — دسترسی پوشه‌ها

از ترمینال هاست:

```bash
cd /home/USER/rezomeh
chmod -R 755 storage bootstrap/cache
```

### مرحله ۶ — انتخاب نسخهٔ PHP 8.4

از cPanel وارد **MultiPHP Manager** شوید و نسخهٔ PHP دامنه را روی **PHP 8.4** بگذارید.
(با نسخهٔ پایین‌تر، سایت خطای 500 با پیام `Your Composer dependencies require a PHP version ">= 8.4.1"` می‌دهد.)

### مرحله ۷ — نصب وابستگی‌ها (Composer)

از **Terminal** هاست:

```bash
cd /home/USER/rezomeh
composer install
```

اگر Composer روی هاست نصب نبود:

```bash
cd /home/USER/rezomeh
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
php composer.phar install
rm composer-setup.php
```

### مرحله ۸ — ساخت جدول‌ها و دادهٔ اولیه

```bash
cd /home/USER/rezomeh
php artisan migrate --seed
```

`migrate` جدول‌ها را می‌سازد و `seed` نقش‌ها و دسترسی‌ها، حساب‌های ادمین، تنظیمات پایه و محتوای اولیهٔ سایت را قرار می‌دهد.

### مرحله ۹ — لینک رسانه‌ها و کش

```bash
cd /home/USER/rezomeh
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### مرحله ۱۰ — کرون‌ها (Cron Jobs)

از بخش **Cron Jobs** این دو خط را با فرکانس **هر دقیقه** ثبت کنید (مسیر PHP را می‌توانید با `which php` پیدا کنید؛ روی هاست‌های cPanel معمولاً `/opt/alt/php84/usr/bin/php` است):

```cron
* * * * * cd /home/USER/rezomeh && /opt/alt/php84/usr/bin/php artisan schedule:run >> /dev/null 2>&1
* * * * * cd /home/USER/rezomeh && /opt/alt/php84/usr/bin/php artisan queue:work --stop-when-empty >> /dev/null 2>&1
```

### مرحله ۱۱ — فعال‌سازی SSL

1. در cPanel وارد **SSL/TLS Status** شوید و **Run AutoSSL** را بزنید؛ صبر کنید تا گواهی صادر شود.
2. در **MultiPHP Manager** گزینهٔ **Force HTTPS Redirect** را روشن کنید.
3. مطمئن شوید `APP_URL` در `.env` با `https://` شروع می‌شود و سپس کش را دوباره بسازید:

```bash
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

### مرحله ۱۲ — ورود به پنل ادمین

مسیر ورود ادمین یک توکن هش‌شدهٔ اختصاصی دارد (بدون توکن، صفحهٔ ورود 404 می‌دهد). توکن را از ترمینال بگیرید:

```bash
cd /home/USER/rezomeh
php artisan tinker --execute="echo \Modules\Auth\Services\AdminLoginToken::global();"
```

سپس آدرس ورود شماست:

```
https://domain.com/admin/login/<توکن>
```

- کاربرهای ادمین نمونه با رمز `password` ساخته می‌شوند — **بلافاصله بعد از اولین ورود رمز را عوض کنید.**
- اگر هنوز پیامک را شارژ نکرده‌اید و کد OTP نمی‌آید، فایل کلید اضطراری ورود بدون پیامک را بسازید و بعد از فعال‌سازی پیامک حذفش کنید:

```bash
touch storage/app/otp_off    # ورود بدون کد پیامک/دومرحله
rm storage/app/otp_off       # بازگرداندن ورود دومرحله‌ای
```

### مرحله ۱۳ — بارگذاری رسانه‌ها

لوگو، تصاویر مقالات و رسانه‌های پروژه در مخزن قرار ندارند (دادهٔ هر سایت جداگانه است). بعد از نصب، از **پنل ادمین → گالری/تنظیمات** فایل‌های خود را آپلود کنید؛ مسیر `storage` از طریق لینک نمادین در دسترس است.

---

سایت بعد از مرحله ۱۲ آمادهٔ استفاده است: صفحهٔ اصلی، فروشگاه، وبلاگ، رزرو مشاوره و پنل کاربر همگی با همان دیتابیس کار می‌کنند.
