<x-public.layout :title="'درگاه پرداخت'">
    <div class="max-w-md mx-auto">
        <div class="glass-card p-8 text-center">
            {{-- شبیه‌ساز درگاه پرداخت (تا پیاده‌سازی فاز ۱۳) --}}
            <span class="mx-auto mb-4 flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-amber-400 to-yellow-500 text-slate-900 shadow-lg shadow-amber-500/30">
                <x-icon name="wallet" size="8" />
            </span>

            <p class="text-xs text-slate-400 mb-1">درگاه پرداخت «{{ $gatewayName }}»</p>
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white mb-2">{{ fa_digits(number_format((float) $payment->amount)) }} تومان</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-6 leading-6">{{ $payment->description }}</p>

            <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/60 text-right text-xs text-slate-500 dark:text-slate-400 mb-6 leading-6">
                این صفحه شبیه‌ساز درگاه پرداخت است (اتصال واقعی زرین‌پال/آی‌دی‌پی در فاز ۱۳ پیاده می‌شود).
                با زدن «پرداخت موفق»، پرداخت تایید و قسط بسته می‌شود.
            </div>

            <form method="POST" action="{{ route('user.projectdev.payment.complete', $payment->id) }}" class="mb-3">
                @csrf
                <button type="submit" class="btn-primary !px-6 !py-3 !text-sm w-full">پرداخت موفق</button>
            </form>
            <form method="POST" action="{{ route('user.projectdev.payment.failed', $payment->id) }}">
                @csrf
                <button type="submit" class="btn-outline !px-6 !py-3 !text-sm w-full !text-rose-500">انصراف / پرداخت ناموفق</button>
            </form>
        </div>

        <a href="{{ route('user.orders.show', $payment->order->order_number) }}" class="mt-4 block text-center text-sm text-slate-500 dark:text-slate-400 hover:text-primary-600 transition">
            بازگشت به سفارش
        </a>
    </div>
</x-public.layout>
