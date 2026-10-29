@extends('adminpanel::layouts.master')

@section('title', 'گزارش محتوا')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">گزارش محتوا</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">مقالات پربازدید، محصولات و مشاوره‌ها</p>
    </div>

    <livewire:analytics.content />
@endsection
