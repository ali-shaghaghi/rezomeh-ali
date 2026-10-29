@extends('adminpanel::layouts.master')

@section('title', 'گزارش فروش')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">گزارش فروش</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">پرداخت‌های موفق و محصولات پرفروش</p>
    </div>

    <livewire:analytics.sales />
@endsection
