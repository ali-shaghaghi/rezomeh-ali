{{-- ===== مشاوره تخصصی ===== --}}
<section id="consultation" class="mb-32 scroll-mt-28">
    <div class="reveal">
        <div class="section-badge">{{ __('public.consultation.title') }}</div>
        <h2 class="section-title">{{ __('public.consultation.title') }}</h2>
        <p class="section-subtitle mx-auto !mb-0 text-center">{{ __('public.consultation.subtitle') }}</p>
    </div>

    @if ($consultationTypes->isNotEmpty())
        <div class="grid grid-cols-1 gap-6 mt-12 sm:grid-cols-2">
            @foreach ($consultationTypes as $type)
                <a href="{{ route('public.consultation.index') }}"
                   class="relative flex flex-col gap-5 p-4 p-6 mb-4 group glass-card reveal sm:flex-row sm:items-center post-card animate-slide-up"
                   style="transition-delay: {{ min($loop->index * 80, 320) }}ms">
                    {{-- آیکون --}}
                    <div class="flex items-center justify-center transition-transform duration-300 shadow-lg w-14 h-14 shrink-0 rounded-2xl bg-gradient-to-br from-primary-500 to-accent-500 shadow-primary-500/25 group-hover:scale-110">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white w-7 h-7"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-baseline gap-x-3">
                            <h3 class="font-bold text-slate-900 dark:text-white">{{ $type->localizedName() }}</h3>
                            <span class="text-xs font-medium text-slate-400 dark:text-slate-500">
                                {{ fa_digits($type->duration_minutes) }} {{ __('public.consultation.minute') }} · {{ __('public.consultation.session') }}
                            </span>
                        </div>
                        @if ($type->localizedDescription())
                            <p class="mt-1.5 text-sm leading-relaxed text-slate-500 dark:text-slate-400 line-clamp-2">{{ $type->localizedDescription() }}</p>
                        @endif

                        <div class="flex flex-wrap items-center justify-between gap-3 mt-4">
                            <span class="text-lg font-extrabold gradient-text">
                                {{ format_price_for_locale((float) $type->price) }}
                            </span>
                            <span class="inline-flex items-center gap-1.5 text-sm font-bold text-primary-600 dark:text-primary-400 group-hover:gap-2.5 transition-all">
                                {{ __('public.consultation.book') }}
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                            </span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-10 text-center reveal">
            <a href="{{ route('public.consultation.index') }}" class="inline-flex items-center gap-2 btn-outline">
                {{ __('public.consultation.book') }}
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            </a>
        </div>
    @else
        <div class="py-16 text-center text-slate-400 dark:text-slate-500 reveal glass-card">
            {{ __('public.consultation.empty') }}
        </div>
    @endif
</section>
