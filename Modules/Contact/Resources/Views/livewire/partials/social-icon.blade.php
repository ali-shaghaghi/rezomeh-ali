{{--
    آیکون شبکه اجتماعی ویجت تماس.
    از کامپوننت مرکزی <x-social> استفاده می‌کند (همان آیکون‌های رزومه/فوتر)
    تا یک منبع واحد برای مسیرهای SVG داشته باشیم.
    رنگ هر برند به‌صورت متغیر CSS برای حالت hover تنظیم می‌شود.
--}}
@php
    $brandColors = [
        'whatsapp'   => '#25D366',
        'telegram'   => '#26A5E4',
        'instagram'  => '#E4405F',
        'linkedin'   => '#0A66C2',
        'github'     => '#181717',
        'rubika'     => '#38AD9C',
        'bale'       => '#00B894',
        'eita'       => '#F15A24',
        'phone'      => '#10B981',
        'mail'       => '#6366F1',
        'globe'      => '#64748B',
    ];
    $color = $brandColors[$key] ?? '#64748B';
@endphp

<span class="flex items-center justify-center" style="--social-color: {{ $color }}">
    <x-social :name="$key" class="h-6 w-6 text-slate-600 transition-colors duration-200 group-hover/link:text-[var(--social-color)] dark:text-slate-300" />
</span>
