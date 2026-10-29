@extends('adminpanel::layouts.master')

@section('title', 'قالب‌های پیامک')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">قالب‌های پیامک</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">مدیریت قالب‌ها با متغیرهای جایگزین — مثال: «سلام {name}»</p>
    </div>

    <livewire:sms.templates />
@endsection