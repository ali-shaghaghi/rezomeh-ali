@props(['type' => 'success', 'message' => '', 'title' => ''])

@php
    $typeClasses = match($type) {
        'success' => 'bg-emerald-50 border-emerald-200 text-emerald-700 dark:bg-emerald-900/30 dark:border-emerald-700 dark:text-emerald-300',
        'error' => 'bg-rose-50 border-rose-200 text-rose-700 dark:bg-rose-900/30 dark:border-rose-700 dark:text-rose-300',
        'warning' => 'bg-amber-50 border-amber-200 text-amber-700 dark:bg-amber-900/30 dark:border-amber-700 dark:text-amber-300',
        'info' => 'bg-primary-50 border-primary-200 text-primary-700 dark:bg-primary-900/30 dark:border-primary-700 dark:text-primary-300',
        default => 'bg-slate-50 border-slate-200 text-slate-700 dark:bg-slate-900/30 dark:border-slate-700 dark:text-slate-300',
    };
@endphp

<div class="mb-4 rounded-xl border {{ $typeClasses }} p-4" role="alert">
    @if($title)
        <p class="mb-1 text-sm font-bold">{{ $title }}</p>
    @endif
    <p class="text-sm">{{ $message }}</p>
</div>