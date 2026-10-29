<x-public.layout :title="__('shop.title')">
    <div class="space-y-8">
        {{-- هدر --}}
        <div class="text-center animate-fade-in">
            <span class="section-badge">{{ __('shop.badge') }}</span>
            <h1 class="section-title">{{ __('shop.title') }}</h1>
            <p class="section-subtitle mx-auto">{{ __('shop.subtitle') }}</p>
        </div>

        @livewire('public.product-grid')
    </div>
</x-public.layout>
