@props(['routeName' => null, 'model' => null, 'meta' => null])

@php
    // کامپوننت سئو — همه لایوت‌ها با <x-seo.head /> متا و JSON-LD می‌گیرند (#ماژول سئو)
    $routeName = $routeName ?? request()->route()?->getName() ?? '';
    $service = app(\Modules\SEO\Services\SeoService::class);
    $meta = $meta ?? $service->metaFor($routeName);
    $schemas = $service->jsonLd($routeName, $meta, $model);
    // og:image همیشه باید وجود داشته باشد (اشتراک‌گذاری در شبکه‌ها + Rich Results) —
    // زنجیره جایگزین: متای صفحه → لوگوی سایت از storage → لوگوی پیش‌فرض img/
    $ogImage = $meta['og_image']
        ? asset('storage/'.$meta['og_image'])
        : (\Modules\Settings\Models\Setting::get('site_logo')
            ? asset('storage/'.\Modules\Settings\Models\Setting::get('site_logo'))
            : asset('img/logo.png'));
    // تأیید سرچ کنسول گوگل — اگر ادمین کد را در تنظیمات گذاشته باشد
    $googleVerification = \Modules\Settings\Models\Setting::get('google_site_verification', '');
@endphp

<title>{{ $meta['title'] }}</title>
<meta name="description" content="{{ \Illuminate\Support\Str::limit($meta['description'], 160, '') }}">
@if ($meta['keywords'])
    <meta name="keywords" content="{{ $meta['keywords'] }}">
@endif

{{-- canonical + hreflang دوزبانه --}}
<link rel="canonical" href="{{ $meta['canonical'] }}">
<link rel="alternate" hreflang="fa" href="{{ url()->current() }}">
<meta property="og:image" content="{{ $ogImage }}">

@if ($meta['noindex'])
    <meta name="robots" content="noindex, nofollow">
@else
    <meta name="robots" content="index, follow, max-image-preview:large">
@endif

{{-- تأیید Google Search Console --}}
@if ($googleVerification)
    <meta name="google-site-verification" content="{{ $googleVerification }}">
@endif

{{-- Open Graph + Twitter Card — برای اشتراک‌گذاری و AI crawlers --}}
<meta property="og:type" content="website">
<meta property="og:title" content="{{ $meta['title'] }}">
<meta property="og:description" content="{{ \Illuminate\Support\Str::limit($meta['description'], 200, '') }}">
<meta property="og:url" content="{{ $meta['canonical'] }}">
<meta property="og:site_name" content="{{ \Modules\Settings\Models\Setting::get('site_title', config('seo.brand.name')) }}">
<meta property="og:locale" content="fa_IR">
<meta property="og:locale:alternate" content="en_US">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:image" content="{{ $ogImage }}">
<meta name="twitter:title" content="{{ $meta['title'] }}">
<meta name="twitter:description" content="{{ \Illuminate\Support\Str::limit($meta['description'], 200, '') }}">

{{-- JSON-LD Schema — برای Google و AI search engines --}}
@foreach ($schemas as $schema)
    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endforeach
