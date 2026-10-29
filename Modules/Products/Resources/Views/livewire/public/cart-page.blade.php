{{-- صفحه سبد خرید — نسخه صفحه‌ای از cart-drawer-items --}}
<div>
    @if (session('error'))
        <div class="mb-5 flex items-center gap-2 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm font-bold text-rose-600 dark:border-rose-800 dark:bg-rose-950/40 dark:text-rose-400">
            <x-icon name="close" size="4" />
            {{ session('error') }}
        </div>
    @endif

    @if ($errorMessage)
        <div class="mb-5 flex items-center gap-2 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm font-bold text-rose-600 dark:border-rose-800 dark:bg-rose-950/40 dark:text-rose-400" wire:key="cart-page-error">
            <x-icon name="close" size="4" />
            {{ $errorMessage }}
        </div>
    @endif

    @if ($count === 0)
        {{-- سبد خالی --}}
        <div class="glass-card flex flex-col items-center justify-center gap-4 py-14 text-center">
            <span class="flex h-20 w-20 items-center justify-center rounded-full bg-slate-100 text-slate-400 dark:bg-slate-800">
                <x-icon name="cart" size="10" />
            </span>
            <div>
                <h3 class="font-bold text-slate-900 dark:text-white">{{ __('shop.cart_empty') }}</h3>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('shop.cart_empty_desc') }}</p>
            </div>
            <div class="flex flex-wrap justify-center gap-3">
                <a href="{{ route('public.products.index') }}" class="btn-primary !px-5 !py-2.5 !text-xs">
                    <x-icon name="products" size="4" />
                    {{ __('shop.browse_products') }}
                </a>
            </div>
        </div>
    @else
        <div class="grid gap-8 lg:grid-cols-3">
            {{-- لیست آیتم‌ها --}}
            <div class="space-y-4 lg:col-span-2">
                <div class="glass-card p-6">
                    <h2 class="mb-5 text-lg font-bold text-slate-900 dark:text-white">
                        {{ __('shop.cart_items') }}
                        <span class="rounded-full bg-primary-100 px-2.5 py-0.5 text-xs font-bold text-primary-600 dark:bg-primary-950/60 dark:text-primary-400">
                            {{ fa_digits($count) }}
                        </span>
                    </h2>

                    <div class="space-y-4">
                        @foreach ($items as $item)
                            <div class="flex gap-4" wire:key="cart-page-item-{{ $item->id }}">
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

                    <button type="button" wire:click="clearCart" wire:loading.attr="disabled" wire:target="clearCart"
                            class="mt-6 flex w-full items-center justify-center gap-1.5 rounded-xl border border-rose-200 py-2.5 text-xs font-semibold text-rose-600 transition hover:bg-rose-50 dark:border-rose-800 dark:hover:bg-rose-950/40 dark:text-rose-400">
                        <x-icon name="trash" size="3.5" />
                        {{ __('shop.clear_cart') }}
                    </button>
                </div>
            </div>

            {{-- خلاصه + دکمه تکمیل خرید --}}
            <div class="space-y-4">
                <div class="glass-card p-6 lg:sticky lg:top-28">
                    <h3 class="mb-4 font-bold text-slate-900 dark:text-white">{{ __('shop.subtotal') }}</h3>
                    <div class="mb-4 flex items-center justify-between">
                        <span class="text-sm text-slate-500 dark:text-slate-400">{{ __('shop.payable') }}</span>
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
            </div>
        </div>

        {{-- پیشنهاد هوشمند — بر اساس علایق کاربر --}}
        @livewire('public.cart-recommendations', key('cart-recommendations'))
    @endif
</div>
