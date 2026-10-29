@extends('adminpanel::layouts.master')

@section('title', 'گزارش‌ها')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">داشبورد گزارش‌ها</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">روند درآمد، سفارشات، کاربران و وضعیت کلی سیستم</p>
    </div>

    <livewire:analytics.overview />
@endsection
