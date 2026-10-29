<div>
    <article class="scroll-mt-28">
        <header class="mb-8">
            <a href="{{ url('/dashboard?tab=tickets') }}" wire:navigate class="inline-flex items-center gap-1.5 text-sm text-slate-500 dark:text-slate-400 hover:text-primary-600 dark:hover:text-primary-400 transition mb-5">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                بازگشت به تیکت‌ها
            </a>

            <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2 mb-3">
                        <span class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-primary-50 dark:bg-primary-950/50 text-primary-600 dark:text-primary-400">{{ $ticket->status_label }}</span>
                        <span class="px-2.5 py-1 text-xs font-medium rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400">اولویت: {{ $ticket->priority_label }}</span>
                    </div>
                    <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ $ticket->subject }}</h1>
                </div>

                {{-- بستن تیکت (زنده) --}}
                @if ($ticket->status !== 'closed')
                    <button type="button" wire:click="closeTicket" wire:confirm="تیکت بسته شود؟ پس از بستن امکان پاسخ نیست."
                            class="btn-outline !px-4 !py-2 !text-xs !border-rose-200 !text-rose-500 hover:!bg-rose-50 dark:hover:!bg-rose-950/40">
                        بستن تیکت
                    </button>
                @endif
            </div>
        </header>

        {{-- گفتگو --}}
        <section class="mb-8 space-y-4">
            {{-- پیام اول (خود تیکت) --}}
            <div class="flex gap-3 justify-start">
                <span class="w-9 h-9 rounded-full bg-gradient-to-br from-primary-500 to-accent-500 flex items-center justify-center text-white text-sm font-bold shrink-0 mt-1">{{ mb_substr($ticket->user?->name ?? 'شما', 0, 1) }}</span>
                <div class="glass-card p-5 max-w-[85%] sm:max-w-[70%]">
                    <div class="flex items-center gap-2 mb-2">
                        <p class="text-sm font-bold text-slate-800 dark:text-slate-200">{{ $ticket->user?->name ?? 'شما' }}</p>
                        <span class="text-xs text-slate-400">{{ fa_digits($ticket->created_at->format('Y/m/d H:i')) }}</span>
                    </div>
                    <p class="text-sm leading-[2] whitespace-pre-line text-slate-600 dark:text-slate-300">{{ $ticket->message }}</p>
                </div>
            </div>

            @foreach ($replies as $reply)
                @if ($reply->is_staff_reply)
                    {{-- پاسخ کارشناس: سمت راست با تم متفاوت --}}
                    <div class="flex gap-3 justify-end" wire:key="reply-{{ $reply->id }}">
                        <div class="rounded-2xl p-5 max-w-[85%] sm:max-w-[70%] bg-primary-600 text-white">
                            <div class="flex items-center gap-2 mb-2">
                                <p class="text-sm font-bold">پشتیبانی</p>
                                <span class="text-xs text-primary-100/80">{{ fa_digits($reply->created_at->format('Y/m/d H:i')) }}</span>
                            </div>
                            <p class="text-sm leading-[2] whitespace-pre-line">{{ $reply->message }}</p>
                        </div>
                        <span class="w-9 h-9 rounded-full bg-primary-600 flex items-center justify-center text-white text-sm shrink-0 mt-1">
                            <x-icon name="shield" size="4" />
                        </span>
                    </div>
                @else
                    {{-- پاسخ خود کاربر --}}
                    <div class="flex gap-3 justify-start" wire:key="reply-{{ $reply->id }}">
                        <span class="w-9 h-9 rounded-full bg-gradient-to-br from-primary-500 to-accent-500 flex items-center justify-center text-white text-sm font-bold shrink-0 mt-1">{{ mb_substr($reply->user?->name ?? 'شما', 0, 1) }}</span>
                        <div class="glass-card p-5 max-w-[85%] sm:max-w-[70%]">
                            <div class="flex items-center gap-2 mb-2">
                                <p class="text-sm font-bold text-slate-800 dark:text-slate-200">{{ $reply->user?->name ?? 'شما' }}</p>
                                <span class="text-xs text-slate-400">{{ fa_digits($reply->created_at->format('Y/m/d H:i')) }}</span>
                            </div>
                            <p class="text-sm leading-[2] whitespace-pre-line text-slate-600 dark:text-slate-300">{{ $reply->message }}</p>
                        </div>
                    </div>
                @endif
            @endforeach

            {{-- صفحه‌بندی پاسخ‌ها (زنده) --}}
            @if ($replies->hasPages())
                <div class="flex justify-center">{{ $replies->links() }}</div>
            @endif
        </section>

        {{-- فرم پاسخ / پیام بسته‌شدن --}}
        @if ($ticket->status !== 'closed')
            <section class="glass-card p-6">
                <h2 class="mb-4 font-bold text-slate-900 dark:text-white">پاسخ جدید</h2>
                <form wire:submit="reply" class="space-y-4">
                    <div>
                        <textarea wire:model.blur="newReply" rows="3" maxlength="3000" required
                                  placeholder="پیامت را بنویس..."
                                  class="w-full px-4 py-3 rounded-xl bg-white/80 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 placeholder:text-slate-400 focus:border-primary-400 focus:ring-2 focus:ring-primary-400/20 outline-none transition-all resize-y text-sm"></textarea>
                        @error('newReply') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex items-center gap-3">
                        <button type="submit" wire:loading.attr="disabled" wire:target="reply" class="btn-primary !px-6 !py-3 !text-xs">
                            <span wire:loading.remove wire:target="reply">ارسال پاسخ</span>
                            <span wire:loading wire:target="reply">در حال ارسال...</span>
                        </button>
                        <span wire:loading wire:target="reply" class="text-xs text-slate-400">پاسخ شما ثبت می‌شود...</span>
                    </div>
                </form>
            </section>
        @else
            <div class="glass-card p-6 text-center">
                <p class="text-sm text-slate-500 dark:text-slate-400">این تیکت بسته شده است. برای موضوع جدید، تیکت جدیدی ثبت کن.</p>
                <a href="{{ route('user.tickets.create') }}" wire:navigate class="btn-outline !px-5 !py-2.5 !text-xs mt-4 inline-block">تیکت جدید</a>
            </div>
        @endif
    </article>
</div>
