<div>
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <p class="text-xs text-slate-500 dark:text-slate-400">درخواست‌های در انتظار بررسی: {{ fa_digits($pendingRequests->count()) }}</p>
        <button type="button" wire:click="analyzeLatest" wire:loading.attr="disabled" wire:target="analyzeLatest"
                class="rounded-xl bg-violet-500 px-4 py-2.5 text-xs font-bold text-white shadow-md shadow-violet-500/25 transition hover:bg-violet-600 disabled:opacity-50">
            <span wire:loading.remove wire:target="analyzeLatest">🤖 تحلیل همه درخواست‌های در انتظار</span>
            <span wire:loading wire:target="analyzeLatest">در حال تحلیل...</span>
        </button>
    </div>

    {{-- لیست درخواست‌ها --}}
    <div class="grid gap-3 sm:grid-cols-2">
        @forelse ($pendingRequests as $request)
            <div class="glass-card glass-card-hover p-4" wire:key="ai-project-{{ $request->id }}">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <p class="truncate text-sm font-bold text-slate-800 dark:text-slate-100">{{ $request->title }}</p>
                        <p class="mt-0.5 text-[11px] text-slate-400">{{ $request->user?->name ?? '—' }} · {{ $request->project_type_name }}</p>
                    </div>
                    <button type="button" wire:click="analyzeOne({{ $request->id }})" wire:loading.attr="disabled" wire:target="analyzeOne({{ $request->id }})"
                            class="shrink-0 rounded-lg bg-violet-100 px-3 py-1.5 text-[11px] font-bold text-violet-600 transition hover:bg-violet-200 dark:bg-violet-900/40 dark:text-violet-400 disabled:opacity-50">
                        تحلیل
                    </button>
                </div>
            </div>
        @empty
            <div class="glass-card col-span-full p-8 text-center text-sm text-slate-500 dark:text-slate-400">درخواست در انتظاری وجود ندارد ✅</div>
        @endforelse
    </div>

    @if ($errorMessage)
        <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 p-3 text-xs font-bold text-rose-600 dark:border-rose-800 dark:bg-rose-950/30 dark:text-rose-400">
            {{ $errorMessage }}
        </div>
    @endif

    @if ($analysis)
        <div class="mt-5 whitespace-pre-line rounded-2xl border border-violet-200 bg-violet-50/50 p-4 text-sm leading-7 text-slate-700 dark:border-violet-800 dark:bg-violet-950/20 dark:text-slate-200">
            {!! nl2br(e($analysis)) !!}
        </div>
    @endif
</div>
