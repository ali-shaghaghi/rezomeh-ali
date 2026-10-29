{{-- ============================================================
     بنر تبلیغاتی بی‌پایان (مارکی) — قبل از فوتر، روی همه صفحات عمومی
     • ارتفاع ثابت ≤ 100px (h-20 موبایل / h-24 دسکتاپ)
     • حرکت روان و بی‌پایان مثل ریلی: محتوا دوبار تکرار می‌شود و
       هر بار که یک کپی کامل خارج شد، کپی دوم دقیقاً جای آن است —
       بنابراین مخاطب هرگز لحظه «پایان» را نمی‌بیند (حلقه seamless).
     • hover → توقف حرکت (خوانایی) | prefers-reduced-motion → ثابت
     ============================================================ --}}
<section class="marquee-banner" aria-label="{{ __('public.promo.banner_aria') }}">
    <a href="{{ route('user.projectdev.create') }}"
       class="marquee-track group"
       tabindex="-1"
       aria-hidden="true">
        {{-- دو کپی یکسان — کلید حلقه بی‌پایان --}}
        @foreach ([1, 2] as $copy)
            <div class="marquee-content" {{ $copy === 2 ? 'aria-hidden' : '' }}>
                @foreach ([
                    ['icon' => '🚀', 'text' => __('public.promo.item_projects')],
                    ['icon' => '✨', 'text' => __('public.promo.item_products')],
                    ['icon' => '🎯', 'text' => __('public.promo.item_consultation')],
                    ['icon' => '⚡', 'text' => __('public.promo.item_delivery')],
                    ['icon' => '💬', 'text' => __('public.promo.item_support')],
                ] as $item)
                    <span class="marquee-item">
                        <span class="marquee-item-icon">{{ $item['icon'] }}</span>
                        <span>{{ $item['text'] }}</span>
                    </span>
                    <span class="marquee-sep" aria-hidden="true">✦</span>
                @endforeach
            </div>
        @endforeach
    </a>

    {{-- CTA شناور وسط — متن اصلی قابل خواندن و کلیک‌پذیر --}}
    <a href="{{ route('user.projectdev.create') }}"
       class="marquee-cta">
        <span class="marquee-cta-dot" aria-hidden="true"></span>
        {{ __('public.promo.cta') }}
    </a>
</section>
