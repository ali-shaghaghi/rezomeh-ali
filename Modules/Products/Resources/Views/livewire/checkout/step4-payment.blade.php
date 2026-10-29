<div>
    @include('products::checkout.partials.progress-stepper', ['step' => 4])

    <div class="grid gap-8 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <div class="glass-card p-6">
                <h2 class="mb-2 text-lg font-bold text-slate-900 dark:text-white">{{ __('shop.payment') }}</h2>
                <p class="mb-6 text-sm text-slate-500 dark:text-slate-400">
                    {{ __('shop.selected_method') }} <b>{{ $paymentMethods[$paymentMethod] ?? $paymentMethod }}</b>
                </p>

                @if ($errorMessage)
                    <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm font-bold text-rose-600 dark:border-rose-800 dark:bg-rose-950/40 dark:text-rose-400">
                        {{ $errorMessage }}
                    </div>
                @endif

                @if ($paymentMethod === 'manual')
                    <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 dark:border-amber-800 dark:bg-amber-950/40">
                        <h3 class="mb-2 flex items-center gap-2 font-bold text-amber-700 dark:text-amber-400">
                            <x-icon name="wallet" size="4" />
                            {{ __('shop.manual_title') }}
                        </h3>
                        <div class="space-y-3 text-sm text-amber-700 dark:text-amber-400/90">
                            <p>{{ __('shop.manual_desc') }}</p>
                            @if ($cardNumber !== '')
                                <div class="rounded-xl bg-white/70 p-3 dark:bg-slate-900/40">
                                    <p class="text-[10px] font-bold uppercase">شماره کارت</p>
                                    <p class="mt-1 font-mono text-sm tracking-widest" dir="ltr">{{ $cardNumber }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @elseif ($paymentMethod === 'crypto')
                    <div class="rounded-2xl border border-violet-200 bg-violet-50 p-5 dark:border-violet-800 dark:bg-violet-950/40" x-data="{ copied: '' }">
                        <h3 class="mb-2 flex items-center gap-2 font-bold text-violet-700 dark:text-violet-400">
                            <x-icon name="wallet" size="4" />
                            {{ __('shop.crypto_title') }}
                        </h3>
                        <div class="space-y-3 text-sm text-violet-700 dark:text-violet-400/90">
                            <p>{{ __('shop.crypto_desc') }}</p>

                            @if (count($cryptoCoins))
                                <div class="space-y-3">
                                    @foreach ($cryptoCoins as $coin)
                                        <div class="rounded-xl border border-violet-200/70 bg-white/70 p-3 dark:border-violet-700/60 dark:bg-slate-900/40" wire:key="coin-{{ $coin['key'] }}">
                                            <div class="flex items-center justify-between gap-2">
                                                <p class="text-xs font-black text-slate-800 dark:text-slate-100">{{ $coin['name'] }}</p>
                                                <span class="rounded-full bg-violet-100 px-2 py-0.5 text-[10px] font-bold text-violet-700 dark:bg-violet-900/50 dark:text-violet-300">{{ $coin['network'] }}</span>
                                            </div>

                                            @if ($coin['amount'] !== null)
                                                <p class="mt-1.5 text-sm font-black text-violet-700 dark:text-violet-300" dir="ltr">
                                                    ≈ {{ fa_digits(number_format($coin['amount'], $coin['decimals'])) }} {{ $coin['key'] === 'usdt' ? 'USDT' : strtoupper($coin['key']) }}
                                                </p>
                                            @else
                                                <p class="mt-1.5 text-[11px] font-bold text-slate-400">نرخ این رمزارز هنوز در تنظیمات ثبت نشده است.</p>
                                            @endif

                                            <div class="mt-2 flex items-center justify-between gap-2 rounded-lg bg-slate-50 p-2 dark:bg-slate-800/60">
                                                <p class="min-w-0 flex-1 truncate font-mono text-[11px] text-slate-600 dark:text-slate-300" dir="ltr">{{ $coin['address'] }}</p>
                                                <button type="button"
                                                        x-on:click="navigator.clipboard.writeText({{ \Illuminate\Support\Js::from($coin['address']) }}); copied = '{{ $coin['key'] }}'; setTimeout(() => copied = '', 2000)"
                                                        class="shrink-0 rounded-lg bg-violet-500 px-2.5 py-1 text-[10px] font-bold text-white transition hover:bg-violet-600">
                                                    <span x-show="copied !== '{{ $coin['key'] }}'">کپی آدرس</span>
                                                    <span x-show="copied === '{{ $coin['key'] }}'" style="display: none">کپی شد ✔</span>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-xs font-bold">هنوز آدرسی برای دریافت رمزارز ثبت نشده است — از پنل ادمین: تنظیمات ← درگاه پرداخت ← کیف پول ارز دیجیتال.</p>
                            @endif

                            <p class="text-xs">{{ __('shop.crypto_warning', ['network' => strtoupper($cryptoNetwork)]) }}</p>
                        </div>
                    </div>
                @else
                    <div class="rounded-2xl border border-primary-200 bg-primary-50 p-5 dark:border-primary-800 dark:bg-primary-950/40">
                        <h3 class="mb-2 flex items-center gap-2 font-bold text-primary-700 dark:text-primary-400">
                            <x-icon name="shield" size="4" />
                            {{ __('shop.gateway_title') }}
                        </h3>
                        <p class="text-sm text-primary-700 dark:text-primary-400/90">
                            {{ __('shop.gateway_desc') }}
                        </p>
                    </div>
                @endif

                <div class="mt-8 flex flex-wrap gap-3">
                    <button type="button" wire:click="pay" wire:loading.attr="disabled" wire:target="pay"
                            class="btn-primary !px-8 !py-3.5">
                        <x-icon name="wallet" size="4" />
                        <span wire:loading.remove wire:target="pay">{{ __('shop.pay_now') }}</span>
                        <span wire:loading wire:target="pay">{{ __('shop.processing') }}</span>
                    </button>
                    <a href="{{ route('checkout.step3') }}" class="btn-outline !px-5 !py-3 !text-xs">
                        <x-icon name="chevronRight" size="4" />
                        {{ __('shop.back') }}
                    </a>
                </div>
            </div>
        {{-- خلاصه نهایی --}}
        <div>
            <div class="glass-card p-6 lg:sticky lg:top-28">
                <h3 class="mb-4 font-bold text-slate-900 dark:text-white">{{ __('shop.payable') }}</h3>

                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-slate-500 dark:text-slate-400">{{ __('shop.items_subtotal') }}</dt>
                        <dd class="font-bold" dir="ltr">{{ fa_digits(number_format($totals['subtotal'])) }}</dd>
                    </div>
                    @if ($totals['discount_amount'] > 0)
                        <div class="flex justify-between text-emerald-600 dark:text-emerald-400">
                            <dt>{{ __('shop.discount') }}</dt>
                            <dd class="font-bold" dir="ltr">-{{ fa_digits(number_format($totals['discount_amount'])) }}</dd>
                        </div>
                    @endif
                    <div class="flex justify-between">
                        <dt class="text-slate-500 dark:text-slate-400">{{ __('shop.vat_short', ['percent' => fa_digits((int) $totals['tax_percent'])]) }}</dt>
                        <dd class="font-bold" dir="ltr">{{ fa_digits(number_format($totals['tax_amount'])) }}</dd>
                    </div>
                    <div class="mt-3 flex justify-between border-t border-slate-200 pt-4 dark:border-slate-700">
                        <dt class="font-bold text-slate-900 dark:text-white">{{ __('shop.total') }}</dt>
                        <dd class="text-2xl font-black text-primary-600 dark:text-primary-400" dir="ltr">
                            {{ fa_digits(number_format($totals['total'])) }}
                        </dd>
                    </div>
                </dl>

                <div class="mt-5 space-y-2 rounded-2xl bg-slate-50 p-4 dark:bg-slate-800/60">
                    @foreach ($items as $row)
                        <div class="flex justify-between text-xs">
                            <span class="truncate text-slate-500 dark:text-slate-400">{{ $row['product']?->localizedName() }}</span>
                            <span class="font-bold" dir="ltr">{{ fa_digits(number_format($row['unit_price'] * $row['quantity'])) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
