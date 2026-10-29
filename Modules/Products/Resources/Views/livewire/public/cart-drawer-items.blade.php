{{-- لیست آیتم‌های سبد + فوتر (partial برای cart-drawer) --}}
<div class="flex-1 overflow-y-auto p-5">
    <div class="space-y-4">
        @foreach ($items as $item)
            <div class="flex gap-4" wire:key="cart-item-{{ $item->id }}">
                <div class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-gradient-to-br from-primary-500/10 to-violet-500/10">
                    @if ($item->product?->cover_image)
                        <img src="{{ $item->product->cover_url }}" alt="{{ $item->product->localizedName() }}" class="h-full w-full object-cover">
                    @else
                        <x-icon :name="$item->product?->typeEnum()->icon() ?? 'products'" size="6" class="text-primary-300 dark:text-primary-700" />
                    @endif
                </div>

                <div class="min-w-0 flex-1">
                    <h3 class="truncate text-sm font-bold text-slate-900 dark:text-white">
                        {{ $item->product?->localizedName() }}
                    </h3>
                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                        {{ __('shop.license_label', ['type' => $item->license_label]) }}
                    </p>

                    <div class="mt-2 flex items-center justify-between">
                        {{-- کنترل تعداد --}}
                        <div class="flex items-center gap-1.5">
                            <button type="button" wire:click="decrement({{ $item->id }})"
                                    class="flex h-7 w-7 items-center justify-center rounded-lg border border-slate-200 text-sm transition hover:border-primary-300 hover:text-primary-600 dark:border-slate-700 dark:hover:border-primary-600">
                                −
                            </button>
                            <span class="w-8 text-center text-sm font-bold" dir="ltr">{{ fa_digits($item->quantity) }}</span>
                            <button type="button" wire:click="increment({{ $item->id }})"
                                    class="flex h-7 w-7 items-center justify-center rounded-lg border border-slate-200 text-sm transition hover:border-primary-300 hover:text-primary-600 dark:border-slate-700 dark:hover:border-primary-600">
                                +
                            </button>
                        </div>

                        <p class="text-sm font-black text-primary-600 dark:text-primary-400" dir="ltr">
                            {{ fa_digits(number_format($item->total_price)) }}
                        </p>
                    </div>
                </div>

                <button type="button" wire:click="removeItem({{ $item->id }})" wire:loading.attr="disabled" wire:target="removeItem({{ $item->id }})"
                        class="shrink-0 self-start rounded-lg p-1.5 text-slate-400 transition hover:bg-rose-50 hover:text-rose-600 dark:hover:bg-rose-950/40"
                        aria-label="{{ __('shop.remove_item') }}">
                    <x-icon name="trash" size="4" />
                </button>
            </div>
        @endforeach
    </div>

    <button type="button" wire:click="clearCart"
            class="mt-6 flex w-full items-center justify-center gap-1.5 rounded-xl border border-rose-200 py-2.5 text-xs font-semibold text-rose-600 transition hover:bg-rose-50 dark:border-rose-800 dark:hover:bg-rose-950/40 dark:text-rose-400">
        <x-icon name="trash" size="3.5" />
        {{ __('shop.clear_cart') }}
    </button>
</div>

{{-- فوتر: جمع + دکمه checkout --}}
<div class="border-t border-slate-200 p-5 dark:border-slate-800">
    <div class="mb-4 flex items-center justify-between">
        <span class="text-sm text-slate-500 dark:text-slate-400">{{ __('shop.subtotal') }}</span>
        <p class="text-xl font-black text-primary-600 dark:text-primary-400" dir="ltr">
            {{ format_price_for_locale((float) $subtotal) }}
        </p>
    </div>

    <button type="button" wire:click="checkout" wire:loading.attr="disabled" wire:target="checkout"
            class="btn-primary w-full !py-3.5">
        <x-icon name="wallet" size="4" />
        <span>{{ __('shop.checkout_now') }}</span>
    </button>

    <p class="mt-3 text-center text-xs text-slate-400">{{ __('shop.vat_note') }}</p>
</div>
