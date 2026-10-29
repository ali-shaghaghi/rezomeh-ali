<x-public.layout :title="__('consultation.title')">
    {{-- ===== هدر ===== --}}
    <section class="mb-10">
        <div class="reveal text-center">
            <div class="section-badge">{{ __('consultation.badge') }}</div>
            <h1 class="section-title">{{ __('consultation.title') }}</h1>
            {{-- mx-auto: بلوک max-w-xl را در کانتینر text-center وسط‌چین می‌کند --}}
            <p class="section-subtitle mx-auto">{{ __('consultation.subtitle') }}</p>
        </div>
    </section>

    {{-- ===== ویزارد رزرو ===== --}}
    <livewire:consultation.booking-wizard />
</x-public.layout>