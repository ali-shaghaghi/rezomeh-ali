@extends('admin::layouts.master')

@section('title', 'به زودی')

@section('content')
    <div class="flex items-center justify-center min-h-[60vh]">
        <div class="text-center">
            <div class="mb-6">
                <x-icon name="settings" size="12" class="text-slate-400 mx-auto" />
            </div>
            <h2 class="text-2xl font-bold text-slate-700 dark:text-slate-300 mb-2">این بخش به زودی اضافه خواهد شد</h2>
            <p class="text-slate-500 dark:text-slate-400">در حال توسعه...</p>
        </div>
    </div>
@endsection