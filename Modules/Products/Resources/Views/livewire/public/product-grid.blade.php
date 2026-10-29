<div>
    {{-- نوار فیلتر + جستجو — موبایل: استک تمام‌عرض | دسکتاپ: ردیف واحد --}}
    <div class="p-4 mb-8 glass-card animate-slide-up sm:p-5">
        <div class="grid grid-cols-1 gap-3 sm:flex sm:flex-wrap sm:items-center">
            <div class="relative sm:min-w-[220px] sm:flex-1">
                <span class="absolute -translate-y-1/2 pointer-events-none right-3 top-1/2 text-slate-400">
                    <x-icon name="search" size="4" />
                </span>
                <input type="text" wire:model.live.debounce.400ms="search" placeholder="{{ __('shop.search_in_products') }}"
                       class="w-full py-3 pl-4 pr-10 text-sm transition border rounded-xl border-slate-200 bg-white/70 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-slate-800/60">
            </div>

            <select wire:model.live="category"
                    class="w-full rounded-xl border border-slate-200 bg-white/70 px-4 py-3 text-sm transition focus:border-primary-400 focus:outline-none sm:w-auto sm:py-2.5 dark:border-slate-700 dark:bg-slate-800/60">
                <option value="">{{ __('shop.all_categories') }}</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->localizedName() }}</option>
                @endforeach
            </select>

            <select wire:model.live="type"
                    class="w-full rounded-xl border border-slate-200 bg-white/70 px-4 py-3 text-sm transition focus:border-primary-400 focus:outline-none sm:w-auto sm:py-2.5 dark:border-slate-700 dark:bg-slate-800/60">
                <option value="">{{ __('shop.all_types') }}</option>
                @foreach ($types as $type)
                    <option value="{{ $type->value }}">{{ $type->localizedLabel() }}</option>
                @endforeach
            </select>

            <select wire:model.live="sort"
                    class="w-full rounded-xl border border-slate-200 bg-white/70 px-4 py-3 text-sm transition focus:border-primary-400 focus:outline-none sm:w-auto sm:py-2.5 dark:border-slate-700 dark:bg-slate-800/60">
                <option value="newest">{{ __('shop.sort.newest') }}</option>
                <option value="cheapest">{{ __('shop.sort.cheapest') }}</option>
                <option value="most_expensive">{{ __('shop.sort.most_expensive') }}</option>
                <option value="popular">{{ __('shop.sort.popular') }}</option>
            </select>

            @if ($search || $category || $type)
                <button wire:click="resetFilters" class="btn-outline w-full !px-4 !py-3 !text-xs sm:w-auto sm:!py-2.5">
                    <x-icon name="close" size="3.5" />
                    {{ __('shop.clear_filters') }}
                </button>
            @endif
        </div>
    </div>

    {{-- ایندیکیتور لودینگ --}}
    <div wire:loading.delay.200ms class="fixed z-40 items-center hidden gap-2 px-4 py-2 text-xs font-medium text-white rounded-full shadow-lg bottom-6 left-6 bg-primary-500/90 backdrop-blur-sm"
         wire:target="search, category, type, sort">
        <svg class="h-3.5 w-3.5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
        </svg>
        {{ __('shop.loading') }}
    </div>

    {{-- گرید محصولات --}}
    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($products as $product)
            <div class="p-4 mb-4 glass-card post-card group animate-slide-up" wire:key="product-{{ $product->id }}">
                <a href="{{ route('public.products.show', $product->slug) }}" class="relative block overflow-hidden rounded-xl h-52 bg-gradient-to-br from-primary-500/10 to-violet-500/10" title="{{ $product->localizedName() }}">
                    @if ($product->cover_image)
                        <img src="{{ $product->cover_url }}" alt="{{ $product->localizedName() }}" class="project-image">
                    @else
                        <div class="flex items-center justify-center w-full h-full">
                            <x-icon :name="$product->typeEnum()->icon()" size="12" class="text-primary-300 dark:text-primary-700" />
                        </div>
                    @endif

                    {{-- نوع محصول --}}
                    <span class="absolute top-3 ltr:right-3 rtl:left-3 inline-flex items-center gap-1 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-white rounded-full shadow-md bg-gradient-to-br from-primary-500 to-accent-500">
                        {{ $product->type_label }}
                    </span>


                    {{-- تخفیف --}}
                    @if ($product->has_sale)
                        <span class="absolute top-3 ltr:left-3 rtl:right-3 inline-flex items-center px-2 py-1 text-[10px] font-bold text-white rounded-full bg-rose-500 shadow-md">
                            {{ __('shop.discount_percent', ['percent' => fa_digits($product->discount_percent)]) }}
                        </span>
                    @endif
                </a>

                <div class="flex flex-col flex-1 p-5 pb-2">
                    <div class="flex items-center gap-2 mb-2 text-xs text-slate-400">
                        <x-icon name="products" size="3.5" />
                        <span>{{ $product->category?->localizedName() ?? __('shop.general') }}</span>
                        <span class="mr-auto" dir="ltr">v{{ $product->version }}</span>
                    </div>

                    <h3 class="mb-2 text-lg font-bold transition text-slate-900 group-hover:text-primary-600 dark:text-white dark:group-hover:text-primary-400">
                        <a href="{{ route('public.products.show', $product->slug) }}" class="transition hover:text-primary-600 dark:hover:text-primary-400">
                            {{ $product->localizedName() }}
                        </a>
                    </h3>

                    <p class="mb-4 text-sm line-clamp-2 text-slate-500 dark:text-slate-400">
                        {{ $product->localizedShortDescription() }}
                    </p>

                    <div class="flex items-center justify-between gap-3 mt-auto">
                        <div>
                            @if ($product->has_sale)
                                <span class="text-xs line-through text-slate-400" dir="ltr">{{ format_price_for_locale((float) $product->price) }}</span>
                            @endif
                            <p class="text-lg font-black text-primary-600 dark:text-primary-400" dir="ltr">
                                {{ format_price_for_locale($product->final_price) }}
                            </p>
                        </div>

                        @livewire('public.add-to-cart-button', ['productId' => $product->id], key('add-to-cart-'.$product->id))
                    </div>

                    <a href="{{ route('public.products.show', $product->slug) }}"
                       class="mt-3 flex items-center justify-center gap-1.5 rounded-xl border border-slate-200 py-2 text-xs font-semibold text-slate-600 transition hover:border-primary-300 hover:text-primary-600 dark:border-slate-700 dark:text-slate-300 dark:hover:border-primary-600 dark:hover:text-primary-400">
                        <x-icon name="eye" size="3.5" />
                        {{ __('shop.view_details') }}
                    </a>
                </div>
            </div>
        @empty
            <div class="p-12 text-center glass-card col-span-full animate-fade-in">
                <p class="text-slate-500 dark:text-slate-400">{{ __('shop.no_products') }}</p>
                <button wire:click="resetFilters" class="btn-outline mt-5 !px-5 !py-2.5 !text-xs">{{ __('shop.show_all_products') }}</button>
            </div>
        @endforelse
    </div>

    @if ($products->hasPages())
        <div class="flex justify-center mt-10">
            {{ $products->links('pagination::tailwind-rtl') }}
        </div>
    @endif
</div>
