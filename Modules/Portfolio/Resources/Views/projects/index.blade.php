<?php /** @var \Modules\Portfolio\Livewire\Pages\ProjectsListing $__env */ ?>
@extends('admin::layouts.master')

@section('title', 'پروژه‌ها')

@section('content')
<div class="pace">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold">لیست پروژه‌ها</h2>
        <a href="{{ route('portfolio.projects.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 text-white rounded-lg bg-primary-600 hover:bg-primary-700">
            <x-icon name="plus" />
            افزودن پروژه جدید
        </a>
    </div>

    <projects-listing />
</div>
@endsection