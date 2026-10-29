<p class="mb-3 text-xs font-bold text-slate-500 dark:text-slate-400">پیام سریع</p>

@if ($sent)
    <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-center dark:border-emerald-800 dark:bg-emerald-950/40"
         x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show" x-transition.opacity>
        <div class="mx-auto mb-2 flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-900/60">
            <svg class="h-5 w-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
            </svg>
        </div>
        <p class="text-sm font-bold text-emerald-700 dark:text-emerald-400">پیام شما ارسال شد!</p>
        <p class="mt-0.5 text-xs text-emerald-600/80 dark:text-emerald-400/70">به‌زودی پاسخ شما را می‌دهیم.</p>
    </div>
@endif

<form wire:submit.prevent="sendMessage" class="space-y-3">
    <div>
        <input type="text" wire:model.blur="name" placeholder="نام شما *"
               class="w-full rounded-xl border border-slate-200 bg-white/70 px-3.5 py-2.5 text-sm transition focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-slate-800/60 dark:text-white">
        @error('name')
            <p class="mt-1 text-xs font-bold text-rose-600 dark:text-rose-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <input type="email" wire:model.blur="email" placeholder="ایمیل (اختیاری)" dir="ltr"
               class="w-full rounded-xl border border-slate-200 bg-white/70 px-3.5 py-2.5 text-sm transition focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-slate-800/60 dark:text-white">
        @error('email')
            <p class="mt-1 text-xs font-bold text-rose-600 dark:text-rose-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <textarea wire:model.blur="message" rows="3" placeholder="پیام شما... *"
                  class="w-full resize-none rounded-xl border border-slate-200 bg-white/70 px-3.5 py-2.5 text-sm transition focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-slate-800/60 dark:text-white"></textarea>
        @error('message')
            <p class="mt-1 text-xs font-bold text-rose-600 dark:text-rose-400">{{ $message }}</p>
        @enderror
    </div>

    @if ($errorMessage)
        <p class="text-xs font-bold text-rose-600 dark:text-rose-400">{{ $errorMessage }}</p>
    @endif

    <button type="submit"
            wire:loading.attr="disabled" wire:target="sendMessage"
            class="flex w-full items-center justify-center gap-2 rounded-xl bg-primary-500 py-2.5 text-sm font-bold text-white shadow-lg shadow-primary-500/25 transition hover:bg-primary-600 disabled:opacity-60">
        <span wire:loading.remove wire:target="sendMessage">ارسال پیام</span>
        <span wire:loading wire:target="sendMessage" class="flex items-center gap-2">
            <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            در حال ارسال...
        </span>
    </button>
</form>
