<div class="flex flex-col items-end gap-2" x-data="{ added: false }" @product-added-to-cart="added = true; setTimeout(() => added = false, 2000)">
    @if ($layout === 'card')
        <button type="button" wire:click="addToCart" wire:loading.attr="disabled" wire:target="addToCart"
                class="btn-primary"
                :class="{ '!bg-emerald-500 hover:!bg-emerald-500': added }">
            <x-icon name="cart" size="3.5" wire:loading.remove wire:target="addToCart" />
            
            <span wire:loading wire:target="addToCart">{{ __('shop.adding') }}</span>
            <span x-show="added" x-cloak>{{ __('shop.added') }}</span>
        </button>
    @else
        {{-- layout: page (نمایش محصول) --}}
        <div class="flex flex-wrap items-center gap-3 pb-2">
            <select wire:model.live="licenseType"
                    class="px-4 py-3 text-sm transition border rounded-xl border-slate-200 bg-white/70 focus:border-primary-400 focus:outline-none dark:border-slate-700 dark:bg-slate-800/60">
                @foreach ($licenseTypes as $type)
                    <option value="{{ $type->value }}">{{ $type->localizedLabel() }} — {{ format_price_for_locale($product->priceForLicense($type->value)) }}</option>
                @endforeach
            </select>

            <button type="button" wire:click="addToCart" wire:loading.attr="disabled" wire:target="addToCart"
                    class="btn-primary !px-6 !py-3"
                    :class="{ '!bg-emerald-500 hover:!bg-emerald-500': added }">
                <x-icon name="cart" size="4" />
                <span>{{ __('shop.add_to_cart') }}</span>
                <span wire:loading wire:target="addToCart" class="text-xs">{{ __('shop.please_wait') }}</span>
                <span x-show="added" x-cloak>{{ __('shop.added') }}</span>
            </button>
        </div>

        <p class="w-full text-xs rtl:text-right ltr:text-left text-slate-500 dark:text-slate-400">
            @php
                $currentType = collect($licenseTypes)->firstWhere('value', $licenseType);
                $activationCount = $currentType?->maxActivations() ?? 1;
            @endphp
            {{ __('shop.license_activations', ['type' => $currentType?->localizedLabel() ?? '', 'count' => current_locale() === 'fa' ? fa_digits($activationCount) : $activationCount]) }}
        </p>
    @endif
</div>
