<div>
    {{-- هدر --}}
    <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
        <div>
            <h2 class="text-2xl font-bold sm:text-3xl">{{ $ticket->subject }}</h2>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                #{{ $ticket->id }} — {{ $ticket->user?->name }} — {{ $ticket->created_at->format('Y/m/d H:i') }}
                @if($ticket->category) — {{ $ticket->category }} @endif
            </p>
        </div>
        <a href="{{ route('admin.tickets.index') }}"
           class="px-4 py-2 text-sm font-medium transition border rounded-xl border-slate-200 text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-400 dark:hover:bg-surface-800">
            بازگشت به لیست
        </a>
    </div>

    {{-- نوتیفیکیشن موفقیت --}}
    @if($successToast)
        <div class="px-4 py-3 mb-6 text-sm rounded-xl bg-emerald-100 text-emerald-700 animate-slide-up dark:bg-emerald-900/30 dark:text-emerald-300"
             x-data="{ show: true }" x-init="setTimeout(() => show = false, 3500)" x-show="show" x-transition.opacity.duration.500ms>
            {{ $successMessage }}
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- ستون اصلی: گفتگو --}}
        <div class="space-y-6 lg:col-span-2">
            {{-- پیام اول تیکت (کاربر) --}}
            <div class="p-6 chart-card">
                <div class="flex items-start gap-3">
                    <span class="flex items-center justify-center shrink-0 h-10 w-10 rounded-xl bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300 text-sm font-bold">
                        {{ mb_substr($ticket->user?->name ?? '؟', 0, 1) }}
                    </span>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2 mb-2">
                            <span class="text-sm font-bold">{{ $ticket->user?->name ?? 'کاربر حذف‌شده' }}</span>
                            <span class="px-2 py-0.5 text-xs rounded-lg bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400">نویسنده تیکت</span>
                            <span class="text-xs text-slate-400">{{ $ticket->created_at->format('Y/m/d H:i') }}</span>
                        </div>
                        <p class="text-sm leading-7 whitespace-pre-line text-slate-600 dark:text-slate-300">{{ $ticket->message }}</p>
                    </div>
                </div>
            </div>

            {{-- پاسخ‌ها (گفتگو) --}}
            @forelse($ticket->replies as $reply)
                <div class="p-6 chart-card">
                    <div class="flex items-start gap-3">
                        @if($reply->is_staff_reply)
                            <span class="flex items-center justify-center shrink-0 h-10 w-10 rounded-xl bg-primary-100 text-primary-600 dark:bg-primary-900/40 dark:text-primary-400 text-sm font-bold">
                                {{ mb_substr($reply->user?->name ?? $reply->admin?->name ?? '؟', 0, 1) }}
                            </span>
                        @else
                            <span class="flex items-center justify-center shrink-0 h-10 w-10 rounded-xl bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300 text-sm font-bold">
                                {{ mb_substr($reply->user?->name ?? $reply->admin?->name ?? '؟', 0, 1) }}
                            </span>
                        @endif
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2 mb-2">
                                <span class="text-sm font-bold">{{ $reply->user?->name ?? $reply->admin?->name ?? 'کاربر حذف‌شده' }}</span>
                                @if($reply->is_staff_reply)
                                    <span class="px-2 py-0.5 text-xs rounded-lg bg-primary-100 text-primary-700 dark:bg-primary-900/40 dark:text-primary-300">پشتیبانی</span>
                                @else
                                    <span class="px-2 py-0.5 text-xs rounded-lg bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400">کاربر</span>
                                @endif
                                <span class="text-xs text-slate-400">{{ $reply->created_at->format('Y/m/d H:i') }}</span>
                            </div>
                            <p class="text-sm leading-7 whitespace-pre-line text-slate-600 dark:text-slate-300">{{ $reply->message }}</p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-6 text-center chart-card">
                    <p class="text-sm text-slate-400">هنوز پاسخی ثبت نشده است — اولین پاسخ را ثبت کنید</p>
                </div>
            @endforelse

            {{-- فرم پاسخ کارشناس --}}
            @can('update', $ticket)
                <div class="p-6 chart-card">
                    <h3 class="mb-4 text-lg font-bold">پاسخ به تیکت</h3>
                    <form wire:submit.prevent="sendReply" class="space-y-3">
                        <div>
                            <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">متن پاسخ <span class="text-red-500">*</span></label>
                            <textarea wire:model.blur="replyMessage" rows="4" required
                                      class="w-full px-3 py-2 text-sm transition border resize-none rounded-xl border-slate-200 bg-slate-50 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white"
                                      placeholder="پاسخ خود را بنویسید... (پس از ثبت، وضعیت تیکت به «در انتظار پاسخ کاربر» تغییر می‌کند)"></textarea>
                            @error('replyMessage') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <button type="submit" wire:loading.attr="disabled" wire:target="sendReply"
                                class="flex items-center gap-2 rounded-xl bg-primary-500 px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-primary-500/25 transition-all hover:shadow-lg hover:shadow-primary-500/35 disabled:opacity-50">
                            <span wire:loading.remove>ثبت پاسخ</span>
                            <span wire:loading class="flex items-center gap-2">
                                <svg class="w-4 h-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                در حال ارسال...
                            </span>
                        </button>
                    </form>
                </div>
            @endcan
        </div>

        {{-- ستون کناری: وضعیت و اطلاعات --}}
        <div class="space-y-6">
            <div class="p-6 chart-card">
                <h3 class="mb-4 text-lg font-bold">وضعیت و اطلاعات</h3>
                <dl class="space-y-3 text-sm">
                    <div class="flex items-center justify-between">
                        <dt class="text-slate-500 dark:text-slate-400">وضعیت</dt>
                        <dd class="px-2.5 py-1 text-xs font-medium rounded-full {{ $ticket->status === 'open' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' : ($ticket->status === 'closed' ? 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400' : 'bg-primary-100 text-primary-700 dark:bg-primary-900/40 dark:text-primary-300') }}">{{ $ticket->status_label }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-slate-500 dark:text-slate-400">اولویت</dt>
                        <dd class="font-medium {{ $ticket->priority === 'urgent' ? 'text-rose-500' : '' }}">{{ $ticket->priority_label }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-slate-500 dark:text-slate-400">کاربر</dt>
                        <dd class="font-medium">{{ $ticket->user?->name ?? '—' }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-slate-500 dark:text-slate-400">تعداد پاسخ‌ها</dt>
                        <dd class="font-medium">{{ $ticket->replies->count() }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-slate-500 dark:text-slate-400">ایجاد</dt>
                        <dd class="font-medium">{{ $ticket->created_at->format('Y/m/d H:i') }}</dd>
                    </div>
                    @if($ticket->category)
                        <div class="flex items-center justify-between">
                            <dt class="text-slate-500 dark:text-slate-400">دسته</dt>
                            <dd class="font-medium">{{ $ticket->category }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            {{-- تغییر وضعیت --}}
            @can('update', $ticket)
                <div class="p-6 chart-card">
                    <h3 class="mb-4 text-lg font-bold">تغییر وضعیت</h3>
                    <form wire:submit.prevent="changeStatus" class="space-y-3">
                        <select wire:model.live="newStatus"
                                class="w-full px-3 py-2 text-sm transition border rounded-xl border-slate-200 bg-slate-50 focus:border-primary-400 focus:outline-none dark:border-slate-600 dark:bg-slate-700/50 dark:text-white">
                            <option value="open">باز</option>
                            <option value="in_progress">در حال بررسی</option>
                            <option value="waiting">در انتظار پاسخ</option>
                            <option value="closed">بسته شده</option>
                        </select>
                        @error('newStatus') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                        <button type="submit" wire:loading.attr="disabled" wire:target="changeStatus"
                                class="w-full rounded-xl bg-slate-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800 disabled:opacity-50 dark:bg-slate-600 dark:hover:bg-slate-500">
                            اعمال وضعیت
                        </button>
                    </form>
                </div>
            @endcan
        </div>
    </div>
</div>
