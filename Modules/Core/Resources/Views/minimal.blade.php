<!DOCTYPE html>
<html lang="fa" dir="rtl" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>صفحه یافت نشد | علی شقاقی</title>
    <link rel="icon" href="{{ site_favicon_url() }}">
    @include('partials.theme-init')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="font-sans text-slate-800 dark:text-slate-100 transition-all duration-300 min-h-screen flex items-center justify-center">
    <div class="bg-mesh" aria-hidden="true"></div>
    <div class="bg-orbs" aria-hidden="true">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
    </div>

    @include('partials.splash-screen')

    <main class="relative z-10 w-full max-w-lg mx-auto px-4 py-16">
        <div class="glass-card p-8 sm:p-12 text-center">
            {{-- کد وضعیت --}}
            <p class="text-7xl sm:text-8xl font-black gradient-text leading-none" aria-hidden="true">
                {{ str_replace(['0', '4'], ['۰', '۴'], (string) $statusCode) }}
            </p>

            <h1 class="mt-6 text-2xl font-extrabold text-slate-900 dark:text-white">
                صفحه موردنظر یافت نشد
            </h1>

            <p class="mt-3 text-sm text-slate-500 dark:text-slate-400 leading-7">
                {{ $message }}
            </p>

            {{-- دکمه‌های بازگشت --}}
            <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3">
                <button type="button" onclick="if (window.history.length > 1) { window.history.back(); } else { window.location.href = '{{ url('/') }}'; }"
                        class="btn-outline !px-6 !py-3 !text-sm w-full sm:w-auto">
                    <span class="inline-flex items-center gap-2">
                        <x-icon name="chevronRight" />
                        بازگشت به صفحه قبلی
                    </span>
                </button>
                <a href="{{ url('/') }}"
                   class="btn-primary !px-6 !py-3 !text-sm w-full sm:w-auto">
                    <span class="inline-flex items-center gap-2">
                        <x-icon name="home" />
                        بازگشت به صفحه اصلی
                    </span>
                </a>
            </div>
        </div>
    </main>
</body>
</html>
