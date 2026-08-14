<?php /** @var \Modules\Portfolio\Http\Controllers\ProjectsController $__env */ ?>
@extends('admin::layouts.master')

@section('title', $project->title)

@section('content')
<div class="pace">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold">{{ $project->title }}</h2>
        <div class="flex gap-2">
            <a href="{{ route('portfolio.projects.edit', $project->id) }}"
               class="inline-flex items-center gap-2 px-4 py-2 text-white rounded-lg bg-primary-600 hover:bg-primary-700">
                <x-icon name="edit" />
                ویرایش
            </a>
            <a href="{{ route('portfolio.projects.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-slate-200 text-slate-700 dark:text-slate-300 hover:bg-slate-300">
                بازگشت
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            @if($project->cover_image)
                <div class="overflow-hidden border rounded-xl border-slate-200 dark:border-slate-700">
                    <img src="{{ asset('storage/' . $project->cover_image) }}" alt="{{ $project->title }}" class="object-cover w-full h-64">
                </div>
            @endif

            <div class="p-6 border rounded-xl border-slate-200 dark:border-slate-700">
                <h3 class="mb-4 text-lg font-bold">توضیحات پروژه</h3>
                <p class="leading-relaxed text-slate-600 dark:text-slate-300">{{ $project->description }}</p>
            </div>

            @if($project->seo_title || $project->seo_description)
                <div class="p-6 border rounded-xl border-slate-200 dark:border-slate-700">
                    <h3 class="mb-4 text-lg font-bold">SEO</h3>
                    <div class="space-y-2">
                        @if($project->seo_title)
                            <p><strong>عنوان SEO:</strong> {{ $project->seo_title }}</p>
                        @endif
                        @if($project->seo_description)
                            <p><strong>توضیحات SEO:</strong> {{ $project->seo_description }}</p>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <div class="space-y-6">
            <div class="p-6 border rounded-xl border-slate-200 dark:border-slate-700">
                <h3 class="mb-4 text-lg font-bold">اطلاعات</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-slate-500">وضعیت:</span>
                        <span class="px-2 py-1 text-xs text-green-800 bg-green-100 rounded dark:bg-green-900/30 dark:text-green-300">
                            {{ ucfirst($project->status) }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">ویژه:</span>
                        <span>{{ $project->featured ? 'بله' : 'خیر' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">دسته‌بندی:</span>
                        <span>{{ $project->category->name ?? 'بدون دسته' }}</span>
                    </div>
                </div>
            </div>

            @if($project->technologies->isNotEmpty())
                <div class="p-6 border rounded-xl border-slate-200 dark:border-slate-700">
                    <h3 class="mb-4 text-lg font-bold">تکنولوژی‌ها</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($project->technologies as $tech)
                            <span class="px-3 py-1 text-sm rounded-lg text-slate-700 bg-slate-100 dark:bg-slate-800 dark:text-slate-300">
                                {{ $tech->name }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="p-6 border rounded-xl border-slate-200 dark:border-slate-700">
                <h3 class="mb-4 text-lg font-bold">لینک‌ها</h3>
                <div class="space-y-2">
                    @if($project->github_url)
                        <a href="{{ $project->github_url }}" target="_blank"
                           class="flex items-center gap-2 text-primary-600 dark:text-primary-400 hover:underline">
                            <x-icon name="github" />
                            GitHub
                        </a>
                    @endif
                    @if($project->demo_url)
                        <a href="{{ $project->demo_url }}" target="_blank"
                           class="flex items-center gap-2 text-primary-600 dark:text-primary-400 hover:underline">
                            <x-icon name="globe" />
                            دمو
                        </a>
                    @endif
                    @if($project->video_url)
                        <a href="{{ $project->video_url }}" target="_blank"
                           class="flex items-center gap-2 text-primary-600 dark:text-primary-400 hover:underline">
                            <x-icon name="video" />
                            ویدئو
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection