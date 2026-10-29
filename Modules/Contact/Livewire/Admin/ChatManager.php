<?php

namespace Modules\Contact\Livewire\Admin;

use Livewire\Attributes\Computed;
use Livewire\Component;
use Modules\Contact\Models\ChatConversation;
use Modules\Contact\Models\ChatMessage;

/**
 * مدیریت چت آنلاین در پنل ادمین (#۲۱).
 *
 * ادمین می‌تواند گفتگوهای ربات را ببیند، خودش پاسخ بدهد،
 * یا گفتگو را ببندد/بایگانی کند.
 */
class ChatManager extends Component
{
    public ?int $selectedId = null;

    public string $reply = '';

    public string $filter = 'open'; // open | all | closed

    protected function rules(): array
    {
        return [
            'reply' => ['required', 'string', 'min:1', 'max:2000'],
        ];
    }

    public function mount(): void
    {
        // اولین گفتگوی باز به‌صورت پیش‌فرض انتخاب می‌شود
        if (! $this->selectedId) {
            $first = $this->conversations()->first();
            $this->selectedId = $first?->id;
        }
    }

    public function select(int $id): void
    {
        $this->selectedId = $id;
        $this->reply = '';
    }

    public function setFilter(string $filter): void
    {
        $this->filter = in_array($filter, ['open', 'all', 'closed'], true) ? $filter : 'open';
        $first = $this->conversations()->first();
        $this->selectedId = $first?->id;
    }

    #[Computed]
    public function conversations()
    {
        $q = ChatConversation::with(['user', 'messages' => fn ($q) => $q->latest('id')->limit(1)])
            ->when($this->filter === 'open', fn ($q) => $q->where('status', 'open'))
            ->when($this->filter === 'closed', fn ($q) => $q->whereIn('status', ['closed', 'archived']))
            ->orderByDesc('last_activity_at');

        return $q->get();
    }

    #[Computed]
    public function selectedConversation(): ?ChatConversation
    {
        if (! $this->selectedId) {
            return null;
        }

        return ChatConversation::with(['messages.admin', 'user'])->find($this->selectedId);
    }

    #[Computed]
    public function stats(): array
    {
        return [
            'open' => ChatConversation::where('status', 'open')->count(),
            'total' => ChatConversation::count(),
            'messages' => ChatMessage::count(),
            'bot' => ChatMessage::where('from', 'bot')->count(),
            'admin' => ChatMessage::where('from', 'admin')->count(),
        ];
    }

    /**
     * ارسال پاسخ دستی ادمین به کاربر
     */
    public function sendReply(): void
    {
        $text = trim($this->reply);
        if ($text === '') {
            return;
        }

        $this->validate();

        $conv = $this->selectedConversation();
        if (! $conv) {
            return;
        }

        ChatMessage::create([
            'conversation_id' => $conv->id,
            'from' => 'admin',
            'text' => $text,
            'admin_id' => auth()->id(),
        ]);
        $conv->update(['last_activity_at' => now()]);

        $this->reply = '';
        unset($this->selectedConversation, $this->conversations, $this->stats);
    }

    /**
     * بستن گفتگو (دیگر برای کاربر به‌صورت فعال نمایش داده نمی‌شود)
     */
    public function close(int $id): void
    {
        $conv = ChatConversation::find($id);
        if ($conv) {
            $conv->update(['status' => 'closed']);
        }
        unset($this->conversations, $this->selectedConversation, $this->stats);
    }

    /**
     * باز کردن مجدد گفتگو
     */
    public function reopen(int $id): void
    {
        $conv = ChatConversation::find($id);
        if ($conv) {
            $conv->update(['status' => 'open']);
        }
        unset($this->conversations, $this->selectedConversation, $this->stats);
    }

    /**
     * غیرفعال/فعال کردن ربات برای این گفتگو —
     * غیرفعال: ربات دیگر پاسخ نمی‌دهد و ادمین خودش جواب می‌دهد
     */
    public function toggleBot(int $id): void
    {
        $conv = ChatConversation::find($id);
        if ($conv) {
            $conv->update(['bot_enabled' => ! $conv->bot_enabled]);
        }
        unset($this->conversations, $this->selectedConversation);
    }

    public function render()
    {
        return view('contact::livewire.admin.chat-manager');
    }
}
