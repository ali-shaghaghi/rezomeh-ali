<div>
    @include('products::checkout.partials.progress-stepper', ['step' => 2])

    <div class="grid gap-8 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <div class="glass-card p-6">
                <h2 class="mb-2 text-lg font-bold text-slate-900 dark:text-white">{{ __('shop.personal_info') }}</h2>
                <p class="mb-6 text-sm text-slate-500 dark:text-slate-400">
                    {{ __('shop.personal_info_desc') }}
                </p>

                <div class="grid gap-5">
                    <div>
                        <label class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-200">{{ __('shop.full_name') }}</label>
                        <input type="text" wire:model.blur="name" placeholder="{{ __('shop.full_name_placeholder') }}"
                               class="w-full rounded-xl border border-slate-200 bg-white/70 px-4 py-3 text-sm transition focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-slate-800/60">
                        @error('name')
                            <p class="mt-1.5 text-xs font-bold text-rose-600 dark:text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-200">{{ __('shop.phone') }}</label>
                            <input type="text" wire:model.blur="phone" placeholder="09121234567" dir="ltr"
                                   class="w-full rounded-xl border border-slate-200 bg-white/70 px-4 py-3 text-sm transition focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-slate-800/60">
                            @error('phone')
                                <p class="mt-1.5 text-xs font-bold text-rose-600 dark:text-rose-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-200">{{ __('shop.email_optional') }}</label>
                            <input type="email" wire:model.blur="email" placeholder="you@example.com" dir="ltr"
                                   class="w-full rounded-xl border border-slate-200 bg-white/70 px-4 py-3 text-sm transition focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-slate-800/60">
                            @error('email')
                                <p class="mt-1.5 text-xs font-bold text-rose-600 dark:text-rose-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-200">{{ __('shop.notes_optional') }}</label>
                        <textarea wire:model.blur="notes" rows="4" placeholder="{{ __('shop.notes_placeholder') }}"
                                  class="w-full rounded-xl border border-slate-200 bg-white/70 px-4 py-3 text-sm transition focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-slate-800/60"></textarea>
                        @error('notes')
                            <p class="mt-1.5 text-xs font-bold text-rose-600 dark:text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-8 flex flex-wrap gap-3">
                    <button type="button" wire:click="nextStep" class="btn-primary !px-6 !py-3.5">
                        {{ __('shop.continue_to_invoice') }}
                        <x-icon name="chevronLeft" size="4" />
                    </button>
                    <button type="button" wire:click="prevStep" class="btn-outline !px-5 !py-3 !text-xs">
                        <x-icon name="chevronRight" size="4" />
                        {{ __('shop.back') }}
                    </button>
                </div>
            </div>
        </div>

        {{-- خلاصه سبد --}}
        <div>
            <div class="glass-card p-6 lg:sticky lg:top-28">
                <h3 class="mb-4 font-bold text-slate-900 dark:text-white">{{ __('shop.your_cart') }}</h3>
                <div class="space-y-3 text-sm">
                    @foreach ($items as $row)
                        <div class="flex justify-between gap-2">
                            <span class="truncate text-slate-500 dark:text-slate-400">
                                {{ $row['product']?->localizedName() }} ({{ fa_digits($row['quantity']) }})
                            </span>
                            <span class="shrink-0 font-bold" dir="ltr">{{ format_price_for_locale((float) $row['unit_price'] * $row['quantity']) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
