<div>
    <article class="scroll-mt-28">
        {{-- هدر --}}
        <header class="mb-8">
            <a href="{{ url('/dashboard?tab=orders') }}" wire:navigate class="inline-flex items-center gap-1.5 text-sm text-slate-500 dark:text-slate-400 hover:text-primary-600 dark:hover:text-primary-400 transition mb-5">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                بازگشت به سفارش‌ها
            </a>

            <div class="flex flex-wrap items-center gap-2 mb-4">
                <span class="text-sm font-mono font-bold px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400" dir="ltr">{{ $order->order_number }}</span>
                <span class="text-sm px-3 py-1.5 rounded-xl bg-primary-50 dark:bg-primary-950/50 text-primary-600 dark:text-primary-400 font-bold">{{ $order->status_label }}</span>
                @if ($order->is_paid)
                    <span class="text-sm px-3 py-1.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 font-bold">تسویه‌شده</span>
                @endif
            </div>

            <h1 class="mb-4 text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ $order->title }}</h1>

            <div class="flex flex-wrap items-center gap-4 text-sm text-slate-500 dark:text-slate-400">
                @if ($order->created_at)
                    <span class="flex items-center gap-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        ثبت: {{ fa_digits($order->created_at->format('Y/m/d')) }}
                    </span>
                @endif
                @if ($order->deadline)
                    <span class="flex items-center gap-1.5 {{ $order->deadline_status === 'overdue' ? 'text-rose-500' : ($order->deadline_status === 'urgent' ? 'text-amber-500' : '') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        ددلاین: {{ fa_digits($order->deadline->format('Y/m/d')) }}
                    </span>
                @endif
            </div>
        </header>

        <div class="grid gap-6 lg:grid-cols-1">
            {{-- توضیحات + مراحل --}}
            <div class="space-y-6">
                <div class="p-6 glass-card sm:p-8">
                    <h2 class="mb-4 font-bold text-slate-900 dark:text-white">توضیحات پروژه</h2>
                    <p class="text-slate-600 dark:text-slate-300 text-sm leading-[2] whitespace-pre-line">{{ $order->description }}</p>
                </div>

                {{-- محصولات خریداری‌شده + لایسنس‌ها (نمایش بلافاصله بعد از خرید) --}}
                @if ($order->items->count())
                    <div class="p-6 glass-card sm:p-8">
                        <div class="flex flex-wrap items-center justify-between gap-2 mb-5">
                            <h2 class="font-bold text-slate-900 dark:text-white">لایسنس‌های خریداری‌شده</h2>
                            <a href="{{ route('user.products.index') }}" class="text-xs font-bold text-primary-600 dark:text-primary-400 hover:underline">
                                مشاهده همه در «محصولات من» ←
                            </a>
                        </div>

                        <div class="space-y-5">
                            @foreach ($order->items as $item)
                                <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/60" wire:key="order-item-{{ $item->id }}">
                                    <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                                        <p class="text-sm font-bold text-slate-800 dark:text-slate-200">{{ $item->product?->name ?? $item->product_name }}</p>
                                        <span class="text-xs px-2 py-0.5 rounded-md bg-primary-50 dark:bg-primary-950/50 text-primary-600 dark:text-primary-400 font-semibold">
                                            {{ \Modules\Products\Enums\LicenseType::tryFrom($item->license_type)?->label() ?? $item->license_type }}
                                            · {{ fa_digits($item->quantity) }} عدد · {{ fa_digits(number_format((float) $item->total_price)) }} تومان
                                        </span>
                                    </div>

                                    <div class="space-y-2.5">
                                        @foreach ($item->licenses as $license)
                                            <div class="flex flex-wrap items-center gap-2 p-3 rounded-xl border border-slate-200 bg-white/70 dark:border-slate-700 dark:bg-slate-900/40" wire:key="order-license-{{ $license->id }}">
                                                <div class="min-w-0 flex-1">
                                                    <code class="block truncate font-mono text-xs text-slate-700 dark:text-slate-200" dir="ltr">{{ $license->license_key }}</code>
                                                    <p class="mt-1 text-[11px] text-slate-400">
                                                        فعال‌سازی: {{ fa_digits($license->activations_count) }} از {{ fa_digits($license->max_activations) }}
                                                        @if ($license->expires_at)
                                                            · انقضا: {{ fa_digits($license->expires_at->format('Y/m/d')) }}
                                                        @else
                                                            · مادام‌العمر
                                                        @endif
                                                    </p>
                                                </div>

                                                @if ($license->is_valid)
                                                    <span class="shrink-0 text-[11px] px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400 font-bold">فعال</span>
                                                @else
                                                    <span class="shrink-0 text-[11px] px-2 py-0.5 rounded-md bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-400 font-bold">منقضی</span>
                                                @endif

                                                <button type="button"
                                                        x-data="{ copied: false }"
                                                        x-on:click="navigator.clipboard.writeText('{{ $license->license_key }}'); copied = true; setTimeout(() => copied = false, 2000)"
                                                        class="btn-outline !px-3 !py-1.5 !text-[11px] shrink-0">
                                                    <span x-show="!copied">کپی</span>
                                                    <span x-show="copied" x-cloak class="text-emerald-600 dark:text-emerald-400">✓ کپی شد</span>
                                                </button>

                                                @if ($license->download_url)
                                                    <a href="{{ $license->download_url }}" class="btn-primary !px-3 !py-1.5 !text-[11px] shrink-0">دانلود</a>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- تسک‌ها --}}
                @if ($order->tasks->count())
                    <div class="p-6 glass-card sm:p-8">
                        <div class="flex items-center justify-between mb-5">
                            <h2 class="font-bold text-slate-900 dark:text-white">مراحل پروژه</h2>
                            <span class="text-xs font-bold text-primary-600 dark:text-primary-400">{{ fa_digits($order->tasks->count()) }} مرحله</span>
                        </div>
                        <div class="space-y-4">
                            @foreach ($order->tasks as $task)
                                @php($taskProgress = (int) min(100, max(0, (int) $task->progress)))
                                <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/60" wire:key="task-{{ $task->id }}">
                                    <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                                        <p class="text-sm font-bold text-slate-800 dark:text-slate-200">{{ $task->title }}</p>
                                        <span class="text-xs px-2 py-0.5 rounded-md bg-primary-50 dark:bg-primary-950/50 text-primary-600 dark:text-primary-400 font-semibold">{{ fa_digits($taskProgress) }}٪</span>
                                    </div>
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="--progress:{{ $taskProgress }}%; width: {{ $taskProgress }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- مالی: اقساط + پرداخت‌ها + پشتیبانی --}}
            <div class="space-y-6">
                @php($openInstallments = $order->installments->where('status', '!=', 'paid')->sortBy('due_date'))
                @if ($openInstallments->count())
                    <div class="p-6 glass-card">
                        <h2 class="mb-4 font-bold text-slate-900 dark:text-white">اقساط باز</h2>
                        <div class="space-y-3">
                            @foreach ($openInstallments as $installment)
                                <div class="flex flex-wrap items-center justify-between gap-3 p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60" wire:key="installment-{{ $installment->id }}">
                                    <div class="min-w-0">
                                        <p class="text-sm font-bold text-slate-800 dark:text-slate-200">
                                            {{ $installment->title }}
                                            @if ($installment->due_date)
                                                <span class="text-xs font-medium text-slate-400">— سررسید: {{ fa_digits($installment->due_date->format('Y/m/d')) }}</span>
                                            @endif
                                        </p>
                                        <p class="text-xs mt-0.5 text-rose-500 font-bold">
                                            پرداخت نشده — {{ fa_digits(number_format((float) $installment->amount)) }} تومان
                                        </p>
                                    </div>
                                    <a href="{{ route('user.projectdev.payment.pay', $installment->id) }}" class="btn-primary !px-4 !py-2 !text-xs shrink-0">
                                        <x-icon name="wallet" size="3.5" />
                                        پرداخت
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- پرداخت‌ها --}}
                @if ($order->payments->count())
                    <div class="p-6 glass-card">
                        <h2 class="mb-4 font-bold text-slate-900 dark:text-white">تاریخچه پرداخت‌ها</h2>
                        <div class="space-y-3">
                            @foreach ($order->payments->sortByDesc('paid_at') as $payment)
                                <div class="flex items-center justify-between gap-2 p-3 text-sm rounded-xl bg-slate-50 dark:bg-slate-800/60" wire:key="payment-{{ $payment->id }}">
                                    <div class="min-w-0">
                                        <p class="font-bold text-slate-800 dark:text-slate-200">{{ fa_digits(number_format((float) $payment->amount)) }} تومان</p>
                                        <p class="text-xs text-slate-400 mt-0.5">{{ fa_digits($payment->paid_at?->format('Y/m/d') ?? '—') }}</p>
                                    </div>
                                    <span class="shrink-0 text-xs px-2 py-0.5 rounded-md {{ $payment->status === 'completed' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400' : ($payment->status === 'refunded' ? 'bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-400') }} font-semibold">{{ $payment->status_label }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- سوال؟ --}}
                <div class="p-6 text-center glass-card">
                    <p class="mb-4 text-sm text-slate-500 dark:text-slate-400">درباره این سفارش سؤالی داری؟</p>
                    <a href="{{ url('/dashboard?tab=tickets') }}" wire:navigate class="btn-outline !px-5 !py-2.5 !text-xs w-full">ارسال تیکت</a>
                </div>
            </div>
        </div>
    </article>
</div>
