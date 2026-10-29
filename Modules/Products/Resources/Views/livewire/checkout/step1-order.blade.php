<div>
    @include('products::checkout.partials.progress-stepper', ['step' => 1])

    <div class="grid gap-8 lg:grid-cols-3">
        {{-- لیست آیتم‌ها --}}
        <div class="space-y-4 lg:col-span-2">
            <div class="glass-card p-6">
                <h2 class="mb-5 text-lg font-bold text-slate-900 dark:text-white">{{ __('shop.review_order') }}</h2>

                @if (empty($items))
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('shop.cart_empty') }}.</p>
                @else
                    <div class="space-y-4">
                        @foreach ($items as $row)
                            <div class="flex flex-col gap-3 border-b border-slate-100 pb-4 last:border-0 dark:border-slate-800 sm:flex-row sm:items-center" wire:key="checkout-item-{{ $row['item_id'] }}">
                                <div class="min-w-0 flex-1">
                                    <h3 class="truncate font-bold text-slate-900 dark:text-white">
                                        {{ $row['product']?->localizedName() }}
                                    </h3>
                                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                                        {{ __('shop.quantity_colon', ['count' => fa_digits($row['quantity'])]) }} ×
                                        <span dir="ltr">{{ format_price_for_locale((float) $row['unit_price']) }}</span>
                                    </p>
                                </div>

                                {{-- انتخاب نوع لایسنس --}}
                                <div class="flex flex-wrap items-center gap-1.5">
                                    @foreach ($licenseTypes as $type)
                                        <button type="button"
                                                wire:click="setLicense({{ $row['item_id'] }}, '{{ $type->value }}')"
                                                class="rounded-lg border px-3 py-1.5 text-xs font-semibold transition {{ ($row['license_type'] ?? '') === $type->value
                                                    ? 'border-primary-500 bg-primary-50 text-primary-600 dark:bg-primary-950/40 dark:text-primary-400'
                                                    : 'border-slate-200 text-slate-500 hover:border-primary-300 dark:border-slate-700 dark:text-slate-400' }}">
                                            {{ $type->localizedLabel() }}
                                        </button>
                                    @endforeach
                                </div>

                                <p class="w-28 shrink-0 text-left font-black text-primary-600 dark:text-primary-400" dir="ltr">
                                    {{ fa_digits(number_format($row['unit_price'] * $row['quantity'])) }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- کد تخفیف --}}
            <div class="glass-card p-6">
                <h3 class="mb-4 flex items-center gap-2 font-bold text-slate-900 dark:text-white">
                    <x-icon name="wallet" size="4" />
                    {{ __('shop.discount_code') }}
                </h3>

                @if (! empty($totals['discount_code']))
                    <div class="flex items-center justify-between gap-3 rounded-xl border border-emerald-200 bg-emerald-50 p-3 dark:border-emerald-800 dark:bg-emerald-950/40">
                        <span class="text-sm font-bold text-emerald-700 dark:text-emerald-400">
                            {{ __('shop.discount_applied', ['code' => $totals['discount_code'], 'percent' => fa_digits((int) $totals['discount_percent'])]) }}
                        </span>
                        <button type="button" wire:click="removeDiscount" class="text-xs font-bold text-rose-600 hover:underline dark:text-rose-400">
                            {{ __('shop.remove') }}
                        </button>
                    </div>
                @else
                    <div class="flex flex-wrap gap-3">
                        <input type="text" wire:model.live="discountCode" placeholder="{{ __('shop.discount_code_placeholder') }}"
                               class="flex-1 rounded-xl border border-slate-200 bg-white/70 px-4 py-2.5 text-sm transition focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-slate-800/60"
                               dir="ltr">
                        <button type="button" wire:click="applyDiscount" class="btn-outline !px-5 !py-2.5 !text-xs">
                            {{ __('shop.apply_code') }}
                        </button>
                    </div>
                    @if ($errorMessage)
                        <p class="mt-2 text-xs font-bold text-rose-600 dark:text-rose-400">{{ $errorMessage }}</p>
                    @endif
                @endif
            </div>
        </div>

        {{-- خلاصه --}}
        <div class="space-y-4">
            <div class="glass-card p-6 lg:sticky lg:top-28">
                <h3 class="mb-4 font-bold text-slate-900 dark:text-white">{{ __('shop.order_summary') }}</h3>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-slate-500 dark:text-slate-400">{{ __('shop.items_subtotal') }}</dt>
                        <dd class="font-bold" dir="ltr">{{ fa_digits(number_format($totals['subtotal'])) }}</dd>
                    </div>
                    @if ($totals['discount_amount'] > 0)
                        <div class="flex justify-between text-emerald-600 dark:text-emerald-400">
                            <dt>{{ __('shop.discount_percent_short', ['percent' => fa_digits((int) $totals['discount_percent'])]) }}</dt>
                            <dd class="font-bold" dir="ltr">-{{ fa_digits(number_format($totals['discount_amount'])) }}</dd>
                        </div>
                    @endif
                    <div class="flex justify-between text-slate-500 dark:text-slate-400">
                        <dt>{{ __('shop.vat', ['percent' => fa_digits((int) $totals['tax_percent'])]) }}</dt>
                        <dd class="font-bold" dir="ltr">{{ fa_digits(number_format($totals['tax_amount'])) }}</dd>
                    </div>
                    <div class="mt-3 flex justify-between border-t border-slate-200 pt-3 dark:border-slate-700">
                        <dt class="font-bold text-slate-900 dark:text-white">{{ __('shop.payable') }}</dt>
                        <dd class="text-lg font-black text-primary-600 dark:text-primary-400" dir="ltr">
                            {{ fa_digits(number_format($totals['total'])) }}
                        </dd>
                    </div>
                </dl>

                <button type="button" wire:click="nextStep" class="btn-primary mt-6 w-full !py-3.5">
                    {{ __('shop.continue_to_personal') }}
                    <x-icon name="chevronLeft" size="4" />
                </button>

                <a href="{{ route('public.cart.index') }}" class="btn-outline mt-3 w-full !py-3 !text-xs">
                    {{ __('shop.back_to_cart') }}
                </a>
            </div>
        </div>
    </div>
</div>
