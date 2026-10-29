@extends('adminpanel::layouts.master')

@section('title', 'رزروهای مشاوره')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">رزروهای مشاوره</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">مدیریت رزروها — تایید، لغو و تغییر وضعیت</p>
    </div>

    <livewire:consultation.admin.consultations-index />
@endsection