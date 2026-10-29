<x-public.layout :title="$product->localizedName()">
    <div class="space-y-8">
        @php
            // نمایش دو‌زبانه با fallback (#محصولات انگلیسی)
            $showName = $product->localizedName();
            $showShort = $product->localizedShortDescription() ?? $product->short_description;
            $showDescription = $product->localizedDescription() ?? $product->description;
            $showCategory = $product->category?->localizedName();
        @endphp

        {{-- مسیر --}}
        <nav class="flex items-center gap-2 text-xs text-slate-400 animate-fade-in" aria-label="{{ __('shop.breadcrumb.home') }}">
            <a href="{{ route('public.home') }}" class="transition hover:text-primary-600">{{ __('shop.breadcrumb.home') }}</a>
            <span>/</span>
            <a href="{{ route('public.products.index') }}" class="transition hover:text-primary-600">{{ __('shop.breadcrumb.products') }}</a>
            <span>/</span>
            @if ($product->category)
                <span>{{ $showCategory }}</span>
                <span>/</span>
            @endif
            <span class="font-bold text-slate-600 dark:text-slate-300">{{ $showName }}</span>
        </nav>

        <div class="grid gap-8 lg:grid-cols-2 animate-slide-up">
            @php
                // گالری: اول عکس شاخص، بعد عکس‌های گالری — همه در یک آرایه برای پیش‌نمایش و زوم
                $galleryImages = collect();
                if ($product->cover_image) {
                    $galleryImages->push(['src' => $product->cover_url, 'alt' => $showName]);
                }
                foreach ($product->images as $image) {
                    $galleryImages->push(['src' => $image->url, 'alt' => $image->alt ?? $showName]);
                }
                $galleryImages = $galleryImages->values();
            @endphp

            {{-- تصویر/پیش‌نمایش — قاب با ابعاد فیکس (بدون پرش چیدمان) + زوم + تعویض از گالری --}}
            <div class="overflow-hidden glass-card"
                 x-data="{
                     current: 0,
                     zoom: false,
                     images: {{ \Illuminate\Support\Js::from($galleryImages->all()) }},
                     prev() { this.current = (this.current + this.images.length - 1) % this.images.length; },
                     next() { this.current = (this.current + 1) % this.images.length; },
                 }"
                 @keydown.escape.window="zoom = false"
                 @keydown.arrow-left.window="zoom && prev()"
                 @keydown.arrow-right.window="zoom && next()">
                <div class="relative flex h-72 items-center justify-center overflow-hidden bg-gradient-to-br from-primary-500/10 to-violet-500/10 sm:h-80 lg:h-96">
                    @if ($galleryImages->isNotEmpty())
                        {{-- ابعاد رزروشده (width/height + ارتفاع ثابت قاب) → جای‌گذاری بدون جابه‌جایی چیدمان --}}
                        <img :src="images[current].src" :alt="images[current].alt"
                             width="800" height="600" loading="lazy"
                             class="h-full w-full object-contain p-2">
                        <button type="button" @click="zoom = true"
                                class="absolute bottom-3 left-3 inline-flex items-center gap-1.5 rounded-xl bg-slate-900/70 px-3 py-2 text-[11px] font-bold text-white backdrop-blur transition hover:bg-slate-900/90">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7"/></svg>
                            بزرگ‌نمایی
                        </button>
                    @else
                        <x-icon :name="$product->typeEnum()->icon()" size="16" class="text-primary-300 dark:text-primary-700" />
                    @endif

                    @if ($product->has_sale)
                        <span class="absolute left-4 top-4 rounded-full bg-rose-500 px-3 py-1 text-sm font-black text-white shadow-sm">
                            {{ __('shop.discount_percent', ['percent' => fa_digits($product->discount_percent)]) }}
                        </span>
                    @endif
                </div>

                @if ($galleryImages->count() > 1)
                    <div class="grid grid-cols-4 gap-2 p-4">
                        <template x-for="(img, index) in images" :key="index">
                            <button type="button" @click="current = index"
                                    class="overflow-hidden rounded-lg border-2 transition"
                                    :class="current === index ? 'border-primary-500 ring-2 ring-primary-500/30' : 'border-slate-200 dark:border-slate-700'">
                                <img :src="img.src" :alt="img.alt" width="120" height="64" loading="lazy" class="h-16 w-full object-cover">
                            </button>
                        </template>
                    </div>
                @endif

                {{-- زوم / بزرگ‌نمایی — تمام‌صفحه، سازگار با موبایل و دسکتاپ --}}
                <template x-if="zoom && images.length">
                    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 p-3 sm:p-6" @click="zoom = false">
                        <img :src="images[current].src" :alt="images[current].alt"
                             class="max-h-[85vh] max-w-full rounded-2xl object-contain shadow-2xl select-none" @click.stop>

                        <button type="button" @click.stop="zoom = false"
                                class="absolute right-3 top-3 flex h-10 w-10 items-center justify-center rounded-full bg-white/10 text-white transition hover:bg-white/25" aria-label="بستن">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>

                        <template x-if="images.length > 1">
                            <div class="flex items-center gap-3 sm:gap-6" @click.stop>
                                <button type="button" @click.stop="prev()"
                                        class="flex h-11 w-11 items-center justify-center rounded-full bg-white/10 text-white transition hover:bg-white/25" aria-label="قبلی">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                </button>
                                <span class="rounded-full bg-white/15 px-3 py-1 text-xs font-bold text-white" x-text="(current + 1) + ' / ' + images.length"></span>
                                <button type="button" @click.stop="next()"
                                        class="flex h-11 w-11 items-center justify-center rounded-full bg-white/10 text-white transition hover:bg-white/25" aria-label="بعدی">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                                </button>
                            </div>
                        </template>
                    </div>
                </template>
            </div>

            {{-- اطلاعات --}}
            <div class="space-y-5">
                <div class="p-5 glass-card">
                    <div class="flex items-center gap-3 mb-3">
                        <span class="px-3 py-1 text-xs font-bold rounded-full bg-primary-100 text-primary-600 dark:bg-primary-950/60 dark:text-primary-400">
                            {{ $product->type_label }}
                        </span>
                        <span class="text-xs text-slate-400" dir="ltr">{{ __('shop.version', ['version' => $product->version]) }}</span>
                    </div>

                    <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                        {{ $showName }}
                    </h1>

                    @if ($product->category)
                        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                            {{ __('shop.category_label', ['category' => $showCategory]) }}
                        </p>
                    @endif

                    <p class="leading-relaxed text-slate-600 dark:text-slate-300">
                        {{ $showShort }}
                    </p>

                    <div class="flex items-end justify-between gap-3">
                        <div class="flex flex-col items-start gap-3">
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('shop.product_price') }}</p>
                            @if ($product->has_sale)
                                <span class="text-sm line-through text-slate-400" dir="ltr">{{ format_price_for_locale((float) $product->price) }}</span>
                            @endif
                            <p class="text-3xl font-black text-primary-600 dark:text-primary-400" dir="ltr">
                                {{ format_price_for_locale($product->final_price) }}
                            </p>
                        </div>
                        @if ($product->is_downloadable)
                            <span class="flex items-center gap-1.5 rounded-full bg-emerald-100 px-3 py-1.5 text-xs font-bold text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400">
                                <x-icon name="download" size="3.5" />
                                {{ __('shop.downloadable') }}
                            </span>
                        @endif
                    </div>

                    <div class="pt-5 mt-5 border-t border-slate-200 dark:border-slate-700">
                        @livewire('public.add-to-cart-button', ['productId' => $product->id, 'layout' => 'page'], key('add-to-cart-page-'.$product->id))
                    </div>
                </div>

                @if ($product->demo_url)
                    <a href="{{ $product->demo_url }}" target="_blank" rel="noopener" class="btn-outline w-full !py-3">
                        <x-icon name="eye" size="4" />
                        {{ __('shop.view_demo') }}
                    </a>
                @endif
            </div>
        </div>

        {{-- تب‌های توضیحات/ویژگی‌ها — زیر باکس خرید، سوییچ آنی بدون reload و بدون اسکرول طولانی --}}
        <div class="glass-card animate-slide-up" x-data="{ tab: 'description' }">
            <div class="flex gap-1 p-2 border-b border-slate-200 dark:border-slate-700" role="tablist">
                <button type="button" role="tab" @click="tab = 'description'"
                        :aria-selected="tab === 'description'"
                        :class="tab === 'description' ? 'bg-primary-100 text-primary-700 dark:bg-primary-950/60 dark:text-primary-300' : 'bg-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200'"
                        class="rounded-xl px-4 py-2.5 text-sm font-bold transition-colors">
                    {{ __('shop.tab_description') }}
                </button>
                <button type="button" role="tab" @click="tab = 'features'"
                        :aria-selected="tab === 'features'"
                        :class="tab === 'features' ? 'bg-primary-100 text-primary-700 dark:bg-primary-950/60 dark:text-primary-300' : 'bg-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200'"
                        class="rounded-xl px-4 py-2.5 text-sm font-bold transition-colors">
                    {{ __('shop.tab_features') }}
                </button>
            </div>

            {{-- پنل توضیحات محصول --}}
            <div class="p-5" role="tabpanel" x-show="tab === 'description'" x-cloak x-transition.opacity.duration.200ms>
                @if ($showDescription)
                    <div class="text-sm leading-relaxed prose-public max-w-none text-slate-600 dark:text-slate-300">
                        {!! render_inline_media($showDescription) !!}
                    </div>
                @else
                    <p class="text-sm leading-relaxed text-slate-600 dark:text-slate-300">{{ $showShort }}</p>
                @endif
            </div>

            {{-- پنل ویژگی‌ها --}}
            <div class="p-5" role="tabpanel" x-show="tab === 'features'" x-cloak x-transition.opacity.duration.200ms>
                @php
                    $licenseLabels = collect(\Modules\Products\Enums\LicenseType::cases())->map->localizedLabel()->values();
                @endphp
                <dl class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div class="flex items-center justify-between gap-3 rounded-xl bg-slate-50 px-4 py-3 dark:bg-slate-800/50">
                        <dt class="text-slate-500 dark:text-slate-400">{{ __('shop.features.category') }}</dt>
                        <dd class="font-bold text-slate-800 dark:text-slate-200">{{ $showCategory ?? __('shop.general') }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3 rounded-xl bg-slate-50 px-4 py-3 dark:bg-slate-800/50">
                        <dt class="text-slate-500 dark:text-slate-400">{{ __('shop.features.product_type') }}</dt>
                        <dd class="font-bold text-slate-800 dark:text-slate-200">{{ $product->type_label }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3 rounded-xl bg-slate-50 px-4 py-3 dark:bg-slate-800/50">
                        <dt class="text-slate-500 dark:text-slate-400">{{ __('shop.features.version') }}</dt>
                        <dd class="font-bold text-slate-800 dark:text-slate-200" dir="ltr">{{ $product->version }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3 rounded-xl bg-slate-50 px-4 py-3 dark:bg-slate-800/50">
                        <dt class="text-slate-500 dark:text-slate-400">{{ __('shop.features.download') }}</dt>
                        <dd class="font-bold text-slate-800 dark:text-slate-200">{{ $product->is_downloadable ? __('shop.features.yes') : __('shop.features.no') }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3 rounded-xl bg-slate-50 px-4 py-3 dark:bg-slate-800/50">
                        <dt class="text-slate-500 dark:text-slate-400">{{ __('shop.features.demo') }}</dt>
                        <dd class="font-bold">
                            @if ($product->demo_url)
                                <a href="{{ $product->demo_url }}" target="_blank" rel="noopener" class="text-primary-600 hover:underline dark:text-primary-400">{{ __('shop.features.yes') }}</a>
                            @else
                                <span class="text-slate-800 dark:text-slate-200">{{ __('shop.features.no') }}</span>
                            @endif
                        </dd>
                    </div>
                    <div class="flex items-center justify-between gap-3 rounded-xl bg-slate-50 px-4 py-3 dark:bg-slate-800/50 sm:col-span-2">
                        <dt class="text-slate-500 dark:text-slate-400">{{ __('shop.features.licenses') }}</dt>
                        <dd class="flex flex-wrap justify-end gap-2">
                            @foreach ($licenseLabels as $label)
                                <span class="rounded-full bg-primary-100 px-3 py-1 text-xs font-bold text-primary-700 dark:bg-primary-950/60 dark:text-primary-300">{{ $label }}</span>
                            @endforeach
                        </dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</x-public.layout>
