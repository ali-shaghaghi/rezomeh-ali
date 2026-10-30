<?php

namespace Modules\AI\Livewire\Admin;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Component;
use Modules\AI\Services\AiService;
use Modules\Core\Models\BlogPost;
use Modules\Products\Models\Product;
use Modules\Settings\Models\Setting;

/**
 * ابزار ۳ — اتصال تلگرام + انتشار خودکار/کمپین.
 *
 * تنظیم Bot Token و Chat ID از Settings؛ انتشار دستی مقاله/محصول
 * به کانال + تولید کپشن تبلیغاتی با AI.
 */
class TelegramPublisher extends Component
{
    // اتصال
    public string $botToken = '';

    public string $chatId = '';

    public bool $connected = false;

    public string $connectionMessage = '';

    // انتشار
    public string $contentType = 'latest_post'; // latest_post | latest_product | campaign

    public int $selectedId = 0;

    public string $caption = '';

    public string $errorMessage = '';

    public string $successMessage = '';

    public bool $sending = false;

    public function mount(): void
    {
        $this->botToken = (string) Setting::get('telegram_bot_token', '');
        // نرمال‌سازی مقدار ذخیره‌شده قدیمی (بدون ‎-100)
        $this->chatId = $this->normalizeChatId((string) Setting::get('telegram_chat_id', ''));
        $this->connected = $this->botToken !== '' && $this->chatId !== '';
    }

    /**
     * ذخیره تنظیمات اتصال تلگرام
     */
    public function saveConnection(): void
    {
        $this->validate([
            'botToken' => ['required', 'string', 'min:20'],
            'chatId' => ['required', 'string', 'max:100'],
        ]);

        // شناسه کانال/سوپرگروپ تلگرام باید منفی باشد (100437… → -100437…)
        $this->chatId = $this->normalizeChatId($this->chatId);

        Setting::set('telegram_bot_token', $this->botToken, 'contact');
        Setting::set('telegram_chat_id', $this->chatId, 'contact');

        $this->connected = true;
        $this->connectionMessage = 'اتصال تلگرام ذخیره شد. Chat ID: '.$this->chatId;
    }

    /**
     * نرمال‌سازی Chat ID — تلگرام برای کانال/سوپرگروپ پیشوند ‎-100 می‌خواهد؛
     * کاربران معمولاً شناسه مثبت (1004371249332) را بدون علامت منفی وارد می‌کنند
     * که نتیجه‌اش خطای Bad Request: chat not found است.
     */
    private function normalizeChatId(string $chatId): string
    {
        $chatId = trim($chatId);

        if (preg_match('/^100\d{10}$/', $chatId)) {
            return '-'.$chatId;
        }

        return $chatId;
    }

    /**
     * تست اتصال با متد getMe — خطای شبکه هرگز نباید صفحه را بترکاند.
     */
    public function testConnection(): void
    {
        // اگر فیلد خالی است از توکن ذخیره‌شده استفاده کن
        $token = trim($this->botToken) !== '' ? $this->botToken : (string) Setting::get('telegram_bot_token', '');

        if (trim($token) === '') {
            $this->connectionMessage = 'ابتدا Bot Token را وارد و ذخیره کنید.';

            return;
        }

        try {
            $res = Http::timeout(15)->get("https://api.telegram.org/bot{$token}/getMe");

            if ($res->successful() && $res->json('ok')) {
                $bot = $res->json('result.username');

                // تست دسترسی به کانال با chat id فعلی (نرمال‌شده)
                $chatId = $this->normalizeChatId($this->chatId !== '' ? $this->chatId : (string) Setting::get('telegram_chat_id', ''));
                $extra = '';
                if ($chatId !== '') {
                    $chat = Http::timeout(15)->get("https://api.telegram.org/bot{$token}/getChat", ['chat_id' => $chatId]);
                    if ($chat->successful() && $chat->json('ok')) {
                        $title = $chat->json('result.title') ?? $chat->json('result.username') ?? '';
                        $extra = ' | کانال: '.($title !== '' ? $title : $chatId).' ✔';
                    } else {
                        $extra = ' | کانال در دسترس نیست: '.($chat->json('description') ?? 'خطا').' (Chat ID: '.$chatId.')';
                    }
                }

                $this->connectionMessage = "اتصال موفق! ربات: @{$bot}{$extra}";
            } else {
                $this->connectionMessage = 'اتصال ناموفق — توکن را بررسی کنید. ('.($res->json('description') ?? 'کد '.$res->status()).')';
            }
        } catch (\Throwable $e) {
            Log::warning('Telegram getMe failed: '.$e->getMessage());
            $this->connectionMessage = 'دسترسی به سرور تلگرام ممکن نیست — اتصال اینترنت/پروکسی را بررسی کنید.';
        }
    }

    /**
     * تولید کپشن تبلیغاتی با AI برای محتوای انتخابی
     */
    public function generateCaption(): void
    {
        [$title, $desc, $url] = $this->contentFor($this->contentType, $this->selectedId);

        if (! $title) {
            $this->errorMessage = 'محتوایی برای انتشار یافت نشد.';

            return;
        }

        try {
            $this->caption = app(AiService::class)->complete(
                'تو مدیر شبکه‌های اجتماعی یک وب‌سایت خدمات برنامه‌نویسی هستی. یک پست تلگرام جذاب و کوتاه (حداکثر ۴ خط) با ایموجی مناسب و دعوت به اقدام بنویس. لحن دوستانه و حرفه‌ای.',
                "موضوع: {$title}\nتوضیح: {$desc}\nلینک: {$url}"
            )."\n\n🔗 {$url}";
        } catch (\Throwable $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    /**
     * ارسال به تلگرام
     */
    public function publish(): void
    {
        if (! $this->connected) {
            $this->errorMessage = 'ابتدا اتصال تلگرام را ذخیره کنید.';

            return;
        }

        [$title, $desc, $url] = $this->contentFor($this->contentType, $this->selectedId);

        // نرمال‌سازی chat id (پیشوند ‎-100 برای کانال/سوپرگروپ)
        $this->chatId = $this->normalizeChatId($this->chatId);

        // کپشن (اعم از AI یا دستی) باید برای parse_mode=HTML ایمن شود —
        // کاراکترهای < و > و & داخل متن باعث خطای 400 تلگرام می‌شوند.
        $text = $this->caption !== ''
            ? htmlspecialchars($this->caption, ENT_QUOTES, 'UTF-8')."\n\n🔗 {$url}"
            : '📢 <b>'.e($title)."</b>\n\n".e($desc)."\n\n🔗 {$url}";

        try {
            $res = Http::timeout(20)->post("https://api.telegram.org/bot{$this->botToken}/sendMessage", [
                'chat_id' => $this->chatId,
                'text' => $text,
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => false,
            ]);

            if ($res->successful() && $res->json('ok')) {
                $this->successMessage = 'پست با موفقیت در تلگرام منتشر شد.';
                $this->caption = '';
            } else {
                $description = (string) ($res->json('description') ?? 'کد '.$res->status());
                $this->errorMessage = 'ارسال ناموفق: '.$description;

                // راهنمای رفع رایج‌ترین خطاها
                if (str_contains($description, 'chat not found')) {
                    $this->errorMessage .= ' — Chat ID باید ‎-100… باشد (مثال: ‎-1004371249332) و ربات باید Admin کانال باشد. Chat ID فعلی: '.$this->chatId;
                } elseif (str_contains($description, 'bot was kicked') || str_contains($description, 'not a member')) {
                    $this->errorMessage .= ' — ربات از کانال خارج شده یا عضو نیست؛ دوباره به عنوان Admin اضافه کنید.';
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Telegram publish failed: '.$e->getMessage());
            $this->errorMessage = 'خطا در ارسال به تلگرام.';
        }
    }

    /**
     * عنوان/توضیح/لینک محتوای انتخابی
     *
     * @return array{0: string, 1: string, 2: string}
     */
    private function contentFor(string $type, int $id): array
    {
        return match ($type) {
            'latest_product' => $this->latestProduct($id),
            default => $this->latestPost($id),
        };
    }

    private function latestPost(int $id): array
    {
        $post = $id
            ? BlogPost::find($id)
            : BlogPost::where('status', 'published')->latest()->first();

        if (! $post) {
            return ['', '', ''];
        }

        return [
            $post->title,
            Str::limit($post->excerpt ?? '', 150),
            route('public.blog.show', $post->slug ?? $post->id),
        ];
    }

    private function latestProduct(int $id): array
    {
        $product = $id
            ? Product::find($id)
            : Product::published()->latest()->first();

        if (! $product) {
            return ['', '', ''];
        }

        return [
            $product->name,
            Str::limit($product->short_description ?? '', 150),
            route('public.products.show', $product->slug),
        ];
    }

    /**
     * لیست محتوای قابل انتخاب (مقالات + محصولات جدید)
     */
    public function render()
    {
        return view('ai::livewire.admin.telegram-publisher', [
            'posts' => BlogPost::where('status', 'published')->latest()->take(10)->get(['id', 'title']),
            'products' => Product::published()->latest()->take(10)->get(['id', 'name']),
        ]);
    }
}
