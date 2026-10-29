{{-- sitemap.xml داینامیک — دوزبانه با hreflang (#ماژول سئو) --}}
<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>

<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:xhtml="http://www.w3.org/1999/xhtml">
@foreach ($urls as $url)
    <url>
        <loc>{{ $url['loc'] }}</loc>
        @foreach (($url['alternates'] ?? []) as $lang => $alt)
            <xhtml:link rel="alternate" hreflang="{{ $lang }}" href="{{ $alt }}" />
        @endforeach
        @if (! empty($url['lastmod']))
            <lastmod>{{ $url['lastmod'] }}</lastmod>
        @endif
        @if (! empty($url['changefreq']))
            <changefreq>{{ $url['changefreq'] }}</changefreq>
        @endif
        @if (isset($url['priority']))
            <priority>{{ number_format($url['priority'], 1) }}</priority>
        @endif
    </url>
@endforeach
</urlset>
