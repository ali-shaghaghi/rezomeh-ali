{{-- ===== بنر دعوت به سفارش پروژه ===== --}}
<section id="order-project" class="mb-32 scroll-mt-28">
    <div class="relative overflow-hidden border rounded-3xl reveal border-primary-200/60 dark:border-primary-800/40">
        {{-- بک‌گراند گرادیان متحرک --}}
        <div class="absolute inset-0 bg-gradient-to-br from-primary-500 via-primary-600 to-accent-500"></div>
        <div class="absolute inset-0 opacity-30 mix-blend-overlay animate-gradient-pan" style="background-image: radial-gradient(circle at 20% 50%, rgba(255,255,255,.6), transparent 25%), radial-gradient(circle at 80% 80%, rgba(255,255,255,.5), transparent 25%);"></div>

        <div class="relative px-8 py-12 sm:px-14 sm:py-16">
            <div class="grid items-center gap-10 lg:grid-cols-[1.2fr_0.8fr]">
                {{-- سمت متن --}}
                <div class="text-center lg:text-start">
                    <span class="inline-flex items-center gap-2 px-3 py-1.5 mb-5 text-xs font-bold text-white rounded-full bg-white/15 backdrop-blur-sm ring-1 ring-white/25">
                        <span class="relative flex w-2 h-2">
                            <span class="absolute inline-flex w-full h-full rounded-full opacity-75 animate-ping bg-white/80"></span>
                            <span class="relative inline-flex w-2 h-2 rounded-full bg-white"></span>
                        </span>
                        {{ __('public.order_project.badge') }}
                    </span>
                    <h2 class="text-3xl font-extrabold leading-tight text-white sm:text-4xl">
                        {{ __('public.order_project.title') }}
                    </h2>
                    <p class="mt-4 text-base leading-relaxed text-white/90 sm:text-lg sm:max-w-xl">
                        {{ __('public.order_project.subtitle') }}
                    </p>

                    <div class="flex flex-wrap justify-center gap-3 mt-8 lg:justify-start">
                        <a href="{{ auth()->check() ? route('user.projectdev.create') : route('auth.login') }}"
                           class="inline-flex items-center gap-2 px-6 py-3 text-sm font-bold transition-all rounded-xl bg-white text-primary-600 shadow-xl hover:shadow-2xl hover:-translate-y-0.5">
                            {{ __('public.order_project.cta') }}
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                        </a>
                        @if (auth()->check())
                            <a href="{{ url('/dashboard?tab=projects') }}"
                               class="inline-flex items-center gap-2 px-6 py-3 text-sm font-bold text-white transition-all rounded-xl bg-white/15 ring-1 ring-white/30 hover:bg-white/25">
                                {{ __('public.order_project.my_projects') }}
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                            </a>
                        @endif
                        @if ($products->isNotEmpty())
                            <a href="#products" class="inline-flex items-center gap-2 px-6 py-3 text-sm font-bold text-white transition-all rounded-xl bg-white/15 ring-1 ring-white/30 hover:bg-white/25">
                                {{ __('public.products.view_all') }}
                            </a>
                        @endif
                    </div>
                </div>

                {{-- سمت آمار + انواع پروژه --}}
                <div class="space-y-4">
                    {{-- شمارنده پروژه‌های تحویل‌شده --}}
                    <div class="p-5 text-center rounded-2xl bg-white/15 backdrop-blur-sm ring-1 ring-white/25">
                        <div class="text-4xl font-extrabold text-white sm:text-5xl gradient-text-white">
                            {{ fa_digits(max($deliveredProjects, $projects->count())) }}+
                        </div>
                        <div class="mt-1 text-sm font-semibold text-white/90">{{ __('public.order_project.delivered') }}</div>
                        <div class="mt-0.5 text-xs text-white/70">{{ __('public.order_project.delivered_hint') }}</div>
                    </div>

                    @if ($projectTypes->isNotEmpty())
                        <div class="p-5 rounded-2xl bg-white/15 backdrop-blur-sm ring-1 ring-white/25">
                            <div class="mb-3 text-xs font-bold uppercase tracking-wide text-white/70">{{ __('public.order_project.types_hint') }}</div>
                            <div class="flex flex-wrap gap-2">
                                @foreach ($projectTypes as $type)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium text-white rounded-lg bg-white/15 ring-1 ring-white/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-white/80"></span>
                                        {{ $type->name }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
