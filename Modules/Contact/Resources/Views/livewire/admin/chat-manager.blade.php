{{-- مدیریت چت آنلاین (#۲۱) — دیدن گفتگوها و پاسخ دستی --}}
<div class="space-y-6">
    {{-- هدر + آمار --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold gradient-text">چت آنلاین</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                گفتگوهای ربات هوشمند و کاربران. می‌توانید خودتان پاسخ بدهید — کاربر در سایت شما را می‌بیند.
            </p>
        </div>
        <div class="flex grid flex-wrap grid-cols-4 gap-3">
            <div class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-center shadow-sm dark:border-slate-700 dark:bg-surface-800">
                <p class="text-lg font-bold text-primary-600 dark:text-primary-400">{{ $this->stats['open'] }}</p>
                <p class="text-[10px] font-medium text-slate-400">باز</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-center shadow-sm dark:border-slate-700 dark:bg-surface-800">
                <p class="text-lg font-bold text-slate-700 dark:text-slate-200">{{ $this->stats['total'] }}</p>
                <p class="text-[10px] font-medium text-slate-400">کل گفتگو</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-center shadow-sm dark:border-slate-700 dark:bg-surface-800">
                <p class="text-lg font-bold text-violet-600 dark:text-violet-400">{{ $this->stats['bot'] }}</p>
                <p class="text-[10px] font-medium text-slate-400">پاسخ ربات</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-center shadow-sm dark:border-slate-700 dark:bg-surface-800">
                <p class="text-lg font-bold text-emerald-600 dark:text-emerald-400">{{ $this->stats['admin'] }}</p>
                <p class="text-[10px] font-medium text-slate-400">پاسخ شما</p>
            </div>
        </div>
    </div>

    <div class="grid gap-6 mb-8 lg:grid-cols-3">

        <div class="grid gap-5 chart-card lg:col-span-1">
            {{-- لیست گفتگوها --}}
            <div class="flex flex-col overflow-hidden bg-white border rounded-2xl border-slate-200 shadow-card dark:border-slate-700 dark:bg-surface-800">
                {{-- فیلترها --}}
                <div class="flex gap-1 p-2 border-b border-slate-100 dark:border-slate-700">
                    @foreach ([['open', 'باز'], ['all', 'همه'], ['closed', 'بسته']] as $f)
                        <button type="button" wire:click="setFilter('{{ $f[0] }}')"
                                class="flex-1 rounded-lg px-3 py-2 text-xs font-medium transition {{ $filter === $f[0] ? 'bg-primary-50 text-primary-600 dark:bg-primary-900/40 dark:text-primary-300' : 'text-slate-500 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-700' }}">
                            {{ $f[1] }}
                        </button>
                    @endforeach
                </div>

                <div class="max-h-[60vh] flex-1 overflow-y-auto p-2">
                    @if ($this->conversations->isEmpty())
                        <div class="px-4 py-12 text-center">
                            <div class="flex items-center justify-center w-12 h-12 mx-auto mb-3 rounded-full bg-slate-100 dark:bg-slate-700">
                                <x-heroicon name="chat-bubble" class="w-6 h-6 text-slate-400" />
                            </div>
                            <p class="text-sm text-slate-500 dark:text-slate-400">هنوز گفتگویی وجود ندارد</p>
                            <p class="mt-1 text-xs text-slate-400">وقتی کاربری چت کند اینجا نمایش داده می‌شود</p>
                        </div>
                    @else
                        @foreach ($this->conversations as $conv)
                            <button type="button" wire:click="select({{ $conv->id }})"
                                    class="mb-1 flex w-full items-start gap-3 rounded-xl p-3 text-right transition {{ $selectedId === $conv->id ? 'bg-primary-50 dark:bg-primary-900/30' : 'hover:bg-slate-50 dark:hover:bg-slate-700/50' }}">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full {{ $selectedId === $conv->id ? 'bg-primary-500 text-white' : 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-300' }}">
                                    <x-heroicon name="user" class="w-4 h-4" />
                                </span>
                                <span class="flex-1 min-w-0">
                                    <span class="flex items-center justify-between gap-2">
                                        <span class="text-sm font-bold truncate text-slate-700 dark:text-slate-200">{{ $conv->display_name }}</span>
                                        @if ($conv->status === 'open')
                                            <span class="w-2 h-2 rounded-full shrink-0 bg-emerald-500"></span>
                                        @endif
                                    </span>
                                    <span class="mt-0.5 block truncate text-xs text-slate-400">
                                        {{ $conv->messages->first()?->text ?: '...' }}
                                    </span>
                                    <span class="mt-1 block text-[10px] text-slate-400">
                                        {{ $conv->last_activity_at?->diffForHumans() ?: '—' }}
                                    </span>
                                </span>
                            </button>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>

        {{-- پنل گفتگوی انتخاب‌شده --}}
        <div class="flex flex-col overflow-hidden bg-white border chart-card lg:col-span-2 rounded-2xl border-slate-200 shadow-card dark:border-slate-700 dark:bg-surface-800">
            @php $conv = $this->selectedConversation(); @endphp
            @if (! $conv)
                <div class="px-4 py-16 text-center">
                    <div class="flex items-center justify-center w-12 h-12 mx-auto mb-3 rounded-full bg-slate-100 dark:bg-slate-700">
                        <x-heroicon name="chat-bubble" class="w-6 h-6 text-slate-400" />
                    </div>
                    <p class="text-sm text-slate-500 dark:text-slate-400">یک گفتگو را از لیست انتخاب کنید</p>
                </div>
            @else
                {{-- هدر گفتگو --}}
                <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-3 border-b border-slate-100 dark:border-slate-700">
                    <div class="flex items-center gap-2.5">
                        <span class="relative flex h-2.5 w-2.5">
                            @if ($conv->status === 'open')
                                <span class="absolute inline-flex w-full h-full rounded-full animate-ping bg-emerald-400 opacity-60"></span>
                                <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                            @else
                                <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-slate-400"></span>
                            @endif
                        </span>
                        <div>
                            <p class="text-sm font-bold text-slate-700 dark:text-slate-200">{{ $conv->display_name }}</p>
                            <p class="text-[10px] text-slate-400">
                                @if ($conv->user)
                                    {{ $conv->user->email }}
                                @else
                                    کاربر مهمان
                                @endif
                                · شروع: {{ $conv->created_at?->format('Y/m/d H:i') }}
                            </p>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        @if ($conv->status === 'open')
                            <button type="button" wire:click="toggleBot({{ $conv->id }})"
                                    class="rounded-lg border px-3 py-1.5 text-xs font-medium transition {{ $conv->bot_enabled
                                        ? 'border-emerald-200 bg-emerald-50 text-emerald-700 hover:border-rose-300 hover:bg-rose-50 hover:text-rose-600 dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-400'
                                        : 'border-rose-200 bg-rose-50 text-rose-600 hover:border-emerald-300 hover:bg-emerald-50 hover:text-emerald-600 dark:border-rose-800 dark:bg-rose-900/20 dark:text-rose-400' }}"
                                    title="{{ $conv->bot_enabled ? 'ربات فعال است — برای پاسخ دستی غیرفعال کنید' : 'ربات غیرفعال است — شما پاسخ می‌دهید' }}">
                                {{ $conv->bot_enabled ? 'ربات: فعال' : 'ربات: غیرفعال' }}
                            </button>
                            <button type="button" wire:click="close({{ $conv->id }})" wire:confirm="این گفتگو بسته شود؟"
                                    class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-500 transition hover:border-rose-300 hover:text-rose-600 dark:border-slate-600 dark:text-slate-400">
                                بستن گفتگو
                            </button>
                        @else
                            <button type="button" wire:click="reopen({{ $conv->id }})"
                                    class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-500 transition hover:border-emerald-300 hover:text-emerald-600 dark:border-slate-600 dark:text-slate-400">
                                باز کردن مجدد
                            </button>
                        @endif
                    </div>
                </div>

                {{-- پیام‌ها --}}
                <div id="admin-chat-scroll" class="max-h-[52vh] min-h-[280px] flex-1 space-y-3 overflow-y-auto p-4">
                    @forelse ($conv->messages as $msg)
                        @if ($msg->from === 'user')
                            <div class="flex flex-row-reverse gap-2.5">
                                <span class="flex items-center justify-center w-8 h-8 rounded-full shrink-0 bg-primary-100 text-primary-600 dark:bg-primary-900/40 dark:text-primary-400">
                                    <x-heroicon name="user" class="w-4 h-4" />
                                </span>
                                <div class="max-w-[75%]">
                                    <div class="rounded-2xl rounded-tl-sm bg-primary-500 px-3.5 py-2.5 text-sm leading-relaxed text-white">
                                        {{ $msg->text }}
                                    </div>
                                    <p class="mt-1 pl-1 text-left text-[10px] text-slate-400">{{ $msg->created_at?->format('H:i') }}</p>
                                </div>
                            </div>
                        @else
                            <div class="flex gap-2.5">
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full {{ $msg->from === 'admin' ? 'bg-emerald-500 text-white' : 'bg-gradient-to-br from-primary-500 to-violet-600 text-white' }}">
                                    @if ($msg->from === 'admin')
                                        <x-heroicon name="user-circle" class="w-4 h-4" />
                                    @else
                                        <x-heroicon name="chat-bubble" class="w-4 h-4" />
                                    @endif
                                </span>
                                <div class="max-w-[75%]">
                                    <span class="text-[10px] font-bold {{ $msg->from === 'admin' ? 'text-emerald-600 dark:text-emerald-400' : 'text-violet-600 dark:text-violet-400' }}">
                                        {{ $msg->from === 'admin' ? 'پاسخ شما'.($msg->admin ? ' · '.$msg->admin->name : '') : 'ربات هوشمند' }}
                                    </span>
                                    <div class="mt-0.5 rounded-2xl rounded-tr-sm bg-slate-100 px-3.5 py-2.5 text-sm leading-relaxed text-slate-700 dark:bg-slate-700/60 dark:text-slate-200">
                                        {{ $msg->text }}
                                    </div>
                                    <p class="mt-1 pr-1 text-[10px] text-slate-400">{{ $msg->created_at?->format('H:i') }}</p>
                                </div>
                            </div>
                        @endif
                    @empty
                        <div class="py-10 text-center">
                            <p class="text-sm text-slate-400">این گفتگو پیامی ندارد</p>
                        </div>
                    @endforelse

                    {{-- فرم پاسخ ادمین --}}
                    @if ($conv->status === 'open')
                        <form wire:submit.prevent="sendReply" class="p-3 border-t border-slate-100 dark:border-slate-700">
                            @error('reply')
                                <p class="mb-1.5 px-1 text-[10px] font-bold text-rose-600 dark:text-rose-400">{{ $message }}</p>
                            @enderror
                            <div class="flex items-end gap-2">
                                <textarea wire:model="reply" rows="1" placeholder="پاسخ شما به کاربر..."
                                            class="max-h-24 flex-1 resize-none rounded-2xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-surface-900 dark:text-white"></textarea>
                                <button type="submit"
                                        class="flex items-center justify-center w-10 h-10 text-white transition shadow-lg shrink-0 rounded-2xl bg-emerald-500 shadow-emerald-500/25 hover:bg-emerald-600 disabled:opacity-50"
                                        wire:loading.attr="disabled" wire:target="sendReply"
                                        aria-label="ارسال پاسخ">
                                    <span wire:loading.remove wire:target="sendReply">
                                        <svg class="w-5 h-5 -scale-x-100" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/>
                                        </svg>
                                    </span>
                                    <span wire:loading wire:target="sendReply" class="block w-5 h-5 border-2 border-white rounded-full animate-spin border-t-transparent"></span>
                                </button>
                            </div>
                            <p class="mt-1.5 px-1 text-[10px] text-slate-400">پاسخ شما بلافاصله در چت کاربر نمایش داده می‌شود.</p>
                        </form>
                    @else
                        <div class="p-3 text-center border-t border-slate-100 dark:border-slate-700">
                            <p class="text-xs text-slate-400">این گفتگو بسته است. برای پاسخ دادن آن را باز کنید.</p>
                        </div>
                    @endif

                </div>


                
            @endif
        </div>

    </div>
    
</div>

{{-- اسکرول خودکار به آخرین پیام --}}
<script>
    document.addEventListener('livewire:initialized', () => {
        const scroll = () => {
            const el = document.getElementById('admin-chat-scroll');
            if (el) el.scrollTop = el.scrollHeight;
        };
        scroll();
        Livewire.hook('morph.updated', ({ el }) => {
            if (el.closest && el.closest('#admin-chat-scroll')) scroll();
        });
    });
</script>

