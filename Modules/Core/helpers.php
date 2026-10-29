<?php

if (!function_exists('module_path')) {
    /**
     * Get the path to a module.
     */
    function module_path(string $moduleName, string $path = ''): string
    {
        return base_path('Modules/' . $moduleName) . ($path ? DIRECTORY_SEPARATOR . $path : '');
    }
}

if (!function_exists('module_config_path')) {
    /**
     * Get the config path for a module.
     */
    function module_config_path(string $moduleName, string $fileName = ''): string
    {
        return module_path($moduleName, 'config' . DIRECTORY_SEPARATOR . $fileName);
    }
}

if (!function_exists('module_view_path')) {
    /**
     * Get the view path for a module.
     */
    function module_view_path(string $moduleName, string $path = ''): string
    {
        // لینوکس حساس به بزرگی حروف است و پوشه‌ها بین ماژول‌ها یکسان نیستند
        // (Resources/Views، Resources/views، …) — پس مسیر را پویا پیدا می‌کنیم.
        $base = module_path($moduleName);
        $viewsDir = null;

        if (is_dir($base)) {
            foreach (scandir($base) ?: [] as $entry) {
                if (strcasecmp($entry, 'resources') === 0 && is_dir($base.DIRECTORY_SEPARATOR.$entry)) {
                    $resourcesPath = $base.DIRECTORY_SEPARATOR.$entry;
                    foreach (scandir($resourcesPath) ?: [] as $sub) {
                        if (strcasecmp($sub, 'views') === 0 && is_dir($resourcesPath.DIRECTORY_SEPARATOR.$sub)) {
                            $viewsDir = $entry.DIRECTORY_SEPARATOR.$sub;
                            break 2;
                        }
                    }
                }
            }
        }

        $viewsDir = $viewsDir ?? ('resources'.DIRECTORY_SEPARATOR.'views');

        return $base . DIRECTORY_SEPARATOR . $viewsDir . ($path ? DIRECTORY_SEPARATOR . $path : '');
    }
}

if (!function_exists('module_lang_path')) {
    /**
     * Get the lang path for a module.
     */
    function module_lang_path(string $moduleName, string $path = ''): string
    {
        return module_path($moduleName, 'resources/lang' . ($path ? DIRECTORY_SEPARATOR . $path : ''));
    }
}

if (!function_exists('module_route_path')) {
    /**
     * Get the routes path for a module.
     */
    function module_route_path(string $moduleName, string $fileName = ''): string
    {
        return module_path($moduleName, 'routes' . DIRECTORY_SEPARATOR . $fileName);
    }
}

if (!function_exists('admin_logo_url')) {
    /**
     * Get the admin panel logo URL.
     * Checks for custom uploaded logo first, falls back to default.
     */
    function admin_logo_url(): string
    {
        $settingsPath = storage_path('app/settings.json');
        if (file_exists($settingsPath)) {
            $settings = json_decode(file_get_contents($settingsPath), true);
            if (!empty($settings['logo_path']) && \Illuminate\Support\Facades\Storage::disk('public')->exists($settings['logo_path'])) {
                $url = asset('storage/' . $settings['logo_path']);
                $fullPath = storage_path('app/public/' . $settings['logo_path']);
                if (file_exists($fullPath)) {
                    $url .= '?v=' . filemtime($fullPath);
                }
                return $url;
            }
        }
        return asset('img/logo.png');
    }
}

if (!function_exists('site_favicon_url')) {
    /**
     * آیکن نوار آدرس (favicon) سایت — از لوگوی آپلودی پنل ادمین.
     * اگر لوگویی آپلود نشده باشد، به favicon.svg پیش‌فرض برمی‌گردد.
     * ponytail: مرورگرها favicon را بر اساس پسوند کش می‌کنند؛
     * cache-buster ?v=filemtime اضافه می‌شود تا پس از آپلود لوگوی جدید، فوراً به‌روز شود.
     */
    function site_favicon_url(): string
    {
        $settingsPath = storage_path('app/settings.json');
        if (file_exists($settingsPath)) {
            $settings = json_decode(file_get_contents($settingsPath), true);
            if (!empty($settings['logo_path']) && \Illuminate\Support\Facades\Storage::disk('public')->exists($settings['logo_path'])) {
                $fullPath = storage_path('app/public/' . $settings['logo_path']);
                if (file_exists($fullPath)) {
                    return asset('storage/' . $settings['logo_path']) . '?v=' . filemtime($fullPath);
                }
            }
        }
        return asset('img/favicon.svg');
    }
}

if (!function_exists('user_avatar_url')) {
    /**
     * Get the user's avatar URL.
     * Falls back to default avatar if none uploaded.
     */
    function user_avatar_url(?object $user = null): string
    {
        $user = $user ?? auth()->user();
        if ($user && !empty($user->avatar) && \Illuminate\Support\Facades\Storage::disk('public')->exists('avatars/' . $user->avatar)) {
            $path = 'avatars/' . $user->avatar;
            $url = asset('storage/' . $path);
            $fullPath = storage_path('app/public/' . $path);
            if (file_exists($fullPath)) {
                $url .= '?v=' . filemtime($fullPath);
            }
            return $url;
        }
        return asset('img/avatar.svg');
    }
}
if (!function_exists('dev_mode_enabled')) {
    /**
     * آیا «حالت توسعه» فعال است؟ (تنظیمات → عمومی سایت → حالت توسعه)
     *
     * فقط در محیط local و وقتی گزینه «حالت توسعه» روشن باشد true برمی‌گردد:
     *   - روشن: پیامک‌ها فقط با درایور لاگ ثبت می‌شوند (بدون تماس واقعی با کاوه‌نگار)
     *     و کد OTP در صفحات تایید نمایش داده می‌شود.
     *   - خاموش: ارسال واقعی با درایور انتخاب‌شده (کاوه‌نگار و...) انجام می‌شود
     *     و کد OTP دیگر نمایش داده نمی‌شود — برای تست اتصال واقعی.
     *
     * در محیط‌های غیر از local همیشه false است (رفتار پروداکشن بدون تغییر).
     */
    function dev_mode_enabled(): bool
    {
        if (!app()->environment('local')) {
            return false;
        }

        return \Modules\Settings\Models\Setting::getBool('dev_mode', true);
    }
}

if (!function_exists('fa_digits')) {
    /**
     * تبدیل ارقام انگلیسی به فارسی (برای نمایش فرانت‌اند عمومی)
     */
    function fa_digits(int|string|null $number): string
    {
        return strtr((string) $number, [
            '0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴',
            '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹',
        ]);
    }
}

if (!function_exists('jdate')) {
    /**
     * تاریخ شمسی (جلالی) بدون وابستگی خارجی — الگوریتم استاندارد Jalali.
     *
     * مثال‌ها:
     *   jdate()                    → ۱۴۰۵/۰۷/۱۰
     *   jdate('l d F Y')           → جمعه ۱۰ مهر ۱۴۰۵
     *   jdate('Y/m/d H:i')         → ۱۴۰۵/۰۷/۱۰ ۲۰:۱۵ (ساعت از زمان محلی)
     *
     * توکن‌های پشتیبانی‌شده: Y/n/m/j/d (تاریخ جلالی)، F (نام ماه)، l (نام روز هفته)؛
     * هر کاراکتر دیگر (H, i, s, /, :, فاصله و...) مستقیم از تاریخ میلادی format می‌شود.
     *
     * @param  string  $format
     * @param  \Carbon\Carbon|\DateTimeInterface|null  $date  تاریخ ورودی (null = اکنون)
     * @param  bool  $latinDigits  اگر true باشد ارقام انگلیسی می‌مانند
     */
    function jdate(string $format = 'Y/m/d', $date = null, bool $latinDigits = false): string
    {
        $dt = $date ?? now();

        $gy = (int) $dt->format('Y');
        $gm = (int) $dt->format('n');
        $gd = (int) $dt->format('j');

        // تبدیل میلادی → جلالی (الگوریتم رسمی Jalali)
        $gDaysInMonth = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];

        $gy2 = ($gm > 2) ? ($gy + 1) : $gy;
        $days = 355666 + (365 * $gy) + intdiv($gy2 + 3, 4) - intdiv($gy2 + 99, 100)
            + intdiv($gy2 + 399, 400) + $gd + $gDaysInMonth[$gm - 1];

        $jy = -1595 + (33 * intdiv($days, 12053));
        $days %= 12053;

        $jy += 4 * intdiv($days, 1461);
        $days %= 1461;

        if ($days > 365) {
            $jy += intdiv($days - 1, 365);
            $days = ($days - 1) % 365;
        }

        if ($days < 186) {
            $jm = 1 + intdiv($days, 31);
            $jd = 1 + ($days % 31);
        } else {
            $jm = 7 + intdiv($days - 186, 30);
            $jd = 1 + (($days - 186) % 30);
        }

        $months = [1 => 'فروردین', 2 => 'اردیبهشت', 3 => 'خرداد', 4 => 'تیر',
            5 => 'مرداد', 6 => 'شهریور', 7 => 'مهر', 8 => 'آبان',
            9 => 'آذر', 10 => 'دی', 11 => 'بهمن', 12 => 'اسفند'];

        // روز هفته: format('w') → یکشنبه=0 ... جمعه=6
        $weekDays = ['یکشنبه', 'دوشنبه', 'سه‌شنبه', 'چهارشنبه', 'پنج‌شنبه', 'جمعه', 'شنبه'];
        $weekday = $weekDays[(int) $dt->format('w')];

        // فرمت به توکن‌ها شکسته می‌شود: توکن‌های جلالی جایگزین، بقیه از format میلادی
        preg_match_all('/(Y|n|m|j|d|F|l|.)/us', $format, $tokens);

        $out = '';
        foreach ($tokens[1] as $token) {
            $out .= match ($token) {
                'Y' => (string) $jy,
                'm' => sprintf('%02d', $jm),
                'n' => (string) $jm,
                'd' => sprintf('%02d', $jd),
                'j' => (string) $jd,
                'F' => $months[$jm],
                'l' => $weekday,
                // H, i, s, A, a و جداکننده‌ها مستقیم از خود تاریخ
                default => $dt->format($token),
            };
        }

        return $latinDigits ? $out : fa_digits($out);
    }
}


if (!function_exists('available_locales')) {
    /**
     * زبان‌های پشتیبانی‌شده توسط سایت عمومی.
     * کلید = کد زبان، مقدار = آرایه شامل پرچم، برچسب نمایشی و جهت متن.
     */
    function available_locales(): array
    {
        return [
            'fa' => [
                'code' => 'fa',
                'label' => 'فارسی',
                'short' => 'fa',
                'flag' => '🇮🇷',
                'dir' => 'rtl',
                'name' => 'Persian',
            ],
            'en' => [
                'code' => 'en',
                'label' => 'English',
                'short' => 'En',
                'flag' => '🇬🇧',
                'dir' => 'ltr',
                'name' => 'English',
            ],
        ];
    }
}

if (!function_exists('current_locale')) {
    /**
     * زبان فعال فعلی کاربر (از سشن/برنامه) — همیشه یکی از کلیدهای available_locales.
     */
    function current_locale(): string
    {
        $locale = app()->getLocale();
        return array_key_exists($locale, available_locales()) ? $locale : 'fa';
    }
}

if (!function_exists('is_rtl')) {
    /**
     * آیا زبان فعال راست‌چین است؟ (فارسی = RTL، انگلیسی = LTR)
     * در تگ <html dir="..."> استفاده می‌شود.
     */
    function is_rtl(): bool
    {
        return (available_locales()[current_locale()]['dir'] ?? 'rtl') === 'rtl';
    }
}

if (!function_exists('locale_direction')) {
    /**
     * جهت متن زبان فعال: 'rtl' یا 'ltr'.
     */
    function locale_direction(): string
    {
        return available_locales()[current_locale()]['dir'] ?? 'rtl';
    }
}

if (!function_exists('localized_field')) {
    /**
     * خواندن فیلد دو‌زبانه از یک مدل با fallback هوشمند.
     *
     * ابتدا فیلد انگلیسی (مثل name_en) را بررسی می‌کند؛ اگر خالی نباشد
     * و زبان فعلی انگلیسی باشد، آن را برمی‌گرداند. در غیر این صورت
     * مقدار فارسی را نمایش می‌دهد (هرگز null برنمی‌گرداند).
     *
     * @param  object|null $model       مدل دارای فیلدهای دو‌زبانه
     * @param  string      $field       نام فیلد پایه (مثل 'name')
     * @param  string|null $localeOverride
     */
    function localized_field(?object $model, string $field, ?string $localeOverride = null): ?string
    {
        if (!$model) {
            return null;
        }

        $locale = $localeOverride ?? current_locale();

        // زبان انگلیسی → اولویت با فیلد _en
        if ($locale === 'en') {
            $enValue = $model->{"{$field}_en"} ?? null;
            if (!blank($enValue)) {
                return $enValue;
            }
        }

        // fallback: مقدار اصلی (فارسی)
        $value = $model->{$field} ?? null;

        // اگر زبان انگلیسی است ولی نسخه انگلیسی وجود ندارد، باز هم
        // مقدار فارسی نمایش داده می‌شود (طبق درخواست کاربر)
        return blank($value) ? null : $value;
    }
}

if (!function_exists('format_price_for_locale')) {
    /**
     * قیمت‌گذاری دو‌زبانه: تومان برای فارسی، دلار برای انگلیسی (#درخواست کاربر).
     *
     * تنظیمات (پنل ادمین → تنظیمات):
     *   usd_rate      → قیمت روز هر دلار به تومان (مثلاً 250000)
     *   usd_markup    → ضریب گرانی برای کاربر خارج (مثلاً 1.3 = ۳۰٪ گران‌تر)
     *
     * محاسبه: تومان ÷ usd_rate × usd_markup = دلار (رُند به عدد صحیح)
     */
    function format_price_for_locale(float $toman, ?string $localeOverride = null): string
    {
        $locale = $localeOverride ?? current_locale();

        if ($locale !== 'en') {
            return fa_digits(number_format($toman)).' تومان';
        }

        $rate = (float) \Modules\Settings\Models\Setting::get('usd_rate', 0);
        $markup = (float) \Modules\Settings\Models\Setting::get('usd_markup', 1);

        // تنظیمات ناقص → همان تومان نمایش داده می‌شود (fallback امن)
        if ($rate <= 0) {
            return number_format($toman).' IRT';
        }

        $usd = (int) max(1, round($toman / $rate * max(1, $markup)));

        return '$'.number_format($usd).' USD';
    }

    /**
     * عدد قیمت دلاری برای کاربر انگلیسی (بدون واحد — برای displayهای خاص)
     */
    function usd_price(float $toman): ?float
    {
        if (current_locale() !== 'en') {
            return null;
        }

        $rate = (float) \Modules\Settings\Models\Setting::get('usd_rate', 0);
        $markup = (float) \Modules\Settings\Models\Setting::get('usd_markup', 1);

        if ($rate <= 0) {
            return null;
        }

        return round($toman / $rate * max(1, $markup), 2);
    }
}

if (!function_exists('has_en_version')) {
    /**
     * آیا این مدل نسخه انگلیسی برای این فیلد دارد؟
     * برای نمایش/مخفی کردن گزینه «انگلیسی» بالای بخش رزومه استفاده می‌شود.
     */
    function has_en_version(?object $model, string $field): bool
    {
        if (!$model) {
            return false;
        }

        return !blank($model->{"{$field}_en"} ?? null);
    }
}

if (!function_exists('localized_array')) {
    /**
     * خواندن فیلد آرایه‌ای دو‌زبانه (مثل typewriter_texts) با fallback.
     * برای فیلدهای JSON که در زبان فارسی و انگلیسی متفاوت‌اند.
     */
    function localized_array(?object $model, string $field, ?string $localeOverride = null): array
    {
        if (!$model) {
            return [];
        }

        $locale = $localeOverride ?? current_locale();

        if ($locale === 'en') {
            $enValue = $model->{"{$field}_en"} ?? null;
            if (!blank($enValue)) {
                return is_array($enValue) ? $enValue : [];
            }
        }

        $value = $model->{$field} ?? null;
        return is_array($value) ? $value : [];
    }
}

if (!function_exists('profile_social_links')) {
    /**
     * شبکه‌های اجتماعی پروفایل رزومه به‌همراه نام آیکون و برچسب.
     * فقط مواردی که مقدار دارند برمی‌گردند — برای نمایش در رزومه و پنل ادمین.
     *
     * @return array<int, array{key: string, icon: string, label: string, url: string}>
     */
    function profile_social_links(?object $profile): array
    {
        if (!$profile) {
            return [];
        }

        // key → [آیکون, برچسب فارسی]
        $map = [
            'github_url'    => ['github', 'گیت‌هاب'],
            'linkedin_url'  => ['linkedin', 'لینکدین'],
            'telegram_url'  => ['telegram', 'تلگرام'],
            'whatsapp_url'  => ['whatsapp', 'واتساپ'],
            'instagram_url' => ['instagram', 'اینستاگرام'],
            'rubika_url'    => ['rubika', 'روبیکا'],
            'bale_url'      => ['bale', 'بله'],
            'eita_url'      => ['eita', 'ایتا'],
        ];

        $links = [];
        foreach ($map as $field => [$icon, $label]) {
            $url = $profile->{$field} ?? null;
            if (!blank($url)) {
                $links[] = ['key' => $field, 'icon' => $icon, 'label' => $label, 'url' => $url];
            }
        }

        // تلفن همراه و ایمیل (با ساختار link مخصوص خودشان)
        $phone = $profile->phone ?? null;
        if (!blank($phone)) {
            $links[] = ['key' => 'phone', 'icon' => 'phone', 'label' => 'تلفن همراه', 'url' => 'tel:'.preg_replace('/\s+/', '', $phone)];
        }
        $email = $profile->email ?? null;
        if (!blank($email)) {
            $links[] = ['key' => 'email', 'icon' => 'mail', 'label' => 'ایمیل', 'url' => 'mailto:'.$email];
        }

        return $links;
    }
}

if (!function_exists('sanitize_rich_html')) {
    /**
     * پاک‌سازی HTML محتوای ویرایشگر غنی (توضیحات محصول / مقاله) هنگام ذخیره و رندر.
     * تگ‌های مجری، رویدادهای on* و URIهای خطرناک را حذف می‌کند؛ تگ‌های
     * فرمت‌دهی (p, figure, h1..h6, ul, a, img, …) دست‌نخورده می‌مانند.
     */
    function sanitize_rich_html(string $html): string
    {
        // تگ‌های اجرایی/فریم/فرم — هم باز هم بسته
        $html = preg_replace('#<\s*/?\s*(script|iframe|object|embed|form|style|link|meta|base)\b[^>]*>#is', '', $html);

        // رویدادهای inline مثل onclick ،onerror (با مقدار نقل‌قول‌دار یا بدون)
        $html = preg_replace('/\son\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html);

        // URIهای اسکریپتی در href/src/xlink:href (data:text/html و vbscript هم حذف)
        $html = preg_replace('/\s(href|src|xlink:href)\s*=\s*(["\']?\s*)(javascript|vbscript|data:text\/html)\s*:/i', ' $1=#', $html);

        return $html;
    }
}

if (!function_exists('render_inline_media')) {
    /**
     * رندر محتوای مقاله/توضیحات محصول با تصاویر درون‌متنی (#درخواست کاربر).
     *
     * دو نوع محتوا پشتیبانی می‌شود:
     *  ۱) HTML ویرایشگر غنی پنل ادمین (Quill) — شامل figureهای
     *     uc-media-* → پس از پاک‌سازی همان‌طور خروجی داده می‌شود.
     *  ۲) متن خام قدیمی با توکن ساده:
     *     [[media|full|storage/inline/abc.jpg]]   → تصویر عریض و کامل
     *     [[media|center|storage/inline/abc.jpg]] → وسط‌چین
     *     [[media|left|storage/inline/abc.jpg]]   → نصف صفحه، سمت چپ
     *     [[media|right|storage/inline/abc.jpg]]  → نصف صفحه، سمت راست
     *
     * فقط مسیرهای محلی storage/ پذیرفته می‌شوند — اسکریپت/دامنهٔ خارجی ممکن نیست
     * و توکن‌های نامعتبر کاملاً از خروجی حذف می‌شوند.
     */
    function render_inline_media(?string $text, bool $escape = true): string
    {
        if ($text === null || $text === '') {
            return '';
        }

        // محتوای HTML فقط وقتی خروجی خام می‌گیرد که تگ ساختاری شناخته‌شده داشته باشد؛
        // متن خامِ حاوی <script …> یا متن ساده همچنان escape می‌شود (دفاع در برابر XSS)
        $isHtml = $escape
            && preg_match('/<(p|figure|h[1-6]|ul|ol|li|blockquote|pre|code|strong|b|em|i|u|s|strike|a|img|br|hr|div|span|table|thead|tbody|tr|td|th|section|article)\b/i', $text) === 1;

        $out = $isHtml ? sanitize_rich_html($text) : nl2br(e($text));

        $rendered = preg_replace_callback(
            '#\[\[media\|(full|center|left|right)\|(storage/[A-Za-z0-9_\-./]+)\]\]#',
            static function (array $m): string {
                $src = asset($m[2]);

                return '<figure class="uc-media uc-media-'.$m[1].'">'
                    .'<img src="'.e($src).'" alt="" loading="lazy">'
                    .'</figure>';
            },
            $out
        );

        $rendered = $rendered ?? $out;

        // توکن‌های نامعتبر (مسیر خارجی، ساختار خراب) هرگز به خروجی راه نمی‌یابند
        $cleaned = preg_replace('#\s*\[\[media\|[^\]]*\]\]\s*#', ' ', $rendered);

        return $cleaned !== null && $cleaned !== '' ? $cleaned : $rendered;
    }
}

if (!function_exists('strip_inline_media')) {
    /**
     * حذف توکن‌های تصویر درون‌متنی از متن‌های پیش‌نمایش (لیست ادمین، خلاصه‌ها)
     * تا کاربر با متنِ `[[media|...]]` مواجه نشود.
     */
    function strip_inline_media(?string $text): string
    {
        $clean = preg_replace('#\s*\[\[media\|[^\]]+\]\]\s*#', ' ', (string) $text);

        return trim($clean ?? (string) $text);
    }
}

if (!function_exists('featured_image_url')) {
    /**
     * آدرس نمایش تصویر شاخص — هم مسیرهای قدیمی storage (blog/featured/…)
     * و هم مسیرهای گالری (gallery/{hash}) و آدرس‌های مطلق را پشتیبانی می‌کند.
     */
    function featured_image_url(?string $value, bool $preferWebp = true): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (preg_match('#^https?://#i', $value)) {
            return $value;
        }

        $clean = ltrim($value, '/');

        // بهینه‌سازی سرعت: اگر نسخه WebP کنار همان فایل وجود داشته باشد،
        // همان URL را WebP برمی‌گردانیم (مرورگرهای مدرن همه پشتیبانی می‌کنند).
        // وقتی فایل WebP موجود نباشد، رفتار قبلی (PNG/JPG) دست‌نخورده می‌ماند.
        // سئو/JSON-LD با $preferWebp = false تصویر اصلی را می‌گیرد.
        if ($preferWebp && preg_match('/\.(png|jpe?g|gif)$/i', $clean)) {
            $webpRel = preg_replace('/\.(png|jpe?g|gif)$/i', '.webp', $clean);
            $isGallery = str_starts_with($clean, 'gallery/');
            $webpAbs = $isGallery ? public_path($webpRel) : storage_path('app/public/' . $webpRel);
            if (is_file($webpAbs)) {
                return asset($isGallery ? $webpRel : 'storage/' . $webpRel);
            }
        }

        if (str_starts_with($clean, 'gallery/')) {
            return asset($clean);
        }

        return asset('storage/' . $value);
    }
}

