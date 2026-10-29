<div x-data="{ formOpen: true }">
    {{-- ===== سربرگ + بازگشت — الگوی my-consultations ===== --}}
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <a href="{{ url('/dashboard?tab=tickets') }}" wire:navigate class="inline-flex items-center gap-1.5 text-sm text-slate-500 dark:text-slate-400 hover:text-primary-600 dark:hover:text-primary-400 transition">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            بازگشت به تیکت‌ها
        </a>
    </div>

    {{-- ===== فرم تیکت جدید (Livewire — بدون رفرش) ===== --}}
    <div x-show="formOpen" x-cloak x-transition.duration.200ms>
        <section class="glass-card p-5 sm:p-6">
            <h2 class="flex items-center gap-2 mb-5 font-bold text-slate-900 dark:text-white">
                <x-icon name="mail" class="text-primary-500" />
                تیکت جدید
            </h2>

            {{-- خطاهای اعتبارسنجی --}}
            @if ($errors->any())
                <div class="mb-5 p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 space-y-1">
                    @foreach ($errors->all() as $error)
                        <p class="text-xs font-bold text-rose-600 dark:text-rose-400">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form wire:submit="saveTicket" class="space-y-4">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="ticket-subject" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">موضوع</label>
                        <input type="text" wire:model.blur="subject" id="ticket-subject" maxlength="190"
                               placeholder="مثلاً: سؤال درباره سفارش ORD-..."
                               class="w-full px-4 py-3 text-sm rounded-xl bg-white/80 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 placeholder:text-slate-400 focus:border-primary-400 focus:ring-2 focus:ring-primary-400/20 outline-none transition-all">
                        @error('subject') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="ticket-message" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">متن تیکت</label>
                        <textarea wire:model.blur="message" id="ticket-message" rows="4" maxlength="3000"
                                  placeholder="توضیح کامل مشکل یا درخواستت را بنویس..."
                                  class="w-full px-4 py-3 text-sm rounded-xl bg-white/80 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 placeholder:text-slate-400 focus:border-primary-400 focus:ring-2 focus:ring-primary-400/20 outline-none transition-all resize-y"></textarea>
                        @error('message') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="ticket-priority" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">اولویت</label>
                        <select wire:model="priority" id="ticket-priority"
                                class="w-full px-4 py-3 text-sm rounded-xl bg-white/80 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:border-primary-400 focus:ring-2 focus:ring-primary-400/20 outline-none transition-all">
                            @foreach (['low' => 'کم', 'medium' => 'متوسط', 'high' => 'زیاد', 'urgent' => 'فوری'] as $key => $label)
                                <option value="{{ $key }}" {{ $priority === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="ticket-category" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">دسته‌بندی</label>
                        <select wire:model="category" id="ticket-category"
                                class="w-full px-4 py-3 text-sm rounded-xl bg-white/80 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:border-primary-400 focus:ring-2 focus:ring-primary-400/20 outline-none transition-all">
                            @foreach (['general' => 'عمومی', 'order' => 'سفارش', 'billing' => 'مالی', 'technical' => 'فنی', 'suggestion' => 'پیشنهاد'] as $key => $label)
                                <option value="{{ $key }}" {{ $category === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <button type="submit" wire:loading.attr="disabled" wire:target="saveTicket" class="btn-primary !px-6 !py-3 !text-xs">
                        <span wire:loading.remove wire:target="saveTicket">ثبت تیکت</span>
                        <span wire:loading wire:target="saveTicket">در حال ثبت...</span>
                    </button>
                    <a href="{{ url('/dashboard?tab=tickets') }}" wire:navigate class="text-xs font-medium text-slate-500 dark:text-slate-400 hover:text-primary-600 dark:hover:text-primary-400">لغو</a>
                </div>
            </form>
        </section>
    </div>
</div>
