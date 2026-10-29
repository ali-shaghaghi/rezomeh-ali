<x-public.layout :title="$category ? $category->name : __('public.nav.blog')">
    {{-- ===== هدر وبلاگ + جستجو ===== --}}
    <section class="mb-10 scroll-mt-28">
        <div class="reveal">
            <div class="section-badge">{{ __('public.nav.blog') }}</div>
            <h1 class="section-title !mb-3">{{ $category?->localizedName() ?? __('public.blog.all_posts') }}</h1>
            <p class="section-subtitle !mb-8">{{ $category?->localizedDescription() ?? __('public.blog.subtitle') }}</p>

            {{-- جستجو — موبایل: تمام‌عرض استک | دسکتاپ: اینپوت + دکمه در یک ردیف --}}
            <form method="GET" action="{{ route('public.blog.index') }}">
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
                    <a href="{{ route('public.blog.index') }}{{ $search ? '?q='.urlencode($search) : '' }}"
                       class="skill-tag !py-1.5 !px-4 !text-xs {{ ! $category ? 'text-primary-600 dark:text-primary-400 border-primary-300 dark:border-primary-600 bg-primary-50 dark:bg-primary-950/50' : '' }}">
                        {{ __('public.blog.all_posts') }}
                    </a>
                    @foreach ($categories as $cat)
                        <a href="{{ route('public.blog.index', array_filter(['category' => $cat->id, 'q' => $search !== '' ? $search : null])) }}"
                           class="skill-tag !py-1.5 !px-4 !text-xs {{ $category?->id === $cat->id ? 'text-primary-600 dark:text-primary-400 border-primary-300 dark:border-primary-600 bg-primary-50 dark:bg-primary-950/50' : '' }}">
                            {{ $cat->localizedName() }}
                        </a>
                    @endforeach
                </div>
            @endif

            @if ($search !== '')
                <p class="mt-4 text-sm text-slate-500 dark:text-slate-400">
                    {{ __('public.common.search') }}: «<span class="font-bold text-primary-600 dark:text-primary-400">{{ $search }}</span>» —
                    {{ fa_digits($posts->total()) }}
                </p>
            @endif
        </div>
    </section>

    {{-- لیست مقالات --}}
    <section>
        @if ($posts->count())
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($posts as $post)
                    <a href="{{ route('public.blog.show', $post->slug) }}" class="p-3 project-card group reveal glass-card">
                        <div class="post-card-image rounded-xl">
                            @if ($post->featured_image)
                                <img src="{{ featured_image_url($post->featured_image) }}" alt="{{ $post->featured_image_alt ?: $post->title }}" class="project-image" loading="lazy">
                            @else
                                <div class="flex items-center justify-center p-6 project-image bg-gradient-to-br from-primary-500 via-primary-600 to-accent-500">
                                    <span class="font-bold leading-relaxed text-center text-white">{{ \Illuminate\Support\Str::limit($post->title, 60) }}</span>
                                </div>
                            @endif
                        </div>
                        <div class="p-5">
                            <div class="flex items-center gap-2 mb-3 text-xs text-slate-400">
                                @if ($post->published_at)
                                    <span class="flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                        {{ fa_digits($post->published_at->format('Y/m/d')) }}
                                    </span>
                                @endif
                                <span>•</span>
                                <span class="flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    {{ fa_digits($post->views) }} {{ __('public.blog.views') }}
                                </span>
                            </div>
                            <h3 class="mb-2 font-bold leading-relaxed transition text-slate-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400">{{ $post->localizedTitle() }}</h3>
                            <p class="text-sm leading-relaxed text-slate-500 dark:text-slate-400">{{ \Illuminate\Support\Str::limit($post->localizedExcerpt(), 100) }}</p>
                            <div class="flex flex-wrap gap-1.5 mt-3">
                                @foreach ($post->tags->take(3) as $tag)
                                    <span class="text-xs px-2.5 py-1 rounded-lg bg-primary-50 dark:bg-primary-950/50 text-primary-600 dark:text-primary-400 font-medium">{{ $tag->name }}</span>
                                @endforeach
                            </div>
                            <span class="inline-flex items-center gap-1.5 text-sm font-bold text-primary-600 dark:text-primary-400 mt-4 group-hover:gap-2.5 transition-all">
                                {{ __('public.blog.read_more') }}
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>

            {{-- صفحه‌بندی --}}
            <div class="mt-12 reveal">
                {{ $posts->links() }}
            </div>
        @else
            <div class="text-center glass-card p-14 reveal">
                <span class="inline-flex items-center justify-center mb-4 w-14 h-14 rounded-2xl bg-primary-50 dark:bg-primary-950/50 text-primary-600 dark:text-primary-400">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-7 h-7"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                </span>
                <p class="mb-1 font-bold text-slate-600 dark:text-slate-300">{{ __('public.common.no_results') }}</p>
                <p class="mb-6 text-sm text-slate-500 dark:text-slate-400">
                    @if ($search !== '')
                        {{ __('public.common.search') }}...
                    @else
                        {{ __('public.blog.all_posts') }}
                    @endif
                </p>
                <a href="{{ route('public.blog.index') }}" class="btn-outline !px-5 !py-2.5 !text-xs">{{ __('blog.clear_filters') }}</a>
            </div>
        @endif
    </section>
</x-public.layout>
