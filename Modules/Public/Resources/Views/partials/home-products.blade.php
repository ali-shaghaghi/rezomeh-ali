{{-- ===== محصولات ===== --}}
<section id="products" class="mb-32 scroll-mt-28">
    <div class="reveal">
        <div class="section-badge">{{ __('public.products.title') }}</div>
        <h2 class="section-title">{{ __('public.products.title') }}</h2>
        <p class="section-subtitle mx-auto !mb-0 text-center">{{ __('public.products.subtitle') }}</p>
    </div>

    @if ($products->isNotEmpty())
        <div class="grid grid-cols-1 gap-6 mt-12 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($products as $product)
            

                   
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
            @endforeach
        </div>

        {{-- دکمه «تمام محصولات» --}}
        <div class="mt-10 text-center reveal">
            <a href="{{ route('public.products.index') }}" class="inline-flex items-center gap-2 btn-outline">
                {{ __('public.products.view_all') }}
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </a>
        </div>
    @else
        <div class="py-16 text-center text-slate-400 dark:text-slate-500 reveal glass-card">
            {{ __('public.common.no_results') }}
        </div>
    @endif
</section>
