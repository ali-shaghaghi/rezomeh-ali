<div>
    {{-- هدر --}}
    <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
        <div>
            <h2 class="text-2xl font-bold sm:text-3xl">{{ $order->title }}</h2>
            <p class="mt-1 font-mono text-xs text-slate-500 dark:text-slate-400" dir="ltr">{{ $order->order_number }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.orders.index') }}"
               class="px-4 py-2 text-sm font-medium transition border rounded-xl border-slate-200 text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-400 dark:hover:bg-surface-800">
                بازگشت به لیست
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- ستون اصلی --}}
        <div class="space-y-6 lg:col-span-2">
            {{-- اطلاعات سفارش --}}
            <div class="p-6 chart-card">
                <h3 class="mb-4 text-lg font-bold">توضیحات سفارش</h3>
                <p class="text-sm leading-7 whitespace-pre-line text-slate-600 dark:text-slate-300">{{ $order->description ?: 'بدون توضیحات' }}</p>
            </div>

            {{-- تاریخچه پرداخت‌ها --}}
            <div class="p-6 chart-card">
                <h3 class="mb-4 text-lg font-bold">تاریخچه پرداخت‌ها ({{ $order->payments->count() }})</h3>
                <div class="space-y-3">
                    @forelse($order->payments as $payment)
                        <div class="flex flex-wrap items-center justify-between gap-2 p-3 rounded-xl bg-slate-50 dark:bg-slate-700/50">
                            <div>
                                <p class="text-sm font-bold">{{ number_format($payment->amount) }} تومان</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                    {{ $payment->payment_method }} — {{ $payment->paid_at?->format('Y/m/d H:i') ?? $payment->created_at->format('Y/m/d H:i') }}
                                    @if($payment->transaction_id) — <span dir="ltr">{{ $payment->transaction_id }}</span> @endif
                                </p>
                            </div>
                            <span class="px-2.5 py-1 text-xs font-medium rounded-full {{ $payment->status === 'completed' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300' }}">
                                {{ $payment->status_label }}
                            </span>
                        </div>
                    @empty
                        <p class="text-sm text-slate-400">هنوز پرداختی ثبت نشده است</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- ستون کناری --}}
        <div class="space-y-6">
            {{-- وضعیت --}}
            <div class="p-6 chart-card">
                <h3 class="mb-4 text-lg font-bold">وضعیت و اطلاعات</h3>
                <dl class="space-y-3 text-sm">
                    <div class="flex items-center justify-between">
                        <dt class="text-slate-500 dark:text-slate-400">وضعیت</dt>
                        <dd class="px-2.5 py-1 text-xs font-medium rounded-full {{ $order->status === 'completed' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-primary-100 text-primary-700 dark:bg-primary-900/40 dark:text-primary-300' }}">{{ $order->status_label }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-slate-500 dark:text-slate-400">اولویت</dt>
                        <dd class="font-medium">{{ $order->priority_label }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-slate-500 dark:text-slate-400">کاربر</dt>
                        <dd class="font-medium">{{ $order->user?->name ?? '—' }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-slate-500 dark:text-slate-400">ددلاین</dt>
                        <dd class="font-medium">{{ $order->deadline?->format('Y/m/d') ?? '—' }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-slate-500 dark:text-slate-400">ثبت سفارش</dt>
                        <dd class="font-medium">{{ $order->created_at->format('Y/m/d') }}</dd>
                    </div>
                </dl>
            </div>

            {{-- مالی --}}
            <div class="p-6 chart-card">
                <h3 class="mb-4 text-lg font-bold">اطلاعات مالی</h3>
                <dl class="space-y-3 text-sm">
                    <div class="flex items-center justify-between">
                        <dt class="text-slate-500 dark:text-slate-400">مبلغ کل</dt>
                        <dd class="font-bold">{{ number_format($order->amount) }} تومان</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-slate-500 dark:text-slate-400">پرداخت‌شده</dt>
                        <dd class="font-bold text-emerald-600 dark:text-emerald-400">{{ number_format($order->paid_amount) }} تومان</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-slate-500 dark:text-slate-400">باقیمانده</dt>
                        <dd class="font-bold {{ $order->is_paid ? 'text-emerald-600' : 'text-rose-500' }}">
                            {{ number_format(max(0, $order->amount - $order->paid_amount)) }} تومان
                        </dd>
                    </div>
                </dl>
                @if((float) $order->amount > 0)
                    <div class="mt-4">
                        <div class="w-full h-2 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-700">
                            <div class="h-full transition-all duration-500 rounded-full bg-gradient-to-l from-emerald-500 to-primary-500"
                                 style="width: {{ min(100, (float) $order->paid_amount / max(1, (float) $order->amount) * 100) }}%"></div>
                        </div>
                        <p class="mt-1 text-xs text-center text-slate-400">{{ $order->is_paid ? 'تسویه کامل' : round((float) $order->paid_amount / max(1, (float) $order->amount) * 100) . '٪ پرداخت شده' }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
