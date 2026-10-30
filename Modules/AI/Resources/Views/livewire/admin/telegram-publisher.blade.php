<div>
    {{-- ===== اتصال تلگرام ===== --}}
    <div class="mb-5 rounded-2xl border border-slate-200 p-4 dark:border-slate-700">
        <div class="mb-3 flex items-center justify-between">
            <p class="text-sm font-bold text-slate-700 dark:text-slate-200">۱. اتصال ربات به کانال</p>
            @if ($connected)
                <span class="flex items-center gap-1.5 rounded-full bg-emerald-100 px-2.5 py-1 text-[10px] font-bold text-emerald-600 dark:bg-emerald-900/40 dark:text-emerald-400">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> متصل
                </span>
            @endif
        </div>

        <div class="grid gap-3 sm:grid-cols-2">
            <div>
                <label class="mb-1.5 block text-xs font-bold text-slate-600 dark:text-slate-300">Bot Token</label>
                <input type="password" wire:model="botToken" dir="ltr" placeholder="123456:ABC-DEF..."
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 font-mono text-xs dark:border-slate-700 dark:bg-surface-800">
                @error('botToken') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-bold text-slate-600 dark:text-slate-300">Chat / Channel ID</label>
                <input type="text" wire:model="chatId" dir="ltr" placeholder="@mychannel یا -100123456789"
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 font-mono text-xs dark:border-slate-700 dark:bg-surface-800">
                @error('chatId') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="mt-3 flex flex-wrap gap-2">
            <button type="button" wire:click="saveConnection" wire:loading.attr="disabled" wire:target="saveConnection"
                    class="rounded-xl bg-sky-500 px-4 py-2 text-xs font-bold text-white shadow-md shadow-sky-500/25 transition hover:bg-sky-600 disabled:opacity-50">
                ذخیره اتصال
            </button>
            <button type="button" wire:click="testConnection"
                    class="rounded-xl border border-sky-300 px-4 py-2 text-xs font-bold text-sky-600 transition hover:bg-sky-50 dark:border-sky-700 dark:hover:bg-sky-950/30">
                تست اتصال
            </button>
            @if ($connectionMessage)
                <span class="self-center text-[11px] font-bold text-slate-500 dark:text-slate-400">{{ $connectionMessage }}</span>
            @endif
        </div>
        <p class="mt-2 text-[10px] text-slate-400">ربات باید ابتدا در کانال Admin شود. Chat ID کانال خصوصی با -100 شروع می‌شود.</p>
    </div>

    @include('ai::livewire.admin.telegram-publish')
</div>
