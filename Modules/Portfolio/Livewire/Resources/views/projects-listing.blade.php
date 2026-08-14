<?php /** @var \Modules\Portfolio\Livewire\Pages\ProjectsListing $__env */ ?>
<div class="space-y-6">

    <!-- Search and Filter -->
    <div class="flex gap-4">
        <input type="text" wire:model.debounce.300ms="search"
               placeholder="جستجو در پروژه‌ها..." class="flex-1 px-4 py-2 border rounded-lg border-slate-300 dark:border-slate-600 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-slate-800 dark:text-white">
        
        <select wire:model="categoryFilter" class="px-4 py-2 border rounded-lg border-slate-300 dark:border-slate-600">
            <option value="">همه دسته‌ها</option>
            @foreach ($categories as $category)
                <option value="{{ $category->slug }}" {{ $categoryFilter === $category->slug ? 'selected' : '' }}>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
    </div>

    <!-- Projects Table -->
    <div class="overflow-x-auto border rounded-lg border-slate-200 dark:border-slate-700">
        <table class="min-w-full">
            <thead>
                <tr class="text-left text-slate-600 dark:text-slate-300">
                    <th class="py-3">عنوان</th>
                    <th class="py-3">دسته‌بندی</th>
                    <th class="py-3">وضعیت</th>
                    <th class="py-3">عملیات</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($projects as $project)
                    <tr class="border-b dark:border-slate-700/50">
                        <td class="py-4">
                            <a href="{{ route('portfolio.projects.show', $project->slug) }}"
                               class="text-primary-600 dark:text-primary-400 hover:underline">
                                {{ Str::limit($project->title, 50) }}
                            </a>
                        </td>
                        <td class="py-4">
                            @if($project->category)
                                <span class="px-2 py-1 text-xs rounded text-slate-500 dark:text-slate-400">
                                    {{ $project->category->name ?? 'بدون دسته' }}
                                </span>
                            @else
                                <span class="px-2 py-1 text-xs rounded text-slate-500 dark:text-slate-400">
                                    بدون دسته
                                </span>
                            @endif
                        </td>
                        <td class="py-4">
                            <span class="px-2 py-1 text-xs rounded" 
                                  class="bg-{{ $project->status === 'published' ? 'green-100' : 'yellow-100' }}-500/20 text-{{ $project->status === 'published' ? 'green-700' : 'amber-700' }}-800 dark:bg-{{ $project->status === 'published' ? 'green-900' : 'amber-900' }}-200 dark:text-{{ $project->status === 'published' ? 'green-300' : 'amber-300' }}">
                                {{ ucfirst($project->status) }}
                            </span>
                        </td>
                        <td class="py-4">
                            <a href="{{ route('portfolio.projects.edit', $project->slug) }}"
                               class="text-sm text-primary-600 dark:text-primary-400 hover:underline">
                                ویرایش
                            </a>
                            <form action="{{ route('portfolio.projects.destroy', $project->id) }}"
                                  method="POST"
                                  class="inline">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="text-sm text-red-600 dark:text-red-400 hover:underline"
                                        onclick="return confirm('آیا از حذف این پروژه اطمینان دارید؟')">
                                    حذف
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if ($projects->hasPages())
        <div class="flex justify-center mt-4">
            @foreach ($projects->links() as $link)
                <a href="{{ $url = $loop->url }}"
                   class="px-4 py-2 mx-2 border rounded-lg border-slate-300 dark:border-slate-600"
                   @if($loop->active)
                       class="text-white bg-primary-600"
                   @endif>
                    {{ $link }}
                </a>
            @endforeach
        </div>
    @endif
</div>