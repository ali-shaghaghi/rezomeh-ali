@props([
    'title' => '',
    'count' => 0,
    'suffix' => '',
    'icon' => 'dashboard',
    'color' => 'primary',
    'trend' => 0,
    'delay' => 0,
])

@php
    $colorClasses = match($color) {
        'primary' => 'bg-primary-100 text-primary-600 dark:bg-primary-900/40',
        'violet' => 'bg-violet-100 text-violet-600 dark:bg-violet-900/40',
        'cyan' => 'bg-cyan-100 text-cyan-600 dark:bg-cyan-900/40',
        'amber' => 'bg-amber-100 text-amber-600 dark:bg-amber-900/40',
        'emerald' => 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/40',
        'rose' => 'bg-rose-100 text-rose-600 dark:bg-rose-900/40',
        default => 'bg-primary-100 text-primary-600 dark:bg-primary-900/40',
    };

    $bgGlow = match($color) {
        'primary' => 'bg-primary-500/10',
        'violet' => 'bg-violet-500/10',
        'cyan' => 'bg-cyan-500/10',
        'amber' => 'bg-amber-500/10',
        'emerald' => 'bg-emerald-500/10',
        'rose' => 'bg-rose-500/10',
        default => 'bg-primary-500/10',
    };
@endphp

<article class="stat-card opacity-0-start observe-in group {{ $attributes->get('class', '') }}">
    <div class="absolute -left-8 -top-8 h-32 w-32 rounded-full {{ $bgGlow }} transition-transform duration-700 group-hover:scale-150"></div>
    <div class="relative flex items-start justify-between">
        <div>
            <p class="text-sm text-slate-500 dark:text-slate-400">{{ $title }}</p>
            <p class="mt-2 text-2xl font-bold" data-count="{{ $count }}" data-suffix="{{ $suffix }}" data-delay="{{ $delay }}"></p>
            @if($trend !== 0)
                <span class="mt-2 inline-flex items-center gap-1 rounded-full {{ $trend > 0 ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300' }}">
                    <x-icon :name="$trend > 0 ? 'trendUp' : 'trendDown'" size="4" />
                    {{ abs($trend) }}٪
                </span>
            @else
                <span class="mt-2 inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600 dark:bg-slate-700 dark:text-slate-300">
                    <x-icon name="trendUp" size="4" />
                    ۰٪
                </span>
            @endif
        </div>
        <span class="flex h-12 w-12 items-center justify-center rounded-2xl {{ $colorClasses }} transition-transform duration-500 group-hover:rotate-6 group-hover:scale-110">
            <x-icon :name="$icon" size="6" />
        </span>
    </div>
</article>