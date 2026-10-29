@php
    // کشو با left-0 لنگر شده است → حالت بسته باید به سمت چپ از صفحه خارج شود.
    // translate-x-full (مثبت) آن را ۱۰۰٪ عرض خودش به راست می‌برد و چون max-w-md
    // است، در وسط صفحه می‌نشیند و دیده می‌شود!
    $openClass = $open ? 'translate-x-0' : '-translate-x-full';
@endphp

<div class="contents">
    {{-- Overlay --}}
    <div class="fixed inset-0 z-[70] bg-slate-900/50 backdrop-blur-sm transition-opacity duration-300 {{ $open ? 'opacity-100' : 'opacity-0 pointer-events-none' }}"
         wire:click="closeDrawer" aria-hidden="true"></div>

    {{-- کشوی سبد — از سمت چپ باز می‌شود (RTL) --}}
    <aside class="cart-drawer fixed inset-y-0 left-0 z-[80] flex w-full max-w-md flex-col bg-white shadow-2xl transition-transform duration-500 ease-out dark:bg-surface-900 {{ $openClass }}"
           role="dialog" aria-label="سبد خرید" aria-modal="true">

    {{-- هدر کشو --}}
    <div class="flex items-center justify-between border-b border-slate-200 p-5 dark:border-slate-800">
        <h2 class="flex items-center gap-2 text-lg font-bold">
            <x-icon name="cart" size="5" />
            سبد خرید
            @if ($count > 0)
                <span class="rounded-full bg-primary-100 px-2.5 py-0.5 text-xs font-bold text-primary-600 dark:bg-primary-950/60 dark:text-primary-400">
                    {{ fa_digits($count) }}
                </span>
            @endif
        </h2>
        <button type="button" wire:click="closeDrawer"
                class="rounded-xl p-2 text-slate-500 transition hover:bg-slate-100 dark:hover:bg-slate-800"
                aria-label="بستن سبد خرید">
            <x-icon name="close" />
        </button>
    </div>

    @if ($errorMessage)
        <div class="m-4 rounded-xl border border-rose-200 bg-rose-50 p-3 text-xs font-bold text-rose-600 dark:border-rose-800 dark:bg-rose-950/40 dark:text-rose-400">
            {{ $errorMessage }}
        </div>
    @endif

    @if ($count === 0)
        {{-- سبد خالی --}}
        <div class="flex flex-1 flex-col items-center justify-center gap-4 p-10 text-center">
            <span class="flex h-20 w-20 items-center justify-center rounded-full bg-slate-100 text-slate-400 dark:bg-slate-800">
                <x-icon name="cart" size="10" />
            </span>
            <div>
                <h3 class="font-bold text-slate-900 dark:text-white">سبد خرید شما خالی است</h3>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">برای شروع، محصولات را از فروشگاه انتخاب کنید.</p>
            </div>
            <button type="button" wire:click="browseProducts" class="btn-primary !px-5 !py-2.5 !text-xs">
                <x-icon name="products" size="4" />
                مشاهده محصولات
            </button>
        </div>
    @else
        @include('products::livewire.public.cart-drawer-items')
    @endif
    </aside>
</div>
