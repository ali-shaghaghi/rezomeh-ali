<x-public.layout :title="$category ? $category->name : __('public.nav.portfolio')">
    {{-- ===== هدر نمونه‌کارها + جستجو ===== --}}
    <section class="mb-10 scroll-mt-28">
        <div class="reveal">
            <div class="section-badge">{{ __('public.nav.portfolio') }}</div>
            <h1 class="section-title !mb-3">{{ $category?->name ?? __('public.projects.all_projects') }}</h1>
            <p class="section-subtitle !mb-8">{{ $category?->description ?? __('public.projects.subtitle') }}</p>

            {{-- جستجو — موبایل: تمام‌عرض استک | دسکتاپ: اینپوت + دکمه در یک ردیف --}}
            <form method="GET" action="{{ route('public.portfolio.index') }}">
                @if ($category)
                    <input type="hidden" name="category" value="{{ $category->id }}">
                @endif
                <div class="glass-card flex flex-col gap-2 rounded-2xl p-2 sm:flex-row sm:items-center">
                    <div class="relative flex-1">
                        <span class="pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        </span>
                        <input type="search" name="q" value="{{ $search }}" placeholder="{{ __('public.common.search') }}..."
                               class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 pr-11 pl-4 text-sm transition focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-surface-800">
                    </div>
                    <button type="submit" class="btn-primary w-full !px-6 !py-3 !text-xs sm:w-auto sm:shrink-0">{{ __('public.common.search') }}</button>
                </div>
            </form>

            {{-- فیلتر دسته‌بندی --}}
            @if ($categories->count())
                <div class="flex flex-wrap gap-2 mt-6">
                    <a href="{{ route('public.portfolio.index') }}{{ $search ? '?q='.urlencode($search) : '' }}"
                       class="skill-tag !py-1.5 !px-4 !text-xs {{ ! $category ? 'text-primary-600 dark:text-primary-400 border-primary-300 dark:border-primary-600 bg-primary-50 dark:bg-primary-950/50' : '' }}">
                        {{ __('public.projects.all_projects') }}
                    </a>
                    @foreach ($categories as $cat)
                        <a href="{{ route('public.portfolio.index', array_filter(['category' => $cat->id, 'q' => $search !== '' ? $search : null])) }}"
                           class="skill-tag !py-1.5 !px-4 !text-xs {{ $category?->id === $cat->id ? 'text-primary-600 dark:text-primary-400 border-primary-300 dark:border-primary-600 bg-primary-50 dark:bg-primary-950/50' : '' }}">
                            {{ $cat->name }}
                        </a>
                    @endforeach
                </div>
            @endif

            @if ($search !== '')
                <p class="mt-4 text-sm text-slate-500 dark:text-slate-400">
                    {{ __('public.common.search') }}: «<span class="font-bold text-primary-600 dark:text-primary-400">{{ $search }}</span>» —
                    {{ fa_digits($projects->total()) }}
                </p>
            @endif
        </div>
    </section>

    {{-- لیست نمونه‌کارها --}}
    <section>
        @if ($projects->count())
            <div class="grid gap-6 sm:grid-cols-2">
                @foreach ($projects as $project)
                    <article class="p-6 project-card group reveal glass-card">
                        <div class="project-image-wrap h-60 rounded-xl">
                            @if ($project->localizedCover())
                                <img src="{{ featured_image_url($project->localizedCover()) }}" alt="{{ $project->localizedTitle() }}" class="project-image" loading="lazy">
                            @else
                                <div class="flex items-center justify-center project-image bg-gradient-to-br from-primary-500 via-primary-600 to-accent-500">
                                    <span class="px-4 text-xl font-bold text-center text-white">{{ $project->localizedTitle() }}</span>
                                </div>
                            @endif
                            @if ($project->featured)
                                <span class="absolute z-10 px-3 py-1 text-xs font-bold text-white rounded-full shadow-lg top-4 right-4 bg-primary-600/95 backdrop-blur-sm">★</span>
                            @endif
                            <div class="project-overlay">
                                <div class="flex flex-wrap gap-1.5 mb-3">
                                    @foreach ($project->technologies->take(4) as $tech)
                                        <span class="text-xs px-2.5 py-1 rounded-lg bg-white/15 text-white/95 backdrop-blur-sm border border-white/10">{{ $tech->name }}</span>
                                    @endforeach
                                </div>
                                <h3 class="mb-1 text-lg font-bold text-white">{{ $project->localizedTitle() }}</h3>
                                <p class="mb-4 text-sm leading-relaxed text-slate-300">{{ $project->localizedShortDescription() ?? \Illuminate\Support\Str::limit($project->localizedDescription(), 100) }}</p>
                                @if ($project->demo_url)
                                    <a href="{{ $project->demo_url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-bold transition-all duration-300 rounded-xl bg-white/95 text-slate-900 hover:bg-white hover:gap-3">
                                        {{ __('public.projects.view_project') }}
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                                    </a>
                                @endif
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="flex items-start justify-between gap-3">
                                <h3 class="text-lg font-bold transition text-slate-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400">{{ $project->localizedTitle() }}</h3>
                                @if ($project->category)
                                    <span class="shrink-0 text-xs font-semibold px-2.5 py-1 rounded-lg bg-primary-50 dark:bg-primary-950/50 text-primary-600 dark:text-primary-400">{{ $project->category->name }}</span>
                                @endif
                            </div>
                            <p class="mt-2 text-sm leading-relaxed text-slate-500 dark:text-slate-400">{{ $project->localizedShortDescription() ?? \Illuminate\Support\Str::limit($project->localizedDescription(), 120) }}</p>
                            @if ($project->relatedProduct)
                                <a href="{{ route('public.products.show', $project->relatedProduct->slug) }}"
                                   class="mt-3 inline-flex items-center gap-1.5 rounded-xl bg-primary-50 px-4 py-2 text-xs font-bold text-primary-600 transition hover:bg-primary-100 dark:bg-primary-950/50 dark:text-primary-300 dark:hover:bg-primary-900/40">
                                    {{ __('public.projects.project_details') }}
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>

            {{-- صفحه‌بندی --}}
            <div class="mt-12 reveal">
                {{ $projects->links() }}
            </div>
        @else
            <div class="text-center glass-card p-14 reveal">
                <span class="inline-flex items-center justify-center mb-4 w-14 h-14 rounded-2xl bg-primary-50 dark:bg-primary-950/50 text-primary-600 dark:text-primary-400">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-7 h-7"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></svg>
                </span>
                <p class="mb-1 font-bold text-slate-600 dark:text-slate-300">{{ __('public.projects.no_results') }}</p>
                <p class="mb-6 text-sm text-slate-500 dark:text-slate-400">
                    @if ($search !== '')
                        {{ __('public.projects.try_other_search') }}
                    @else
                        {{ __('public.projects.none_published') }}
                    @endif
                </p>
                <a href="{{ route('public.portfolio.index') }}" class="btn-outline !px-5 !py-2.5 !text-xs">{{ __('public.projects.clear_filters') }}</a>
            </div>
        @endif
    </section>
</x-public.layout>
