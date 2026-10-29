<x-public.layout :title="'درگاه پرداخت'">
    <div class="mx-auto max-w-md">
        <div class="glass-card p-8 text-center">
            {{-- شبیه‌ساز درگاه پرداخت (تا پیاده‌سازی کامل درگاه‌های واقعی) --}}
            <span class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-400 to-yellow-500 text-slate-900 shadow-lg shadow-amber-500/30">
                <x-icon name="wallet" size="8" />
            </span>

            <p class="mb-1 text-xs text-slate-400">درگاه پرداخت «{{ $gatewayName }}»</p>
            <h1 class="mb-2 text-2xl font-extrabold text-slate-900 dark:text-white" dir="ltr">
                {{ fa_digits(number_format((float) $payment->amount)) }} <span class="text-sm font-normal">تومان</span>
            </h1>
            <p class="mb-6 text-sm leading-6 text-slate-500 dark:text-slate-400">{{ $payment->description }}</p>

            @if (! empty($gatewayError))
                <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 p-4 text-right text-xs font-bold leading-6 text-rose-600 dark:border-rose-800 dark:bg-rose-950/40 dark:text-rose-400">
                    {{ $gatewayError }}
                    <br>سفارش شما ثبت شده و در انتظار پرداخت مانده است — پس از برقراری اتصال دوباره تلاش کنید.
                </div>
            @endif

            <div class="mb-6 rounded-xl bg-slate-50 p-4 text-right text-xs leading-6 text-slate-500 dark:bg-slate-800/60 dark:text-slate-400">
                این صفحه شبیه‌ساز درگاه پرداخت است.
                با زدن «پرداخت موفق»، پرداخت تأیید و سفارش شما تکمیل می‌شود.
            </div>

            @if ($payment->order)
                <div class="mb-6 flex items-center justify-between rounded-xl border border-slate-200 px-4 py-2.5 text-xs dark:border-slate-700">
                    <span class="text-slate-500 dark:text-slate-400">شماره سفارش</span>
                    <b class="tracking-wider" dir="ltr">{{ $payment->order->order_number }}</b>
                </div>
            @endif

            <form method="POST" action="{{ route('payment.complete', $payment->id) }}" class="mb-3">
                @csrf
                <button type="submit" class="btn-primary w-full !px-6 !py-3 !text-sm">پرداخت موفق</button>
            </form>
            <form method="POST" action="{{ route('payment.fail', $payment->id) }}">
                @csrf
                <button type="submit" class="btn-outline w-full !px-6 !py-3 !text-sm !text-rose-500">انصراف / پرداخت ناموفف</button>
            </form>
        </div>

        @if ($payment->order)
            <a href="{{ route('user.orders.show', $payment->order->order_number) }}"
               class="mt-4 block text-center text-sm text-slate-500 transition hover:text-primary-600 dark:text-slate-400">
                بازگشت به سفارش
            </a>
        @endif
    </div>
</x-public.layout>
