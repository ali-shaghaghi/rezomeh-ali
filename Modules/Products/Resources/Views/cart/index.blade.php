<x-public.layout :title="__('shop.cart_title')">
    <div class="space-y-8">
        <div class="animate-fade-in">
            <span class="section-badge">{{ __('shop.cart_badge') }}</span>
            <h1 class="section-title">{{ __('shop.cart_heading') }}</h1>
            <p class="section-subtitle !mb-0">{{ __('shop.cart_subtitle') }}</p>
        </div>

        {{-- سبد خرید به‌صورت صفحه‌ای — همان داده‌های کشوی سبد --}}
        @livewire('public.cart-page', key('cart-page'))
    </div>
</x-public.layout>
