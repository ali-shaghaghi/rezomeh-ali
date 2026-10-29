<div>
    {{-- فیلترها --}}
    <div class="glass-card mb-5 grid gap-3 p-4 sm:grid-cols-4">
        <div>
            <label class="mb-1 block text-[11px] text-slate-500 dark:text-slate-400">از تاریخ</label>
            <input type="date" wire:model.live.debounce.500ms="fromDate"
                   class="w-full rounded-xl border border-slate-200 bg-white/80 px-4 py-2.5 text-sm text-slate-800 outline-none focus:border-primary-400 focus:ring-2 focus:ring-primary-400/20 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-100">
        </div>
        <div>
            <label class="mb-1 block text-[11px] text-slate-500 dark:text-slate-400">تا تاریخ</label>
            <input type="date" wire:model.live.debounce.500ms="toDate"
                   class="w-full rounded-xl border border-slate-200 bg-white/80 px-4 py-2.5 text-sm text-slate-800 outline-none focus:border-primary-400 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-100">
        </div>
        <div>
            <label class="mb-1 block text-[11px] text-slate-500 dark:text-slate-400">روش پرداخت</label>
            <select wire:model.live="method"
                    class="w-full rounded-xl border border-slate-200 bg-white/80 px-4 py-2.5 text-sm text-slate-800 outline-none focus:border-primary-400 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-100">
                <option value="">همه روش‌ها</option>
                @foreach ($methods as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end">
            <button type="button" wire:click="resetFilters" class="btn-outline w-full !px-4 !py-2.5 !text-xs">پاک کردن فیلترها</button>
        </div>
    </div>

    {{-- جمع کل --}}
    <div class="mb-5 grid grid-cols-2 gap-3 sm:grid-cols-3">
        <div class="glass-card p-4 text-center">
            <p class="text-[11px] text-slate-500 dark:text-slate-400">تعداد تراکنش‌ها</p>
            <p class="mt-1 text-xl font-black text-slate-900 dark:text-white">{{ fa_digits($payments->count()) }}</p>
        </div>
        <div class="glass-card p-4 text-center">
            <p class="text-[11px] text-slate-500 dark:text-slate-400">جمع مبلغ</p>
            <p class="mt-1 text-xl font-black text-emerald-500">{{ fa_digits(number_format($totalAmount)) }}<span class="text-[11px] text-slate-400"> تومان</span></p>
        </div>
        <div class="glass-card p-4 text-center col-span-2 sm:col-span-1">
            <p class="text-[11px] text-slate-500 dark:text-slate-400">میانگین تراکنش</p>
            <p class="mt-1 text-xl font-black text-primary-500">{{ fa_digits(number_format($payments->count() ? $totalAmount / $payments->count() : 0)) }}<span class="text-[11px] text-slate-400"> تومان</span></p>
        </div>
    </div>

    {{-- پرداخت‌ها — کارت‌های موبایل‌پسند --}}
    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
        @forelse ($payments as $payment)
            <div class="glass-card glass-card-hover p-4" wire:key="payment-{{ $payment->id }}">
                <div class="flex items-start justify-between gap-2">
                    <div class="flex min-w-0 items-center gap-2.5">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-primary-100 text-xs font-bold text-primary-600 dark:bg-primary-900/40 dark:text-primary-400">
                            {{ mb_substr($payment->user?->name ?? '؟', 0, 1) }}
                        </span>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-bold text-slate-800 dark:text-slate-100">{{ $payment->user?->name ?? '—' }}</p>
                            <p class="text-[10px] text-slate-400" dir="ltr">{{ $payment->order?->order_number ?? '—' }}</p>
                        </div>
                    </div>
                    <span class="shrink-0 rounded-md bg-primary-100 px-2 py-0.5 text-[10px] font-semibold text-primary-600 dark:bg-primary-900/50 dark:text-primary-400">
                        {{ $methods[$payment->payment_method] ?? $payment->payment_method }}
                    </span>
                </div>
                <div class="mt-3 flex items-end justify-between border-t border-slate-100 pt-3 dark:border-slate-800">
                    <p class="text-lg font-black text-emerald-600 dark:text-emerald-400" dir="ltr">
                        {{ fa_digits(number_format($payment->amount)) }}
                        <span class="text-[10px] font-normal">تومان</span>
                    </p>
                    <p class="text-[10px] text-slate-400">{{ $payment->paid_at?->format('Y/m/d H:i') ?? '—' }}</p>
                </div>
            </div>
        @empty
            <div class="glass-card col-span-full p-10 text-center text-sm text-slate-500 dark:text-slate-400">در این بازه پرداختی ثبت نشده است.</div>
        @endforelse
    </div>

    {{-- محصولات پرفروش — کارت‌های موبایل‌پسند --}}
    <h3 class="mb-4 mt-8 text-lg font-bold text-slate-900 dark:text-white">محصولات پرفروش</h3>
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @forelse ($topProducts as $index => $product)
            <div class="glass-card glass-card-hover flex items-center gap-4 p-5" wire:key="topproduct-{{ $index }}">
                {{-- رتبه --}}
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br text-lg font-black text-white shadow-lg {{ ['from-amber-400 to-yellow-500 shadow-amber-500/30', 'from-slate-300 to-slate-400 shadow-slate-400/30', 'from-orange-400 to-amber-600 shadow-orange-500/30'][$index] ?? 'from-primary-400 to-primary-600 shadow-primary-500/30' }}">
                    {{ fa_digits($index + 1) }}
                </span>

                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-bold text-slate-800 dark:text-slate-100">{{ $product->product_name }}</p>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                        <span class="font-bold text-primary-600 dark:text-primary-400">{{ fa_digits($product->quantity) }}</span> عدد فروش
                    </p>
                </div>

                <p class="shrink-0 text-sm font-black text-emerald-600 dark:text-emerald-400" dir="ltr">
                    {{ fa_digits(number_format($product->revenue)) }}
                    <span class="text-[10px] font-normal">ت</span>
                </p>
            </div>
        @empty
            <div class="glass-card col-span-full p-10 text-center text-sm text-slate-500 dark:text-slate-400">هنوز محصولی فروخته نشده است.</div>
        @endforelse
    </div>
</div>
