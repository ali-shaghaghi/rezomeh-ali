@php
    $steps = [
        1 => [__('shop.steps.review'), 'products'],
        2 => [__('shop.steps.personal'), 'users'],
        3 => [__('shop.steps.invoice'), 'wallet'],
        4 => [__('shop.steps.payment'), 'shield'],
    ];
    $labels = [1 => __('shop.steps.review_short'), 2 => __('shop.steps.personal_short'), 3 => __('shop.steps.invoice_short'), 4 => __('shop.steps.payment_short')];
@endphp

<div class="mb-10 animate-slide-up" wire:key="checkout-stepper-{{ $step }}">
    <div class="flex items-center justify-between gap-1">
        @foreach ($steps as $number => $stepDef)
            @php
                $isActive = $step === $number;
                $isDone = $step > $number;
            @endphp
            <div class="flex flex-1 items-center {{ $number < 4 ? 'flex-1' : '' }}">
                <button type="button"
                        @if ($number < $step) wire:click="prevStep" @endif
                        class="checkout-step-btn flex h-11 w-11 shrink-0 items-center justify-center rounded-full text-sm font-bold transition-all duration-300 {{ $isActive ? 'checkout-step-active' : ($isDone ? 'checkout-step-done' : 'checkout-step-idle') }}"
                        @if ($number >= $step) disabled @endif
                        aria-current="{{ $isActive ? 'step' : 'false' }}">
                    @if ($isDone)
                        <x-icon name="check" size="5" />
                    @else
                        {{ fa_digits($number) }}
                    @endif
                </button>
                <span class="mr-2 hidden text-xs font-medium transition sm:inline {{ $isActive ? 'font-bold text-primary-600 dark:text-primary-400' : 'text-slate-500 dark:text-slate-400' }}">
                    {{ $labels[$number] }}
                </span>
                @if ($number < 4)
                    <div class="checkout-step-line mx-2 h-1 flex-1 rounded-full transition-all duration-500 {{ $isDone ? 'bg-primary-500' : 'bg-slate-200 dark:bg-slate-700' }}"></div>
                @endif
            </div>
        @endforeach
    </div>
</div>
