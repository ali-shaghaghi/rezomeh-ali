@props(['code' => 'fa', 'class' => 'inline-block'])
@php
    // نگاشت کد زبان → پرچم کشور متناظر (فارسی = ایران، انگلیسی = بریتانیا)
    $flagMap = ['fa' => 'ir', 'ir' => 'ir', 'en' => 'gb', 'gb' => 'gb'];
    $flag = $flagMap[$code] ?? 'ir';
    $uid = 'flag-'.uniqid();
@endphp

@switch($flag)
    @case('ir')
        {{-- پرچم ایران: سه‌رنگ سبز/سفید/قرمز + نشان مرکزی --}}
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 60 40" role="img" aria-label="پرچم ایران"
             class="{{ $class }} rounded-[3px] overflow-hidden ring-1 ring-black/10 dark:ring-white/15">
            <rect width="60" height="13.33" fill="#239f40"/>
            <rect y="13.33" width="60" height="13.34" fill="#ffffff"/>
            <rect y="26.67" width="60" height="13.33" fill="#da0000"/>
            {{-- نشان ساده‌شده مرکز --}}
            <g transform="translate(30 20)">
                <circle r="5" fill="#da0000"/>
                <circle r="3.1" fill="#ffffff"/>
                <path d="M0 -2.6 C1.1 -0.9 1.1 0.9 0 2.6 C-1.1 0.9 -1.1 -0.9 0 -2.6 Z" fill="#239f40"/>
            </g>
        </svg>
        @break
    @case('gb')
        {{-- پرچم بریتانیا (Union Jack) --}}
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 60 30" role="img" aria-label="United Kingdom flag"
             class="{{ $class }} rounded-[3px] overflow-hidden ring-1 ring-black/10 dark:ring-white/15">
            <clipPath id="{{ $uid }}">
                <path d="M30 15 h30 v15 z v15 h-30 z h-30 v-15 z v-15 h30 z"/>
            </clipPath>
            <rect width="60" height="30" fill="#012169"/>
            <path d="M0 0 L60 30 M60 0 L0 30" stroke="#ffffff" stroke-width="6"/>
            <path d="M0 0 L60 30 M60 0 L0 30" stroke="#c8102e" stroke-width="4" clip-path="url(#{{ $uid }})"/>
            <path d="M30 0 v30 M0 15 h60" stroke="#ffffff" stroke-width="10"/>
            <path d="M30 0 v30 M0 15 h60" stroke="#c8102e" stroke-width="6"/>
        </svg>
        @break
@endswitch