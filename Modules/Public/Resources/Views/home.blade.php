<x-public.layout>
    {{-- ===== HERO ===== --}}
    <section id="hero" class="min-h-[88vh] flex items-center mb-32 scroll-mt-28">
        @php
            // نمایش محتوای دو‌زبانه با fallback به فارسی
            $profileName = localized_field($profile, 'name');
            $profileJobTitle = localized_field($profile, 'job_title');
            $profileBio = localized_field($profile, 'bio');
            $profileLocation = localized_field($profile, 'location');
            $typewriterTexts = localized_array($profile, 'typewriter_texts');
            $enAvailable = $profile->hasEnglishVersion();
        @endphp

        <div class="grid lg:grid-cols-[1fr_auto] gap-12 lg:gap-16 items-center w-full min-w-0">
            <div class="order-2 min-w-0 lg:order-1">
                {{-- گزینه زبان بخش رزومه: «انگلیسی» با پرچم انگلیس — فقط اگر نسخه انگلیسی موجود باشد --}}
                <div class="flex items-center justify-between gap-3 mb-4 flex-wrap">
                    <div class="hero-item hero-delay-1 section-badge !mb-0">
                        <span class="status-dot"></span>
                        {{ __('public.hero.available') }}
                    </div>
                    @if ($enAvailable)
                        <a href="{{ route('public.locale', current_locale() === 'en' ? 'fa' : 'en') }}"
                           class="hero-item hero-delay-1 inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-semibold border transition
                               {{ current_locale() === 'en'
                                   ? 'bg-primary-50 dark:bg-primary-950/60 text-primary-600 dark:text-primary-400 border-primary-200/60 dark:border-primary-800/40'
                                   : 'bg-white/70 dark:bg-slate-800/70 text-slate-600 dark:text-slate-300 border-slate-200/70 dark:border-slate-700/50 hover:border-primary-300 dark:hover:border-primary-700' }}"
                           title="{{ __('public.hero.resume_language') }}">
                            <x-flag :code="current_locale() === 'en' ? 'fa' : 'en'" class="w-5 h-[13px]" />
                            {{ current_locale() === 'en' ? __('public.hero.view_in_persian') : __('public.hero.view_in_english') }}
                            <span class="text-[10px] font-bold uppercase opacity-70">{{ current_locale() === 'en' ? 'fa' : 'En' }}</span>
                        </a>
                    @endif
                </div>

                <p class="mb-3 text-sm hero-item hero-delay-1 text-slate-500 dark:text-slate-400">
                    {{ __('public.hero.greeting') }}
                </p>

                <h1 class="hero-item hero-delay-2 text-4xl sm:text-5xl lg:text-[3.25rem] font-extrabold leading-[1.15] tracking-tight text-slate-900 dark:text-white mb-4">
                    {{ $profileName }}
                </h1>

                <p class="hero-item hero-delay-3 text-xl sm:text-2xl font-semibold text-slate-600 dark:text-slate-300 mb-6 min-h-[2.5rem] break-words">
                    <span id="typewriter" class="typewriter-cursor gradient-text">{{ empty($typewriterTexts) ? $profileJobTitle : '' }}</span>
                </p>

                <p class="hero-item hero-delay-3 text-slate-500 dark:text-slate-400 text-base sm:text-lg leading-[1.9] mb-8 max-w-lg">
                    {{ $profileBio }}
                </p>

                <div class="flex flex-wrap gap-3 mb-10 hero-item hero-delay-4">
                    @if ($profile->resume_file)
                        <a href="{{ route('public.resume.download') }}" class="btn-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                            {{ __('public.hero.download_resume') }}
                        </a>
                    @endif
                    <a href="#projects" class="btn-outline">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></svg>
                        {{ __('public.hero.view_projects') }}
                    </a>
                </div>

                <div class="grid max-w-md min-w-0 grid-cols-3 gap-2 mb-8 hero-item hero-delay-5 sm:gap-3">
                    <div class="stat-card">
                        <div class="stat-value">+{{ fa_digits(max($experiences->count() * 2, 5)) }}</div>
                        <div class="stat-label">{{ __('public.hero.years') }}</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">+{{ fa_digits($projects->count() * 5) }}</div>
                        <div class="stat-label">{{ __('public.hero.projects_done') }}</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">+{{ fa_digits($projects->count() * 3) }}</div>
                        <div class="stat-label">{{ __('public.hero.happy_clients') }}</div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-5 text-sm hero-item hero-delay-5 text-slate-500 dark:text-slate-400">
                    @if ($profileLocation)
                        <span class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            {{ $profileLocation }}
                        </span>
                    @endif
                    @if ($profile->email)
                        <span class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                            {{ $profile->email }}
                        </span>
                    @endif
                </div>
            </div>

            <div class="flex justify-center order-1 w-full min-w-0 hero-item hero-delay-3 lg:order-2 lg:justify-end">
                <div class="profile-wrap">
                    <div class="profile-ring"></div>
                    <div class="relative glass-card p-6 sm:p-7 w-full max-w-[320px] sm:max-w-[360px] lg:max-w-[380px] mx-auto shadow-card">
                        <div class="status-badge">
                            <span class="status-dot"></span>
                            {{ __('public.hero.online') }}
                        </div>
                        <div class="flex items-center justify-center w-full overflow-hidden shadow-lg aspect-square rounded-2xl bg-gradient-to-br from-primary-500 via-primary-600 to-accent-500 shadow-primary-500/30">
                            @if ($profile->avatar)
                                @php
                                    // هم مسیرهای قدیمی storage و هم مسیرهای گالری (gallery/{hash}) پشتیبانی می‌شود
                                    $heroAvatarUrl = (string) featured_image_url($profile->avatar);
                                    $heroAvatarDisk = \Illuminate\Support\Facades\Storage::disk('public');
                                    $heroAvatarIsLegacy = ! str_starts_with(ltrim($profile->avatar, '/'), 'gallery/') && ! preg_match('#^https?://#i', $profile->avatar);
                                    if ($heroAvatarIsLegacy && $heroAvatarDisk->exists($profile->avatar)) {
                                        $heroAvatarUrl .= '?v=' . $heroAvatarDisk->lastModified($profile->avatar);
                                    }
                                    // نسخه WebP برای سرعت بارگذاری — فقط وقتی فایل واقعاً وجود داشته باشد
                                    $heroAvatarWebp = null;
                                    if ($heroAvatarIsLegacy) {
                                        $heroAvatarWebpPath = preg_replace('/\.(png|jpe?g|gif)$/i', '.webp', $profile->avatar);
                                        if ($heroAvatarWebpPath !== $profile->avatar && $heroAvatarDisk->exists($heroAvatarWebpPath)) {
                                            $heroAvatarWebp = asset('storage/' . $heroAvatarWebpPath) . '?v=' . $heroAvatarDisk->lastModified($heroAvatarWebpPath);
                                        }
                                    }
                                @endphp
                                <picture>
                                    @if ($heroAvatarWebp)
                                        <source srcset="{{ $heroAvatarWebp }}" type="image/webp">
                                    @endif
                                    <img src="{{ $heroAvatarUrl }}" alt="{{ $profileName }}" class="object-cover w-full h-full" onerror="this.style.display='none'">
                                </picture>
                            @else
                                <span class="font-black text-white select-none text-8xl">{{ mb_substr($profileName, 0, 1) }}</span>
                            @endif
                        </div>
                        <div class="mt-5 text-center">
                            <p class="text-xl font-bold text-slate-900 dark:text-white">{{ $profileName }}</p>
                            <p class="mt-1 text-sm font-medium text-primary-600 dark:text-primary-400">{{ $profileJobTitle }}</p>
                        </div>
                        <div class="flex flex-wrap justify-center gap-2 pt-4 mt-4 border-t border-slate-200/60 dark:border-slate-700/50">
                            @foreach (profile_social_links($profile) as $link)
                                <a href="{{ $link['url'] }}"
                                   @if(! in_array($link['key'], ['phone', 'email'])) target="_blank" rel="noopener noreferrer" @endif
                                   class="social-icon-btn"
                                   aria-label="{{ $link['label'] }}"
                                   title="{{ $link['label'] }}">
                                    <x-social :name="$link['icon']" class="w-[18px] h-[18px]" />
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== SKILLS ===== --}}
    <section id="skills" class="mb-32 scroll-mt-28">
        <div class="mb-12 reveal">
            <div class="section-badge">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
                {{ __('public.nav.skills') }}
            </div>
            <h2 class="section-title">{{ __('public.skills.title') }}</h2>
            <p class="section-subtitle">{{ __('public.skills.subtitle') }}</p>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="p-6 glass-card sm:p-8 reveal">
                <h3 class="mb-5 text-sm font-bold text-slate-900 dark:text-white">{{ __('public.skills.core_techs') }}</h3>
                <div class="flex flex-wrap gap-2.5">
                    @forelse ($skills['tags'] as $tag)
                        <span class="skill-tag reveal">{{ $tag->name }}</span>
                    @empty
                        <p class="text-sm text-slate-500">{{ __('public.skills.empty') }}</p>
                    @endforelse
                </div>
            </div>
            <div class="p-6 glass-card sm:p-8 reveal">
                <h3 class="mb-6 text-sm font-bold text-slate-900 dark:text-white">{{ __('public.skills.proficiency') }}</h3>
                <div class="space-y-5">
                    @forelse ($skills['bars'] as $skill)
                        <div class="reveal">
                            <div class="flex justify-between mb-2">
                                <span class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ $skill->localizedName() }}</span>
                                <span class="text-sm font-bold text-primary-600 dark:text-primary-400">{{ fa_digits($skill->proficiency) }}٪</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="--progress:{{ $skill->proficiency }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">{{ __('public.skills.empty') }}</p>
                    @endforelse
                </div>
            </div>
        </div>
    </section>

    {{-- ===== EXPERIENCE ===== --}}
    <section id="experience" class="mb-32 scroll-mt-28">
        <div class="mb-12 reveal">
            <div class="section-badge">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                {{ __('public.nav.experience') }}
            </div>
            <h2 class="section-title">{{ __('public.experience.title') }}</h2>
            <p class="section-subtitle">{{ __('public.experience.subtitle') }}</p>
        </div>
        <div class="relative mr-1">
            @forelse ($experiences as $exp)
                <div class="relative pb-10 pe-10 last:pb-0 reveal">
                    <div class="timeline-dot"></div>
                    <div class="p-6 glass-card-hover">
                        <div class="flex flex-wrap items-center gap-2 mb-3">
                            <span class="px-3 py-1 text-xs font-bold rounded-lg bg-primary-50 dark:bg-primary-950/50 text-primary-600 dark:text-primary-400">
                                {{ fa_digits($exp->start_date?->format('Y')) }} — {{ $exp->is_current ? __('public.experience.present') : fa_digits($exp->end_date?->format('Y')) }}
                            </span>
                            <span class="text-sm text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                                {{ $exp->localizedCompany() }}
                            </span>
                            @if ($exp->location)
                                <span class="text-xs text-slate-400">{{ $exp->location }}</span>
                            @endif
                        </div>
                        <h3 class="mb-2 text-lg font-bold text-slate-900 dark:text-white">{{ $exp->localizedPosition() }}</h3>
                        <p class="mb-4 text-sm leading-relaxed text-slate-500 dark:text-slate-400">{{ $exp->localizedDescription() }}</p>
                        @if ($exp->technologies)
                            <div class="flex flex-wrap gap-2">
                                @foreach ($exp->technologies as $t)
                                    <span class="text-xs px-2.5 py-1 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 font-medium">{{ $t }}</span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-sm text-slate-500">{{ __('public.skills.empty') }}</p>
            @endforelse
            <div class="timeline-line" aria-hidden="true"></div>
        </div>
    </section>

    {{-- ===== PROJECTS ===== --}}
    <section id="projects" class="mb-32 scroll-mt-28">
        <div class="mb-12 reveal">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <div class="section-badge">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></svg>
                        {{ __('public.nav.projects') }}
                    </div>
                    <h2 class="section-title">{{ __('public.projects.title') }}</h2>
                    <p class="section-subtitle !mb-0">{{ __('public.projects.subtitle') }}</p>
                </div>
                <a href="{{ route('public.portfolio.index') }}" class="shrink-0 btn-outline !px-5 !py-2.5 !text-xs">
                    {{ __('public.projects.view_all') }}
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                </a>
            </div>
        </div>
        <div class="grid gap-6 sm:grid-cols-2">
            @forelse ($projects as $project)
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
            @empty
                <p class="col-span-2 text-sm text-slate-500">{{ __('public.skills.empty') }}</p>
            @endforelse
        </div>
    </section>

    {{-- ===== ORDER PROJECT BANNER ===== --}}
    @include('public::partials.home-order-banner')

    {{-- ===== PRODUCTS ===== --}}
    @include('public::partials.home-products')

    {{-- ===== CONSULTATION ===== --}}
    @include('public::partials.home-consultation')

    {{-- ===== LATEST POSTS ===== --}}
    @if ($posts->count())
        <section id="blog" class="mb-32 scroll-mt-28">
            <div class="mb-12 reveal">
                <div class="flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <div class="section-badge">{{ __('public.nav.blog') }}</div>
                        <h2 class="section-title">{{ __('public.blog.title') }}</h2>
                        <p class="section-subtitle !mb-0">{{ __('public.blog.subtitle') }}</p>
                    </div>
                    <a href="{{ route('public.blog.index') }}" class="shrink-0 btn-outline !px-5 !py-2.5 !text-xs">
                        {{ __('public.blog.all_posts') }}
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                    </a>
                </div>
            </div>
            <div class="grid gap-6 p-6 project-card sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($posts as $post)
                    <a href="{{ route('public.blog.show', $post->slug) }}" class="p-3 project-card group reveal glass-card">
                        <div class="post-card-image rounded-xl">
                            @if ($post->featured_image)
                                <img src="{{ featured_image_url($post->featured_image) }}" alt="{{ $post->featured_image_alt ?: $post->localizedTitle() }}" class="project-image" loading="lazy">
                            @else
                                <div class="flex items-center justify-center p-4 project-image bg-gradient-to-br from-primary-500 via-primary-600 to-accent-500">
                                    <span class="font-bold leading-relaxed text-center text-white">{{ \Illuminate\Support\Str::limit($post->localizedTitle(), 60) }}</span>
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
                            <span class="inline-flex items-center gap-1.5 text-sm font-bold text-primary-600 dark:text-primary-400 mt-4 group-hover:gap-2.5 transition-all">
                                {{ __('public.blog.read_more') }}
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- متن‌های تایپ‌رایتر برای JS (دو‌زبانه) --}}
    <script data-typewriter type="application/json">{!! json_encode($typewriterTexts, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
</x-public.layout>
