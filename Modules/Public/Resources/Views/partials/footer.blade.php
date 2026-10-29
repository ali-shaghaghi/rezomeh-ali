<footer id="footer" class="relative mt-4">
    @php
        $profile = \Modules\Resume\Models\ResumeProfile::current();
        $profileName = localized_field($profile, 'name') ?? 'علی شقاقی';
        $socialLinks = profile_social_links($profile);
    @endphp
    <div class="relative overflow-hidden border-t border-slate-200/60 bg-gradient-to-b from-white/40 to-slate-50/60 backdrop-blur-sm dark:border-slate-800/60 dark:from-surface-900/40 dark:to-surface-950/60">
        {{-- هاله نوری دکوراتیو --}}
        <div class="absolute inset-x-0 -top-24 h-24 pointer-events-none bg-gradient-to-b from-primary-500/8 to-transparent dark:from-primary-500/10" aria-hidden="true"></div>

        <div class="max-w-5xl px-4 py-12 mx-auto sm:px-6">
            <div class="flex flex-col items-center gap-6 text-center">
                {{-- نام + عنوان شغلی --}}
                <div>
                    <h3 class="text-lg font-extrabold tracking-tight gradient-text">{{ $profileName }}</h3>
                    @if (localized_field($profile, 'job_title'))
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ localized_field($profile, 'job_title') }}</p>
                    @endif
                </div>

                {{-- شبکه‌های اجتماعی --}}
                @if (count($socialLinks))
                    <div class="flex flex-wrap items-center justify-center gap-2.5">
                        @foreach ($socialLinks as $link)
                            <a href="{{ $link['url'] }}"
                               @if (! in_array($link['key'], ['phone', 'email'])) target="_blank" rel="noopener noreferrer" @endif
                               class="social-icon-btn"
                               aria-label="{{ $link['label'] }}"
                               title="{{ $link['label'] }}">
                                <x-social :name="$link['icon']" class="w-[18px] h-[18px]" />
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="flex flex-col items-center justify-center gap-3 px-4 pt-8 mt-8 text-sm border-t border-slate-200/50 sm:flex-row dark:border-slate-800/50 text-slate-500 dark:text-slate-400">
                <p>{{ __('public.footer.rights') }} {{ fa_digits(date('Y')) }} {{ $profileName }}</p>
                <span class="hidden w-1 h-1 rounded-full bg-slate-300 sm:block dark:bg-slate-600"></span>
                <p class="flex items-center gap-1.5">{{ __('public.footer.developed_with') }}
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 text-rose-500"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l8.84 8.84 8.84-8.84a5.5 5.5 0 0 0 0-7.78z"/></svg>
                </p>
            </div>
        </div>
    </div>
</footer>
