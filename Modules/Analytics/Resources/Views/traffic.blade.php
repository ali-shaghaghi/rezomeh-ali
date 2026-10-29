@extends('adminpanel::layouts.master')

@section('title', 'گزارش ترافیک')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">گزارش ترافیک کاربران</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">کاربران آنلاین، فعالیت‌ها و صفحات پر بازدید</p>
    </div>

    <livewire:analytics.traffic />
@endsection
