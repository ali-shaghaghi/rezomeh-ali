{{--
    پارشال مشترک صفحه‌بندی Tailwind (RTL فارسی) — هم برای Livewire و هم لینک‌های معمولی
    Livewire این فایل را با wire:click رندر می‌کند؛ لاراول هم برای LengthAwarePaginator معمولی
--}}
@php
    // اسکرول به بالای لیست بعد از تغییر صفحه
    if (! isset($scrollTo)) {
        $scrollTo = 'body';
    }

    $scrollIntoViewJs = $scrollTo !== false
        ? "(\$el.closest('{$scrollTo}') || document.querySelector('{$scrollTo}')).scrollIntoView({ behavior: 'smooth', block: 'start' })"
        : '';

    $isFirst = $paginator->onFirstPage();
    $hasMore = $paginator->hasMorePages();
    $pageName = $paginator->getPageName();
@endphp

@if ($paginator->hasPages())
    <nav role="navigation" aria-label="صفحه‌بندی" class="flex flex-col items-center justify-between gap-3 mt-6 sm:flex-row">
        {{-- خلاصه نتایج --}}
        <p class="text-xs text-slate-500 dark:text-slate-400">
            @if ($paginator->firstItem())
                نمایش
                <span class="font-bold text-primary-600 dark:text-primary-400">{{ number_format($paginator->firstItem()) }}</span>
                تا
                <span class="font-bold text-primary-600 dark:text-primary-400">{{ number_format($paginator->lastItem()) }}</span>
                از
                <span class="font-bold">{{ number_format($paginator->total()) }}</span>
                مورد
            @else
                {{ number_format($paginator->total()) }} مورد
            @endif
        </p>

        {{-- دکمه‌ها --}}
        <div class="flex items-center gap-1.5">
            {{-- بعدی (اول از راست در RTL) --}}
            @if ($hasMore)
                @if (!empty($__livewire))
                    <button type="button" wire:click="nextPage('{{ $pageName }}')"
                            x-on:click="{{ $scrollIntoViewJs }}"
                            class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-slate-600 shadow-sm transition hover:border-primary-300 hover:text-primary-600 dark:border-slate-600 dark:bg-surface-800 dark:text-slate-300 dark:hover:border-primary-500 dark:hover:text-primary-400">
                        <span>بعدی</span>
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
                    </button>
                @else
                    <a href="{{ $paginator->nextPageUrl() }}"
                       class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-slate-600 shadow-sm transition hover:border-primary-300 hover:text-primary-600 dark:border-slate-600 dark:bg-surface-800 dark:text-slate-300 dark:hover:border-primary-500 dark:hover:text-primary-400">
                        <span>بعدی</span>
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
                    </a>
                @endif
            @else
                <span class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-medium text-slate-300 cursor-not-allowed dark:border-slate-700 dark:bg-surface-800 dark:text-slate-600">
                    <span>بعدی</span>
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
                </span>
            @endif

            {{-- شماره صفحات --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="inline-flex items-center px-2 py-2 text-xs font-bold text-slate-400 dark:text-slate-500" aria-disabled="true">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span wire:key="page-{{ $page }}" aria-current="page"
                                  class="inline-flex items-center justify-center min-w-[2rem] h-8 px-2 py-1.5 text-xs font-bold text-white rounded-xl bg-gradient-to-l from-primary-500 to-primary-600 shadow-md shadow-primary-500/30">
                                {{ number_format($page) }}
                            </span>
                        @elseif (!empty($__livewire))
                            <button type="button" wire:click="gotoPage({{ $page }}, '{{ $pageName }}')" wire:key="page-{{ $page }}"
                                    class="inline-flex items-center justify-center min-w-[2rem] h-8 px-2 py-1.5 text-xs font-medium text-slate-600 rounded-xl border border-slate-200 bg-white transition hover:border-primary-300 hover:text-primary-600 dark:border-slate-600 dark:bg-surface-800 dark:text-slate-300 dark:hover:border-primary-500 dark:hover:text-primary-400">
                                {{ number_format($page) }}
                            </button>
                        @else
                            <a href="{{ $url }}" wire:key="page-{{ $page }}"
                               class="inline-flex items-center justify-center min-w-[2rem] h-8 px-2 py-1.5 text-xs font-medium text-slate-600 rounded-xl border border-slate-200 bg-white transition hover:border-primary-300 hover:text-primary-600 dark:border-slate-600 dark:bg-surface-800 dark:text-slate-300 dark:hover:border-primary-500 dark:hover:text-primary-400">
                                {{ number_format($page) }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- قبلی (آخر از راست در RTL = سمت چپ) --}}
            @if ($isFirst)
                <span class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-medium text-slate-300 cursor-not-allowed dark:border-slate-700 dark:bg-surface-800 dark:text-slate-600">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    <span>قبلی</span>
                </span>
            @elseif (!empty($__livewire))
                <button type="button" wire:click="previousPage('{{ $pageName }}')"
                        x-on:click="{{ $scrollIntoViewJs }}"
                        class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-slate-600 shadow-sm transition hover:border-primary-300 hover:text-primary-600 dark:border-slate-600 dark:bg-surface-800 dark:text-slate-300 dark:hover:border-primary-500 dark:hover:text-primary-400">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    <span>قبلی</span>
                </button>
            @else
                <a href="{{ $paginator->previousPageUrl() }}"
                   class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-slate-600 shadow-sm transition hover:border-primary-300 hover:text-primary-600 dark:border-slate-600 dark:bg-surface-800 dark:text-slate-300 dark:hover:border-primary-500 dark:hover:text-primary-400">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    <span>قبلی</span>
                </a>
            @endif
        </div>
    </nav>
@endif
