{{-- پیشنهاد هوشمند سبد خرید — بر اساس علایق کاربر --}}
<div class="mt-8" wire:key="cart-recommendations">
    @if ($recommendations->isNotEmpty())
        <div class="glass-card p-6">
            <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                <h3 class="flex items-center gap-2 text-lg font-bold text-slate-900 dark:text-white">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-violet-100 text-violet-600 dark:bg-violet-900/40 dark:text-violet-400">
                        <x-icon name="star" size="4" />
                    </span>
                    @if ($isEmptyCart)
                        {{ __('shop.recommendations.special') }}
                    @else
                        {{ __('shop.recommendations.similar') }}
                    @endif
                </h3>

                @if ($interestLabels !== [])
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-xs text-slate-400">{{ __('shop.recommendations.based_on') }}</span>
                        @foreach ($interestLabels as $label)
                            <span class="rounded-full bg-primary-50 px-3 py-1 text-xs font-bold text-primary-600 dark:bg-primary-950/60 dark:text-primary-400">
                                {{ $label }}
                            </span>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($recommendations as $product)
                    <div class="post-card group flex flex-col overflow-hidden" wire:key="recommendation-{{ $product->id }}">
                        <a href="{{ route('public.products.show', $product->slug) }}" class="relative block h-32 overflow-hidden bg-gradient-to-br from-primary-500/10 to-violet-500/10">
                            @if ($product->cover_image)
                                <img src="{{ $product->cover_url }}" alt="{{ $product->localizedName() }}" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                            @else
                                <div class="flex h-full w-full items-center justify-center">
                                    <x-icon :name="$product->typeEnum()->icon()" size="10" class="text-primary-300 dark:text-primary-700" />
                                </div>
                            @endif

                            @if ($product->has_sale)
                                <span class="absolute left-2 top-2 rounded-full bg-rose-500 px-2 py-0.5 text-[10px] font-black text-white shadow-sm">
                                    ٪{{ fa_digits($product->discount_percent) }}
                                </span>
                            @endif

                            <span class="absolute right-2 top-2 rounded-full bg-white/90 px-2 py-0.5 text-[10px] font-bold text-primary-600 shadow-sm backdrop-blur-sm dark:bg-slate-900/80 dark:text-primary-400">
                                {{ $product->type_label }}
                            </span>
                        </a>

                        <div class="flex flex-1 flex-col p-3">
                            <h4 class="truncate text-sm font-bold text-slate-900 transition group-hover:text-primary-600 dark:text-white dark:group-hover:text-primary-400">
                                {{ $product->localizedName() }}
                            </h4>
                            <p class="mt-0.5 truncate text-[11px] text-slate-400">{{ $product->category?->localizedName() ?? __('shop.general') }}</p>

                            <div class="mt-auto flex items-center justify-between gap-2 pt-3">
                                <p class="text-sm font-black text-primary-600 dark:text-primary-400" dir="ltr">
                                    {{ format_price_for_locale((float) $product->final_price) }}
                                </p>

                                <button type="button" wire:click="quickAdd({{ $product->id }})" wire:loading.attr="disabled" wire:target="quickAdd({{ $product->id }})"
                                        class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary-500 text-white shadow-md shadow-primary-500/25 transition hover:bg-primary-600 disabled:opacity-50"
                                        aria-label="{{ __('shop.recommendations.add', ['name' => $product->localizedName()]) }}" title="{{ __('shop.add_to_cart') }}">
                                    <x-icon name="plus" size="3.5" />
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
