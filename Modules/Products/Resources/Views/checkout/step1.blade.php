<x-public.layout :title="__('shop.checkout_title')">
    <div class="space-y-8">
        <div class="animate-fade-in">
            <span class="section-badge">{{ __('shop.steps.badge') }}</span>
            <h1 class="section-title">{{ __('shop.steps.step_of', ['step' => fa_digits(1), 'title' => __('shop.steps.review')]) }}</h1>
            <p class="section-subtitle mx-auto">{{ __('shop.steps.review_desc') }}</p>
        </div>

        @livewire('checkout.step1-order')
    </div>
</x-public.layout>
