<?php /** @var \Modules\Portfolio\Http\Controllers\ProjectsController $__env */ ?>
@extends('admin::layouts.master')

@section('title', 'ویرایش پروژه')

@section('content')
<div class="pace">
    <div class="mb-6">
        <h2 class="mb-4 text-2xl font-bold">ویرایش پروژه</h2>
    </div>

    <form wire:submit.prevent="update" enctype="multipart/form-data" class="space-y-6">
        <input type="hidden" wire:model="categoryFilter">
        <input type="hidden" wire:model="projectId" value="{{ $project->id }}">

        <input type="hidden" wire:model="originalThumbnail" value="{{ $project->thumbnail }}">
        <input type="hidden" wire:model="originalCoverImage" value="{{ $project->cover_image }}">

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div>
                <label class="block mb-2 text-sm font-medium text-slate-700 dark:text-slate-300">عنوان <span class="text-red-500">*</span></label>
                <input wire:model.debounce.300ms="title" type="text" class="w-full px-4 py-2 border rounded-lg border-slate-300 dark:border-slate-600 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-slate-800 dark:text-white" placeholder="{{ $project->title }}" value="{{ $project->title }}">
            </div>

            <div>
                <label class="block mb-2 text-sm font-medium text-slate-700 dark:text-slate-300">Slug <span class="text-red-500">*</span></label>
                <input wire:model.debounce.300ms="slug" type="text" class="w-full px-4 py-2 border rounded-lg border-slate-300 dark:border-slate-600 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-slate-800 dark:text-white" placeholder="project-name" value="{{ $project->slug }}">
            </div>
        </div>

        <div class="col-span-2">
            <label class="block mb-2 text-sm font-medium text-slate-700 dark:text-slate-300">توضیحات <span class="text-red-500">*</span></label>
            <textarea wire:model.debounce.300ms="description" rows="4" class="w-full px-4 py-2 border rounded-lg border-slate-300 dark:border-slate-600 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-slate-800 dark:text-white" placeholder="توضیحات پروژه...">{{ $project->description }}</textarea>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div>
                <label class="block mb-2 text-sm font-medium text-slate-700 dark:text-slate-300">تصویر کوچک (Thumbnail) <span class="text-red-500">*</span></label>
                <input wire:model="thumbnail" type="file" class="w-full px-4 py-2 border rounded-lg border-slate-300 dark:border-slate-600 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-slate-800 dark:text-white" accept="image/*">
                @if($project->thumbnail)
                    <div class="mt-2">
                        <img src="{{ asset('storage/' . $project->thumbnail) }}" alt="Thumbnail" class="max-w-xs mt-2 rounded">
                    </div>
                @endif
            </div>

            <div>
                <label class="block mb-2 text-sm font-medium text-slate-700 dark:text-slate-300">تصویر کاور <span class="text-red-500">*</span></label>
                <input wire:model="cover_image" type="file" class="w-full px-4 py-2 border rounded-lg border-slate-300 dark:border-slate-600 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-slate-800 dark:text-white" accept="image/*">
                @if($project->cover_image)
                    <div class="mt-2">
                        <img src="{{ asset('storage/' . $project->cover_image) }}" alt="Cover Image" class="max-w-xs mt-2 rounded">
                    </div>
                @endif
            </div>
        </div>

        <div>
            <label class="block mb-2 text-sm font-medium text-slate-700 dark:text-slate-300">دسته‌بندی <span class="text-red-500">*</span></label>
            <select wire:model="category_id" class="w-full px-4 py-2 border rounded-lg border-slate-300 dark:border-slate-600 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-slate-800 dark:text-white">
                <option value="">انتخاب دسته‌بندی</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" {{ $category_id === $category->id || $project->category->contains($category) ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block mb-2 text-sm font-medium text-slate-700 dark:text-slate-300">تکنولوژی‌ها <span class="text-red-500">*</span></label>
            <div class="space-y-2">
                @foreach ($technologies as $technology)
                    <label class="flex items-center gap-2 px-3 py-1 border rounded-lg cursor-pointer border-slate-300 dark:border-slate-600"
                           wire:click="$event.target.checked ? $wire.self.technologies.push('{{ $technology->id }}') : $wire.self.technologies.splice($wire.self.technologies.indexOf('{{ $technology->id }}'), 1)">
                        <input type="checkbox" wire:model="technologies" value="{{ $technology->id }}" class="hidden" {{ in_array($technology->id, $project->technologies->pluck('id')->toArray()) ? 'checked' : '' }}>
                        <span>{{ $technology->name }}</span>
                    </label>
                @endforeach
            </div>
            <input type="hidden" wire:model="technologies">
        </div>

        <div>
            <label class="block mb-2 text-sm font-medium text-slate-700 dark:text-slate-300">وضعیت <span class="text-red-500">*</span></label>
            <select wire:model="status" class="w-full px-4 py-2 border rounded-lg border-slate-300 dark:border-slate-600 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-slate-800 dark:text-white">
                <option value="draft" {{ $project->status === 'draft' ? 'selected' : '' }}>پیش‌نویس</option>
                <option value="published" {{ $project->status === 'published' ? 'selected' : '' }}>منتشر شده</option>
                <option value="archived" {{ $project->status === 'archived' ? 'selected' : '' }}>آرشیو شده</option>
            </select>
        </div>

        <div class="flex gap-3">
            <button type="submit"
                    class="flex-1 px-4 py-2 text-white transition-colors rounded-lg bg-primary-600 hover:bg-primary-700">
                به‌روزرسانی پروژه
            </button>
            <a href="{{ route('portfolio.projects.index') }}"
               class="flex-1 px-4 py-2 transition-colors rounded-lg bg-slate-200 text-slate-700 dark:text-slate-300 hover:bg-slate-300">
                انصراف
            </a>
        </div>
    </form>
</div>
@endsection