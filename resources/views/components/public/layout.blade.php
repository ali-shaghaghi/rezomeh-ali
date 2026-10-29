<!DOCTYPE html>
<html lang="{{ current_locale() }}" dir="{{ locale_direction() }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ site_favicon_url() }}">

    {{-- سئوی داینامیک — متا + OG + JSON-LD (#ماژول سئو) --}}
    <x-seo::head />

    @include('partials.theme-init')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    @stack('styles')
    {{ $head ?? '' }}
</head>
<body class="font-sans text-slate-800 dark:text-slate-100 transition-all duration-300 min-h-screen">
    <div class="bg-mesh" aria-hidden="true"></div>
    <div class="bg-orbs" aria-hidden="true">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
    </div>

    @include('partials.splash-screen')

    @include('public::partials.header')

    <main class="relative max-w-5xl mx-auto px-4 sm:px-6 pt-28 pb-20 overflow-x-clip">
        {{ $slot }}
    </main>

    {{-- بنر تبلیغاتی بی‌پایان — قبل از فوتر، روی همه صفحات عمومی --}}
    @include('public::partials.promo-banner')

    @include('public::partials.footer')

    {{-- کشوی سبد خرید — روی همه صفحات عمومی --}}
    @livewire('public.cart-drawer')

    {{-- ویجت شناور تماس — روی همه صفحات --}}
    @livewire('contact.floating-widget')

    @stack('scripts')
    {{-- ponytail: aria-labelهای منوی موبایل دوزبانه — JS از window.__localeText می‌خواند --}}
    <script>
        window.__localeText = {
            openMenu: @json(__('public.header.open_menu')),
            closeMenu: @json(__('public.header.close_menu')),
        };
    </script>
</body>
</html>
