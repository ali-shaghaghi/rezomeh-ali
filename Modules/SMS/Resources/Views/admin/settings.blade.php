@extends('adminpanel::layouts.master')

@section('title', 'تنظیمات پیامک')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">پیامک</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">تنظیمات ارائه‌دهنده پیامکی سایت</p>
    </div>

    <livewire:sms.settings />
@endsection