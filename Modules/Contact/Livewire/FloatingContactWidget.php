<?php

namespace Modules\Contact\Livewire;

use Livewire\Attributes\Computed;
use Livewire\Component;
use Modules\Contact\Models\ChatConversation;
use Modules\Contact\Models\ChatMessage;
use Modules\Contact\Services\BotReplyService;
use Modules\Contact\Services\ContactService;

/**
 * ویجت شناور تماس — در همه صفحات mount می‌شود (سه لایوت).
 *
 * سه حالت دارد:
 *   ۱) channels  → شبکه‌های اجتماعی + تماس مستقیم
 *   ۲) chat      → چت آنلاین با ادمین/ربات هوشمند
 *   ۳) message   → فرم پیام سریع
 *
 * اگر هیچ مسیر ارتباطی فعال نباشد، کل ویجت رندر نمی‌شود (#۲۱).
 *
 * گفتگوها در دیتابیس دائمی می‌شوند تا ادمین بتواند آن‌ها را ببیند
 * و خودش پاسخ بدهد.
 */
class FloatingContactWidget extends Component
{
    // فیلدهای فرم پیام سریع
    public string $name = '';

    public string $email = '';

    public string $message = '';

    public bool $sent = false;

    public string $errorMessage = '';

    // تب فعال داخل ویجت: channels | chat — چت آنلاین پیش‌فرض است
    public string $tab = 'chat';

    // متن در حال تایپ کاربر در پنل چت
    public string $chatInput = '';

    // ponytail: حالت نمایشی "در حال تایپ" — هنگام تولید پاسخ ربات
    public bool $botTyping = false;

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:120'],
            'email' => ['nullable', 'email', 'max:190'],
            'message' => ['required', 'string', 'min:10', 'max:2000'],
            'chatInput' => ['nullable', 'string', 'max:1000'],
        ];
    }

    protected $validationAttributes = [
        'name' => 'نام',
        'email' => 'ایمیل',
        'message' => 'پیام',
        'chatInput' => 'پیام چت',
    ];

    public function mount(): void
    {
        // پیش‌پر نام/ایمیل کاربر واردشده
        $user = auth()->user();

        if ($user) {
            $this->name = $user->name;
            $this->email = $user->email ?? '';
        }
    }

    /**
     * گفتگوی جاری کاربر (از دیتابیس — دائمی بین سشن‌ها)
     */
    #[Computed]
    public function conversation(): ?ChatConversation
    {
        return $this->findOrCreateConversation(false);
    }

    /**
     * پیام‌های چت کاربر
     *
     * @return array<int, array{from: string, text: string, time: int}>
     */
    #[Computed]
    public function chatThread(): array
    {
        $conv = $this->conversation();

        if (! $conv) {
            return [];
        }

        return $conv->messages()
            ->orderBy('id')
            ->get()
            ->map(fn (ChatMessage $m) => [
                // برای کاربر، admin و bot یک شکل نمایش داده می‌شوند ولی
                // آیکون/برچسب متفاوت دارند (ربات = cpu، ادمین = آدم)
                'from' => $m->from === 'user' ? 'user' : 'bot',
                'sender' => $m->from, // user | bot | admin
                'text' => $m->text,
                'time' => $m->created_at?->timestamp ?? time(),
            ])
            ->all();
    }

    /**
     * پیدا کردن یا ساخت گفتگوی جاری.
     * کاربر واردشده: بر اساس user_id. مهمان: بر اساس uuid در سشن.
     */
    private function findOrCreateConversation(bool $create = true): ?ChatConversation
    {
        $user = auth()->user();

        if ($user) {
            $conv = ChatConversation::where('user_id', $user->id)->open()->latest('id')->first();
            if ($conv) {
                return $conv;
            }
            if (! $create) {
                return null;
            }

            return ChatConversation::create([
                'uuid' => \Illuminate\Support\Str::uuid()->toString(),
                'user_id' => $user->id,
                'guest_name' => $user->name,
                'status' => 'open',
                'last_activity_at' => now(),
            ]);
        }

        $uuid = session('chat_conv_uuid');
        if ($uuid) {
            $conv = ChatConversation::where('uuid', $uuid)->first();
            if ($conv) {
                return $conv;
            }
        }

        if (! $create) {
            return null;
        }

        $conv = ChatConversation::create([
            'uuid' => \Illuminate\Support\Str::uuid()->toString(),
            'session_id' => session()->getId(),
            'status' => 'open',
            'last_activity_at' => now(),
        ]);
        session(['chat_conv_uuid' => $conv->uuid]);

        return $conv;
    }

    /**
     * ارسال پیام سریع — ثبت در contact_messages + ایمیل مدیر
     */
    public function sendMessage(): void
    {
        $validated = $this->validate();

        try {
            app(ContactService::class)->sendMessage($validated, request());

            $this->sent = true;
            $this->errorMessage = '';

            // ریست فرم برای استفاده مجدد
            $this->reset(['name', 'email', 'message']);

            // اگر کاربر وارد شده باشد، نامش برگردانده می‌شود
            if (auth()->user()) {
                $this->name = auth()->user()->name;
            }

            $this->dispatch('contact-message-sent', message: 'پیام شما با موفقیت ارسال شد');
        } catch (\Throwable $e) {
            $this->errorMessage = 'ارسال پیام ناموفق بود؛ لطفاً دوباره تلاش کنید.';
        }
    }

    /**
     * ارسال یک پیام داخل پنل چت (#۲۱)
     * پیام کاربر به ترد اضافه می‌شود، سپس پاسخ ربات هوشمند تولید می‌شود.
     */
    public function sendChat(): void
    {
        $text = trim($this->chatInput);
        if ($text === '') {
            return;
        }

        $this->validateOnly('chatInput');

        $conv = $this->findOrCreateConversation();

        ChatMessage::create([
            'conversation_id' => $conv->id,
            'from' => 'user',
            'text' => $text,
        ]);
        $conv->update(['last_activity_at' => now()]);
        $this->chatInput = '';

        // اعلام "در حال تایپ" تا ربات طبیعی‌تر به نظر برسد (#۲۱)
        $this->botTyping = true;

        // ربات غیرفعال → ادمین خودش پاسخ می‌دهد؛ پاسخ خودکار ارسال نمی‌شود
        if ($conv->bot_enabled === false) {
            $this->botTyping = false;
            unset($this->chatThread);

            return;
        }

        // تاریخچه کامل گفتگو از دیتابیس — برای پاسخ واقعی‌تر
        $history = $conv->messages()
            ->orderBy('id')
            ->get()
            ->map(fn (ChatMessage $m) => [
                'from' => $m->from === 'user' ? 'user' : 'assistant',
                'text' => $m->text,
            ])
            ->all();

        $reply = app(BotReplyService::class)->reply($text);

        ChatMessage::create([
            'conversation_id' => $conv->id,
            'from' => 'bot',
            'text' => $reply,
        ]);
        $conv->update(['last_activity_at' => now()]);

        $this->botTyping = false;

        // کش کامپوننت دوباره ساخته می‌شود تا پیام جدید نمایش داده شود
        unset($this->chatThread);
    }

    /**
     * پاک کردن تاریخچه چت (بستن گفتگوی جاری)
     */
    public function clearChat(): void
    {
        $conv = $this->findOrCreateConversation(false);
        if ($conv) {
            $conv->update(['status' => 'closed']);
        }
        session()->forget('chat_conv_uuid');
        unset($this->chatThread);
    }

    /**
     * تغییر تب داخل ویجت
     */
    public function setTab(string $tab): void
    {
        $this->tab = in_array($tab, ['channels', 'chat'], true) ? $tab : 'chat';
    }

    public function render()
    {
        $service = app(ContactService::class);

        return view('contact::livewire.floating-contact-widget', [
            'socialLinks' => $service->socialLinks(),
            'contactInfo' => $service->contactInfo(),
            'whatsappLink' => $service->whatsappLink(),
            'hasChannels' => $service->hasChannels(),
        ]);
    }
}
