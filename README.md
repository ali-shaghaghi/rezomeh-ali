<p align="center"><img src="public/img/logo.png" width="120" alt="Logo"></p>

<h1 align="center">رزومه علی شقاغی — Rezomeh Ali</h1>

<p align="center">
  سایت شخصی، فروشگاه محصولات دیجیتال، وبلاگ و سیستم رزرو مشاوره<br>
  ساخته‌شده با Laravel 13 + Livewire 3 (معماری ماژولار)
</p>

<p align="center">
  <a href="https://alishaghaghi.ir">🌐 سایت زنده</a> •
  <a href="https://github.com/ali-shaghaghi/rezomeh-ali">📦 مخزن گیت‌هاب</a>
</p>

---

## امکانات و ماژول‌ها

پروژه به‌صورت ماژولار در پوشهٔ `Modules/` سازمان‌دهی شده است:

| ماژول | توضیح |
|---|---|
| `Public` | صفحات عمومی: صفحه اصلی، وبلاگ، نمونه‌کار، تماس |
| `Resume` | رزومه، مهارت‌ها، نمونه‌کارها و پروژه‌ها |
| `Products` | فروشگاه محصولات دیجیتال + چکاوت و پرداخت |
| `Orders` | سفارش‌ها، پرداخت و اتصال به درگاه |
| `Blog` | مقالات، دسته‌بندی، برچسب و نظرات |
| `Consultation` | رزرو مشاوره (ویزارد چندمرحله‌ای + پرداخت) |
| `UserPanel` | پنل کاربر (سفارش‌ها، تیکت‌ها، مشاوره‌ها) |
| `AdminPanel` | پنل ادمین: داشبورد، مدیریت محتوا و تنظیمات |
| `Auth` | ورود کاربر/ادمین با OTP پیامکی و ۲ عاملی |
| `Users` | مدیریت کاربران، نقش‌ها و دسترسی‌ها |
| `Tickets` | سیستم تیکت پشتیبانی |
| `SEO` | متاتگ‌ها، Canonical، Sitemap و داده‌های ساختاریافته |
| `Settings` | تنظیمات سایت از پنل ادمین |
| `SMS` / `Notifications` / `AI` / `Gallery` / `Analytics` / `Contact` / `ProjectDev` | پیامک، اعلان‌ها، انتشار تلگرام، رسانه، آمار، تماس و ابزارهای پروژه |
| `Core` | سرویس‌های مشترک، Middleware و هِلپرها |

**خلاصه:** رزومه + فروشگاه + وبلاگ + رزرو مشاوره + پنل کاربر و ادمین، با ورود دومرحله‌ای پیامکی و SEO کامل.

---

## پیش‌نیازها

| مورد | نسخه |
|---|---|
| PHP | **8.4** (حداقل 8.3؛ نسخهٔ تولید روی هاست: 8.4) |
| افزونه‌های PHP | `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`, `curl`, `zip`, `gd` |
| دیتابیس | MySQL 8 / MariaDB 10.4+ |
| Composer | 2.x |
| Node.js | 18+ (فقط برای ساخت CSS/JS) |
| هاست | لینوکس/cPanel با پشتیبانی از `symlink` و `.htaccess` |

---

## راه‌اندازی محلی (توسعه)

```bash
git clone https://github.com/ali-shaghaghi/rezomeh-ali.git
cd rezomeh-ali

composer setup          # معادل: install + .env + key:generate + migrate + npm build
php artisan serve       # http://localhost:8000
```

اگر ترجیح می‌دهید مرحله‌به‌مرحله:

```bash
composer install
cp .env.example .env
php artisan key:generate

# دیتابیس را در .env تنظیم کنید (پیش‌فرض: sqlite)
php artisan migrate --seed

npm install
npm run build           # یا npm run dev برای توسعه

php artisan storage:link
php artisan serve
```

اجرای تست‌ها:

```bash
composer test           # معادل: php artisan test
```

---

## نصب روی هاست واقعی (cPanel) — مرحله به مرحله

> این مراحل روی هاست cPanel (پارس‌پک و سایرها) تست شده‌اند. نام کاربری هاست شما در ادامه با `USER` نمایش داده می‌شود (مثلاً `h439152`).

### ۱) ساخت دیتابیس

1. از پنل هاست وارد **MySQL® Databases** شوید.
2. یک دیتابیس بسازید، مثلاً `USER_rezomeh`.
3. یک کاربر دیتابیس بسازید (نام/رمز قوی).
4. کاربر را با گزینهٔ **Add User To Database → ALL PRIVILEGES** به دیتابیس اختصاص دهید.

### ۲) آپلود و اکسترکت فایل‌ها

پک استقرار (ساخته‌شده از همین مخزن) را از **File Manager** آپلود و Extract کنید. دو روش برای چیدمان هست:

**روش الف — لینک نمادین (پیشنهادی، استاندارد):**

```
/home/USER/rezomeh/          ← کل پروژه (app, Modules, vendor, ...)
/home/USER/public_html       → لینک به /home/USER/rezomeh/public
```

از ترمینال هاست:

```bash
rm -rf public_html
ln -s /home/USER/rezomeh/public /home/USER/public_html
```

**روش ب — روش سایت فعلی (بدون لینک نمادین):**

1. پروژه را در `/home/USER/rezomeh` اکسترکت کنید.
2. محتوای پوشهٔ `rezomeh/public` را در `public_html` کپی کنید.
3. در `public_html/index.php` دو خط زیر را مطابق شکل تغییر دهید:

```php
require __DIR__.'/../rezomeh/vendor/autoload.php';
$app = require_once __DIR__.'/../rezomeh/bootstrap/app.php';
```

### ۳) تنظیم فایل `.env`

فایل `.env` را در ریشهٔ پروژه ویرایش کنید (مهم‌ترین مقادیر):

```env
APP_NAME=AliShaghaghi
APP_ENV=production
APP_DEBUG=false                 # در پروداکشن هرگز true نکنید
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=USER_rezomeh
DB_USERNAME=USER_rezomeh
DB_PASSWORD=<رمز-دیتابیس>

SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true      # فقط با HTTPS فعال باشد
```

> فایل `.env` را هرگز در پوشهٔ `public` قرار ندهید و به‌اشتراک نگذارید؛ حاوی رمز دیتابیس است.

### ۴) دسترسی پوشه‌ها

از ترمینال هاست:

```bash
cd /home/USER/rezomeh
chmod -R 755 storage bootstrap/cache
chmod -R 775 storage/framework storage/logs
```

### ۵) نسخهٔ PHP را روی 8.4 بگذارید

از **MultiPHP Manager** نسخهٔ PHP دامنه را **PHP 8.4** کنید.

> اگر نسخه پایین‌تر باشد، سایت خطای زیر را می‌دهد (معمولاً 500):
> `Composer detected issues in your platform: Your Composer dependencies require a PHP version ">= 8.4.1"`

### ۶) ایمپورت دیتابیس

1. از **phpMyAdmin** دیتابیس ساخته‌شده را باز کنید.
2. تب **Import** → فایل `database.sql` (داخل پک یا `backup/database.sql`) را انتخاب کنید.
3. Encoding را روی `utf8mb4_general_ci` بگذارید و **Go** بزنید.

اگر حجم فایل بزرگ بود، از **MySQL Shell** یا فشرده‌سازی `gzip` استفاده کنید.

### ۷) کرون‌ها (Cron Jobs)

از بخش **Cron Jobs** دو کرون با فرکانس هر دقیقه ثبت کنید (مسیر PHP را با `which php` پیدا کنید؛ روی هاست‌های cPanel معمولاً `/opt/alt/php84/usr/bin/php` است):

```cron
* * * * * cd /home/USER/rezomeh && /opt/alt/php84/usr/bin/php artisan schedule:run >> /dev/null 2>&1
* * * * * cd /home/USER/rezomeh && /opt/alt/php84/usr/bin/php artisan queue:work --stop-when-empty >> /dev/null 2>&1
```

> `schedule:run` وظایف زمان‌بندی‌شده و `queue:work` اجرای جاب‌های دیتابیس (پیامک/اعلان/ایمیل) را بر عهده دارد.

### ۸) فعال‌سازی SSL

1. از **SSL/TSL Status** گزینهٔ **Run AutoSSL** را بزنید و صبر کنید تا گواهی صادر شود.
2. در **MultiPHP Manager** گزینهٔ **Force HTTPS Redirect** را روشن کنید.
3. مطمئن شوید `APP_URL` در `.env` با `https://` است و بعد کش را پاک کنید:

```bash
cd /home/USER/rezomeh
/opt/alt/php84/usr/bin/php artisan config:cache && /opt/alt/php84/usr/bin/php artisan route:cache && /opt/alt/php84/usr/bin/php artisan view:cache
```

### ۹) پاک‌سازی و تست نهایی

- پس از اطمینان از سلامت سایت، فایل‌های نصبی/موقت را از ریشهٔ وب حذف کنید (`install.php` و…).
- `/storage` و `/bootstrap/cache` نباید از طریق مرورگر قابل باز شدن باشند.
- تست کنید: صفحه اصلی، یک محصول، یک مقاله، ثبت‌نام کاربر، پنل ادمین.

---

## عیب‌یابی — خطاهای رایج

| خطا | علت | راه‌حل |
|---|---|---|
| **500 +** `platform_check.php ... require PHP >= 8.4.1` | نسخهٔ PHP هاست پایین‌تر است | MultiPHP Manager → PHP 8.4 |
| **500 عمومی** | خطای PHP یا دسترسی | `storage/logs/laravel.log` و `~/logs/php.error.log` را ببینید؛ `chmod` پوشهٔ storage |
| **404 روی همهٔ صفحات** | `.htaccess` خوانده نمی‌شود | `AllowOverride All` در تنظیمات دامنه + وجود `.htaccess` در `public` |
| **تصاویر 404** | لینک سیمبولیک storage ساخته نشده | `php artisan storage:link` |
| **صفحهٔ ورود ادمین 404** | مسیر ورود ادمین توکن‌دار است | `GET /admin/login/{token}` — توکن در جدول `settings` کلید `admin_login_token` یا ستون `admins.login_route_token` |
| **کرون اجرا نمی‌شود** | مسیر PHP اشتباه است | در ترمینال هاست `which php` و `php artisan about` را تست کنید |
| **تغییرات `.env` اعمال نشد** | کش تنظیمات | `php artisan config:clear` سپس `config:cache` |

---

## نکات امنیتی

- فایل `.env` و دامپ دیتابیس (`database.sql`) در `.gitignore` هستند و هرگز نباید وارد گیت یا پوشهٔ `public` شوند.
- `APP_DEBUG=false` و `SESSION_SECURE_COOKIE=true` در پروداکشن الزامی است.
- ورود ادمین با مسیر هش‌شدهٔ اختصاصی محافظت می‌شود؛ بدون توکن معتبر، صفحهٔ ورود 404 می‌دهد (ورود OTP پیامکی + دومرحله‌ای).
- پشتیبان‌گیری دوره‌ای: پوشهٔ `backup/` (نادیده گرفته‌شده در گیت) + اسکریپت `tools/server_backup.php` برای ساخت پک کامل از سرور (فایل‌ها + `database.sql`).
- پس از هر استقرار، نسخهٔ قبلی را نگه دارید و دیتابیس را جداگانه Export کنید.

---

## توسعه و مشارکت

```bash
composer test      # تست‌ها
./vendor/bin/pint  # قالب‌بندی کد PHP
npm run build      # ساخت نهایی CSS/JS
```

ساختار کلی پروژه:

```
├── Modules/              # ماژول‌های مستقل (هر ماژول: Models, Http, Livewire, Resources, Routes)
├── app/                  # هستهٔ اپلیکیشن (User, Admin, Middleware)
├── resources/            # قالب‌ها، CSS و JS مشترک
├── public/               # ریشهٔ وب (index.php, .htaccess, assets)
├── database/             # مایگریشن‌ها وSeederها
├── tests/                # تست‌های Feature ماژول‌ها
├── tools/                # اسکریپت‌های کمکی (پشتیبان‌گیری و ...)
└── backup/               # پک بکاپ سرور (نادیده گرفته می‌شود)
```

---

<p align="center">ساخته‌شده با Laravel · Livewire · Tailwind CSS</p>
