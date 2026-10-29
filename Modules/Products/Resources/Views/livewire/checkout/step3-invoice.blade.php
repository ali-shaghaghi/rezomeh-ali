<div>
    @include('products::checkout.partials.progress-stepper', ['step' => 3])

    <div class="grid gap-8 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            {{-- صورت‌حساب --}}
            <div class="glass-card p-6">
                <h2 class="mb-5 text-lg font-bold text-slate-900 dark:text-white">{{ __('shop.invoice') }}</h2>

                <div class="overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-700">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-800/60">
                            <tr>
                                <th class="p-3 text-right font-bold">{{ __('shop.col_product') }}</th>
                                <th class="p-3 text-center font-bold">{{ __('shop.col_license') }}</th>
                                <th class="p-3 text-center font-bold">{{ __('shop.col_quantity') }}</th>
                                <th class="p-3 text-left font-bold">{{ __('shop.col_price') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach ($items as $row)
                                <tr wire:key="invoice-item-{{ $row['item_id'] }}">
                                    <td class="p-3 font-medium">{{ $row['product']?->localizedName() }}</td>
                                    <td class="p-3 text-center text-slate-500 dark:text-slate-400">{{ $row['license_label'] }}</td>
                                    <td class="p-3 text-center" dir="ltr">{{ fa_digits($row['quantity']) }}</td>
                                    <td class="p-3 text-left font-bold" dir="ltr">{{ fa_digits(number_format($row['unit_price'] * $row['quantity'])) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <dl class="mt-6 space-y-3 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-slate-500 dark:text-slate-400">{{ __('shop.items_subtotal') }}</dt>
                        <dd class="font-bold" dir="ltr">{{ format_price_for_locale((float) $totals['subtotal']) }}</dd>
                    </div>
                    @if ($totals['discount_amount'] > 0)
                        <div class="flex justify-between text-emerald-600 dark:text-emerald-400">
                            <dt>{{ __('shop.discount_code_short', ['code' => $totals['discount_code'] ?: '-']) }}</dt>
                            <dd class="font-bold" dir="ltr">-{{ format_price_for_locale((float) $totals['discount_amount']) }}</dd>
                        </div>
                    @endif
                    <div class="flex justify-between">
                        <dt class="text-slate-500 dark:text-slate-400">{{ __('shop.vat', ['percent' => fa_digits((int) $totals['tax_percent'])]) }}</dt>
                        <dd class="font-bold" dir="ltr">{{ format_price_for_locale((float) $totals['tax_amount']) }}</dd>
                    </div>
                    <div class="mt-3 flex justify-between border-t border-slate-200 pt-4 dark:border-slate-700">
                        <dt class="font-bold text-slate-900 dark:text-white">{{ __('shop.final_amount') }}</dt>
                        <dd class="text-2xl font-black text-primary-600 dark:text-primary-400" dir="ltr">
                            {{ format_price_for_locale((float) $totals['total']) }}
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        {{-- روش پرداخت --}}
        <div>
            <div class="glass-card p-6 lg:sticky lg:top-28">
                <h3 class="mb-4 font-bold text-slate-900 dark:text-white">{{ __('shop.payment_method') }}</h3>

                <div class="space-y-3">
                    @foreach ($paymentMethods as $key => $label)
                        <button type="button" wire:click="selectMethod('{{ $key }}')"
                                class="flex w-full items-center gap-3 rounded-xl border p-3.5 text-right transition {{ $paymentMethod === $key
                                    ? 'border-primary-500 bg-primary-50 dark:bg-primary-950/40'
                                    : 'border-slate-200 hover:border-primary-300 dark:border-slate-700' }}">
                            <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full border-2 {{ $paymentMethod === $key ? 'border-primary-500' : 'border-slate-300 dark:border-slate-600' }}">
                                @if ($paymentMethod === $key)
                                    <span class="h-2.5 w-2.5 rounded-full bg-primary-500"></span>
                                @endif
                            </span>
                            <span class="text-sm font-bold text-slate-900 dark:text-white">{{ $label }}</span>
                        </button>
                    @endforeach
                </div>

                @if ($errorMessage)
                    <p class="mt-3 text-xs font-bold text-rose-600 dark:text-rose-400">{{ $errorMessage }}</p>
                @endif

                <button type="button" wire:click="nextStep" class="btn-primary mt-6 w-full !py-3.5">
                    {{ __('shop.continue_to_payment') }}
                    <x-icon name="chevronLeft" size="4" />
                </button>

                <button type="button" wire:click="prevStep" class="btn-outline mt-3 w-full !py-3 !text-xs">
                    <x-icon name="chevronRight" size="4" />
                    {{ __('shop.back') }}
                </button>
            </div>
        </div>
    </div>
</div>
