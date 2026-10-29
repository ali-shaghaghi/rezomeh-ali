<x-public.layout :title="__('shop.checkout_title')">
    <div class="space-y-8">
        <div class="animate-fade-in">
            <span class="section-badge">{{ __('shop.steps.badge') }}</span>
            <h1 class="section-title">{{ __('shop.steps.step_of', ['step' => fa_digits(4), 'title' => __('shop.steps.payment')]) }}</h1>
            <p class="section-subtitle mx-auto">{{ __('shop.steps.payment_desc') }}</p>
        </div>

        @livewire('checkout.step4-payment')
    </div>
</x-public.layout>
