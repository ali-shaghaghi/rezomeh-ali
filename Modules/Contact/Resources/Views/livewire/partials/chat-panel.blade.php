{{-- پنل چت آنلاین (#۲۱) — ربات هوشمند با ظاهر چت واقعی --}}
<div class="flex flex-col" style="height: min(52vh, 380px)">
    {{-- نوار وضعیت آنلاین --}}
    <div class="flex items-center justify-between border-b border-slate-100 px-4 py-2.5 dark:border-slate-700">
        <div class="flex items-center gap-2">
            <span class="relative flex h-2.5 w-2.5">
                <span class="absolute inline-flex w-full h-full rounded-full animate-ping bg-emerald-400 opacity-60"></span>
                <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
            </span>
            <span class="text-xs font-bold text-slate-700 dark:text-slate-200">{{ __('public.contact.online') }}</span>
        </div>
        @if (! empty($this->chatThread))
            <button type="button" wire:click="clearChat"
                    class="text-[10px] font-medium text-slate-400 transition hover:text-rose-500"
                    aria-label="{{ __('public.contact.clear_chat') }}">
                {{ __('public.contact.clear_chat') }}
            </button>
        @endif
    </div>

    {{-- بدنه چت --}}
    <div id="chat-scroll" class="flex-1 p-4 space-y-3 overflow-y-auto">
        @if (empty($this->chatThread))
            <div class="flex gap-2.5">
                <span class="flex items-center justify-center w-8 h-8 text-white rounded-full shrink-0 bg-gradient-to-br from-primary-500 to-violet-600">
                    <x-heroicon name="cpu-chip" class="w-4 h-4" />
                </span>
                <div class="max-w-[80%]">
                    <div class="rounded-2xl rounded-tr-sm bg-slate-100 px-3.5 py-2.5 text-xs leading-relaxed text-slate-700 dark:bg-slate-700 dark:text-slate-200">
                        {{ __('public.contact.welcome') }}
                    </div>
                    <p class="mt-1 pr-1 text-[10px] text-slate-400">{{ __('public.contact.bot') }}</p>
                </div>
            </div>
        @else
            @foreach ($this->chatThread as $msg)
                @if ($msg['from'] === 'user')
                    <div class="flex flex-row-reverse gap-2.5">
                        {{-- آیکون آدم برای پیام کاربر --}}
                        <span class="flex items-center justify-center w-8 h-8 rounded-full shrink-0 bg-primary-100 text-primary-600 dark:bg-primary-900/40 dark:text-primary-400">
                            <x-heroicon name="user" class="w-4 h-4" />
                        </span>
                        <div class="max-w-[80%]">
                            <div class="rounded-2xl rounded-tl-sm bg-primary-500 px-3.5 py-2.5 text-xs leading-relaxed text-white">
                                {{ $msg['text'] }}
                            </div>
                            <p class="mt-1 pl-1 text-left text-[10px] text-slate-400">{{ date('H:i', $msg['time']) }}</p>
                        </div>
                    </div>
                @else
                    {{-- آیکون ربات برای پاسخ ربات / آدم برای پاسخ ادمین --}}
                    <div class="flex gap-2.5">
                        @if (($msg['sender'] ?? 'bot') === 'admin')
                            <span class="flex items-center justify-center w-8 h-8 rounded-full shrink-0 bg-emerald-100 text-emerald-600 dark:bg-emerald-900/40 dark:text-emerald-400">
                                <x-heroicon name="user" class="w-4 h-4" />
                            </span>
                        @else
                            <span class="flex items-center justify-center w-8 h-8 text-white rounded-full shrink-0 bg-gradient-to-br from-primary-500 to-violet-600">
                                <x-heroicon name="cpu-chip" class="w-4 h-4" />
                            </span>
                        @endif
                        <div class="max-w-[80%]">
                            <div class="rounded-2xl rounded-tr-sm bg-slate-100 px-3.5 py-2.5 text-xs leading-relaxed text-slate-700 dark:bg-slate-700 dark:text-slate-200">
                                {{ $msg['text'] }}
                            </div>
                            <p class="mt-1 pr-1 text-[10px] text-slate-400">
                                {{ ($msg['sender'] ?? 'bot') === 'admin' ? __('public.contact.support') : __('public.contact.bot') }}
                                · {{ date('H:i', $msg['time']) }}
                            </p>
                        </div>
                    </div>
                @endif
            @endforeach
        @endif
    </div>

    {{-- در حال تایپ — طبیعی‌تر کردن پاسخ ربات --}}
    @if ($botTyping)
        <div class="flex gap-2.5 px-4 pb-2">
            <span class="flex items-center justify-center w-8 h-8 text-white rounded-full shrink-0 bg-gradient-to-br from-primary-500 to-violet-600">
                <x-heroicon name="chat-bubble" class="w-4 h-4" />
            </span>
            <div class="flex items-center gap-1 px-4 py-3 rounded-tr-sm rounded-2xl bg-slate-100 dark:bg-slate-800">
                <span class="h-1.5 w-1.5 animate-bounce rounded-full bg-slate-400" style="animation-delay: 0ms"></span>
                <span class="h-1.5 w-1.5 animate-bounce rounded-full bg-slate-400" style="animation-delay: 150ms"></span>
                <span class="h-1.5 w-1.5 animate-bounce rounded-full bg-slate-400" style="animation-delay: 300ms"></span>
            </div>
        </div>
    @endif

    {{-- فرم ارسال پیام --}}
    <form wire:submit.prevent="sendChat" class="p-3 border-t border-slate-100 dark:border-slate-700">
        @error('chatInput')
            <p class="mb-1.5 px-1 text-[10px] font-bold text-rose-600 dark:text-rose-400">{{ $message }}</p>
        @enderror
        <div class="flex items-end gap-2">
            <textarea wire:model="chatInput"
                      wire:keydown.enter.prevent="sendChat"
                      rows="1"
                      placeholder="{{ __('public.contact.chat_placeholder') }}"
                      class="max-h-24 flex-1 resize-none rounded-2xl border border-slate-200 bg-white/70 px-3.5 py-2.5 text-xs transition focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-slate-800/60 dark:text-white"></textarea>
            <button type="submit"
                    class="flex items-center justify-center w-10 h-10 text-white transition shadow-lg shrink-0 rounded-2xl bg-primary-500 shadow-primary-500/25 hover:bg-primary-600 disabled:opacity-50"
                    wire:loading.attr="disabled"
                    wire:target="sendChat"
                    aria-label="{{ __('public.contact.send') }}">
                <span wire:loading.remove wire:target="sendChat">
                    <svg class="w-5 h-5 -scale-x-100" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/>
                    </svg>
                </span>
                <span wire:loading wire:target="sendChat" class="block w-5 h-5 border-2 border-white rounded-full animate-spin border-t-transparent"></span>
            </button>
        </div>
    </form>
</div>

{{-- اسکرول خودکار به آخرین پیام — بعد از هر پیام جدید (کاربر یا ربات) --}}
<script>
    document.addEventListener('livewire:initialized', () => {
        const scroll = () => {
            const el = document.getElementById('chat-scroll');
            if (el) el.scrollTop = el.scrollHeight;
        };
        scroll();

        // هر commit لایووایر (ارسال پیام، پاسخ ربات، typing) → اسکرول به پایین
        Livewire.hook('commit', ({ succeed }) => {
            succeed(() => {
                // پس از آپدیت DOM
                queueMicrotask(scroll);
                // ضمانت دوباره پس از رندر morph (مخصوصاً بعد از پاسخ ربات)
                setTimeout(scroll, 120);
            });
        });

        // fallback برای morph در کامپوننت‌های قدیمی‌تر
        Livewire.hook('morph.updated', ({ el }) => {
            if (el.closest && el.closest('#chat-scroll')) scroll();
        });
    });
</script>
