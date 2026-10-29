<div>
    {{-- آمار کلی --}}
    <div class="mb-5 grid grid-cols-2 gap-3 sm:grid-cols-4">
        <div class="glass-card p-4 text-center">
            <p class="text-[11px] text-slate-500 dark:text-slate-400">کل ارسالی‌ها</p>
            <p class="mt-1 text-xl font-black text-slate-900 dark:text-white">{{ fa_digits($totalSent + $totalFailed) }}</p>
        </div>
        <div class="glass-card p-4 text-center">
            <p class="text-[11px] text-slate-500 dark:text-slate-400">موفق</p>
            <p class="mt-1 text-xl font-black text-emerald-500">{{ fa_digits($totalSent) }}</p>
        </div>
        <div class="glass-card p-4 text-center">
            <p class="text-[11px] text-slate-500 dark:text-slate-400">ناموفق</p>
            <p class="mt-1 text-xl font-black text-rose-500">{{ fa_digits($totalFailed) }}</p>
        </div>
    </div>

    {{-- فیلترها --}}
    <div class="glass-card mb-5 grid gap-3 p-4 sm:grid-cols-3">
        <input type="search" wire:model.live.debounce.300ms="search" placeholder="جستجوی شماره یا متن..."
               class="w-full rounded-xl border border-slate-200 bg-white/80 px-4 py-2.5 text-sm text-slate-800 outline-none focus:border-primary-400 focus:ring-2 focus:ring-primary-400/20 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-100">
        <select wire:model.live="status"
                class="w-full rounded-xl border border-slate-200 bg-white/80 px-4 py-2.5 text-sm text-slate-800 outline-none focus:border-primary-400 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-100">
            <option value="">همه وضعیت‌ها</option>
            <option value="sent">موفق</option>
            <option value="failed">ناموفق</option>
        </select>
        <select wire:model.live="provider"
                class="w-full rounded-xl border border-slate-200 bg-white/80 px-4 py-2.5 text-sm text-slate-800 outline-none focus:border-primary-400 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-100">
            <option value="">همه درایورها</option>
            @foreach (\Modules\SMS\Services\SmsService::DRIVERS as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </select>
    </div>

    {{-- جدول لاگ --}}
    <div class="glass-card overflow-x-auto p-0">
        <table class="w-full text-right text-sm">
            <thead>
                <tr class="border-b border-slate-200 text-xs text-slate-500 dark:border-slate-700 dark:text-slate-400">
                    <th class="p-4 font-medium">شماره</th>
                    <th class="p-4 font-medium">متن</th>
                    <th class="p-4 font-medium">درایور</th>
                    <th class="p-4 font-medium">وضعیت</th>
                    <th class="p-4 font-medium">کاربر</th>
                    <th class="p-4 font-medium">زمان</th>
                    <th class="p-4 font-medium">عملیات</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($logs as $log)
                    <tr class="border-b border-slate-100 last:border-0 dark:border-slate-800" wire:key="smslog-{{ $log->id }}">
                        <td class="p-4 font-mono text-xs" dir="ltr">{{ $log->phone }}</td>
                        <td class="max-w-xs truncate p-4 text-slate-600 dark:text-slate-300">{{ $log->message }}</td>
                        <td class="p-4 text-xs">{{ \Modules\SMS\Services\SmsService::DRIVERS[$log->provider] ?? $log->provider }}</td>
                        <td class="p-4">
                            <span class="rounded-md px-2 py-0.5 text-xs font-semibold {{ $log->status === 'sent' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400' : 'bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-400' }}">
                                {{ $log->status === 'sent' ? 'موفق' : 'ناموفق' }}
                            </span>
                        </td>
                        <td class="p-4 text-xs text-slate-500">{{ $log->user?->name ?? '—' }}</td>
                        <td class="p-4 text-xs text-slate-500">{{ $log->sent_at ? jdate('Y/m/d H:i', $log->sent_at) : '—' }}</td>
                        <td class="p-4">
                            <button type="button" wire:click="viewLog({{ $log->id }})"
                                    class="btn-outline !px-3 !py-1.5 !text-xs">
                                مشاهده
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="p-10 text-center text-sm text-slate-500 dark:text-slate-400">هنوز پیامکی ارسال نشده است.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $logs->links() }}</div>

    {{-- مشاهده کامل لاگ — مودال ریل‌تایم (wire:poll) بدون لود صفحه --}}
    @if ($viewLog)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" wire:key="viewlog-modal-{{ $viewLog->id }}">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" wire:click="closeView"></div>
            <div class="glass-card relative max-h-[85vh] w-full max-w-2xl overflow-y-auto p-6" wire:poll.5s>
                <div class="mb-5 flex items-center justify-between gap-3">
                    <h3 class="font-bold text-slate-900 dark:text-white">مشاهده کامل لاگ پیامک</h3>
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] text-slate-400">به‌روزرسانی خودکار هر ۵ ثانیه</span>
                        <button type="button" wire:click="closeView" class="btn-outline !px-3 !py-1.5 !text-xs">بستن</button>
                    </div>
                </div>

                <div class="space-y-4 text-sm">
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                        <div class="rounded-xl bg-slate-50 p-3 dark:bg-slate-800/60">
                            <p class="text-[11px] text-slate-500">شماره مقصد</p>
                            <p class="mt-0.5 font-mono text-xs font-bold" dir="ltr">{{ $viewLog->phone }}</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 p-3 dark:bg-slate-800/60">
                            <p class="text-[11px] text-slate-500">وضعیت</p>
                            <p class="mt-0.5 text-xs font-bold {{ $viewLog->status === 'sent' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-500' }}">
                                {{ $viewLog->status === 'sent' ? 'موفق' : 'ناموفق' }}
                            </p>
                        </div>
                        <div class="rounded-xl bg-slate-50 p-3 dark:bg-slate-800/60">
                            <p class="text-[11px] text-slate-500">درایور</p>
                            <p class="mt-0.5 text-xs font-bold">{{ \Modules\SMS\Services\SmsService::DRIVERS[$viewLog->provider] ?? $viewLog->provider }}</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 p-3 dark:bg-slate-800/60">
                            <p class="text-[11px] text-slate-500">کاربر</p>
                            <p class="mt-0.5 text-xs font-bold">{{ $viewLog->user?->name ?? '—' }}</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 p-3 dark:bg-slate-800/60">
                            <p class="text-[11px] text-slate-500">زمان ارسال (شمسی)</p>
                            <p class="mt-0.5 text-xs font-bold">{{ $viewLog->sent_at ? jdate('Y/m/d H:i', $viewLog->sent_at) : '—' }}</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 p-3 dark:bg-slate-800/60">
                            <p class="text-[11px] text-slate-500">هزینه (ریال)</p>
                            <p class="mt-0.5 text-xs font-bold">{{ fa_digits(number_format((float) $viewLog->cost, 4)) }}</p>
                        </div>
                    </div>

                    <div>
                        <p class="mb-1 text-xs text-slate-500">متن کامل پیامک</p>
                        <p class="whitespace-pre-wrap rounded-xl bg-slate-50 p-3 text-slate-700 dark:bg-slate-800/60 dark:text-slate-200">{{ $viewLog->message }}</p>
                    </div>

                    <div>
                        <p class="mb-1 text-xs text-slate-500">پاسخ خام ارائه‌دهنده (JSON)</p>
                        <pre dir="ltr" class="max-h-52 overflow-auto whitespace-pre-wrap rounded-xl bg-slate-900 p-3 text-left text-[11px] leading-relaxed text-emerald-300">{{ $viewLog->response ?: '—' }}</pre>
                    </div>

                    <div class="grid grid-cols-2 gap-3 text-[11px] text-slate-500">
                        <span>شناسه لاگ: <b>{{ $viewLog->id }}</b></span>
                        <span>ثبت: <b>{{ jdate('Y/m/d H:i', $viewLog->created_at) }}</b></span>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>