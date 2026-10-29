<x-public.layout :title="$post->localizedTitle()" :metaDescription="$post->localizedSeoDescription()">
    <article class="scroll-mt-28">
        {{-- هدر مقاله --}}
        <header class="mb-5 reveal">
            <div class="flex flex-wrap items-center gap-2 mb-4">
                @foreach ($post->categories as $cat)
                    <a href="{{ route('public.blog.index', ['category' => $cat->id]) }}" class="section-badge !mb-0 hover:shadow-glow transition">{{ $cat->localizedName() }}</a>
                @endforeach
            </div>
            <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900 dark:text-white mb-5 leading-[1.3]">{{ $post->localizedTitle() }}</h1>
            <div class="flex flex-wrap items-center gap-4 text-sm text-slate-500 dark:text-slate-400">
                @if ($post->user)
                    <span class="flex items-center gap-2">
                        <span class="flex items-center justify-center w-8 h-8 text-sm font-bold text-white rounded-full bg-gradient-to-br from-primary-500 to-accent-500">{{ mb_substr($post->user->name, 0, 1) }}</span>
                        {{ $post->user->name }}
                    </span>
                @endif
                @if ($post->published_at)
                    <span class="flex items-center gap-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        {{ fa_digits($post->published_at->format('Y/m/d')) }}
                    </span>
                @endif
                <span class="flex items-center gap-1.5">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    {{ fa_digits($post->views) }} {{ __('public.blog.views') }}
                </span>
                <span class="flex items-center gap-1.5">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
                    {{ __('blog.comments_count', ['count' => fa_digits($comments->count() + $comments->sum(fn ($c) => $c->replies->count()))]) }}
                </span>
            </div>
        </header>

        {{-- تصویر شاخص --}}
        @if ($post->featured_image)
            <div class="relative mb-5 overflow-hidden border rounded-2xl reveal shadow-card border-slate-200/70 dark:border-slate-700/50">
                <img src="{{ featured_image_url($post->featured_image) }}" alt="{{ $post->featured_image_alt ?: $post->localizedTitle() }}" class="w-full max-h-[480px] object-cover">
                <div class="absolute inset-0 pointer-events-none bg-gradient-to-t from-slate-900/20 to-transparent"></div>
            </div>
        @endif

        {{-- محتوای مقاله --}}
        <div class="p-6 mb-5 glass-card sm:p-10 reveal">
            <div class="prose-public max-w-none">
                {!! render_inline_media($post->localizedContent()) !!}
            </div>

            {{-- برچسب‌ها --}}
            @if ($post->tags->count())
                <div class="flex flex-wrap items-center gap-2 pt-6 mt-5 border-t border-slate-200/60 dark:border-slate-700/50">
                    <span class="ml-1 text-xs font-bold text-slate-500 dark:text-slate-400">{{ __('blog.tags_label') }}</span>
                    @foreach ($post->tags as $tag)
                        <span class="skill-tag !text-xs !py-1.5 !px-3">{{ $tag->name }}</span>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- نظرات --}}
        <section id="comments" class="mb-5 scroll-mt-28">
            <div class="mb-6 reveal">
                <div class="section-badge">{{ __('blog.comments') }}</div>
                <h2 class="text-2xl font-extrabold text-slate-900 dark:text-white">{{ __('blog.your_comment') }}</h2>
            </div>

            {{-- پیام موفقیت/خطا پس از ثبت نظر یا پاسخ --}}
            @if (session('success'))
                <div class="p-4 mb-5 text-sm font-medium rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200/70 dark:bg-emerald-900/20 dark:text-emerald-300 dark:border-emerald-800/60 reveal">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="p-4 mb-5 text-sm font-medium rounded-xl bg-rose-50 text-rose-700 border border-rose-200/70 dark:bg-rose-900/20 dark:text-rose-300 dark:border-rose-800/60 reveal">
                    {{ session('error') }}
                </div>
            @endif

            @if ($comments->count())
                <div class="mb-5 space-y-4">
                    @foreach ($comments as $comment)
                        <div class="p-5 glass-card reveal" x-data="{ replyOpen: false }">
                            <div class="flex items-center gap-3 mb-3">
                                @if ($comment->admin_id)
                                    <span class="flex items-center justify-center text-sm font-bold text-white rounded-full w-9 h-9 bg-gradient-to-br from-accent-500 to-primary-500 shrink-0">{{ mb_substr($comment->authorName(), 0, 1) }}</span>
                                @elseif ($comment->user?->avatar)
                                    <img src="{{ user_avatar_url($comment->user) }}" alt="{{ $comment->user->name }}" class="object-cover w-9 h-9 rounded-full shrink-0" loading="lazy">
                                @else
                                    <span class="flex items-center justify-center text-sm font-bold text-white rounded-full w-9 h-9 bg-gradient-to-br from-primary-500 to-accent-500 shrink-0">{{ mb_substr($comment->authorName(), 0, 1) }}</span>
                                @endif
                                <div>
                                    <p class="flex items-center gap-2 text-sm font-bold text-slate-800 dark:text-slate-200">
                                        {{ $comment->authorName() }}
                                        @if ($comment->admin_id)
                                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-lg bg-accent-100 text-accent-700 dark:bg-accent-900/40 dark:text-accent-300">{{ __('blog.admin_badge') }}</span>
                                        @endif
                                    </p>
                                    <p class="text-xs text-slate-400">{{ $comment->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                            <p class="text-sm leading-relaxed text-slate-600 dark:text-slate-300">{{ $comment->content }}</p>

                            @auth
                                <div class="mt-3">
                                    <button type="button" @click="replyOpen = !replyOpen"
                                            class="inline-flex items-center gap-1.5 text-xs font-bold text-primary-600 transition hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5"><polyline points="9 17 4 12 9 7"/><path d="M20 18v-2a4 4 0 00-4-4H4"/></svg>
                                        {{ __('blog.reply') }}
                                    </button>

                                    <div x-show="replyOpen" x-transition.opacity.duration.200ms style="display: none" class="mt-3">
                                        <form method="POST" action="{{ route('public.blog.comment', $post->slug) }}" class="space-y-3">
                                            @csrf
                                            <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                                            <textarea name="content" rows="3" required minlength="5" maxlength="3000"
                                                      placeholder="{{ __('blog.reply_placeholder') }}"
                                                      class="w-full px-4 py-3 text-sm transition-all border outline-none resize-y rounded-xl bg-white/80 dark:bg-slate-800/60 border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 placeholder:text-slate-400 focus:border-primary-400 focus:ring-2 focus:ring-primary-400/20"></textarea>
                                            <div class="flex items-center gap-3">
                                                <button type="submit" class="btn-primary !py-2 !text-xs">{{ __('blog.submit_reply') }}</button>
                                                <button type="button" @click="replyOpen = false" class="text-xs font-medium text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200">{{ __('blog.cancel') }}</button>
                                            </div>
                                            <p class="text-[11px] text-slate-400">{{ __('blog.comment_approval_note') }}</p>
                                        </form>
                                    </div>
                                </div>
                            @endauth

                            {{-- پاسخ‌های تأییدشده — کمی جلوتر از نظر اصلی (تودرتو) --}}
                            @if ($comment->replies->count())
                                <div class="pt-3 mt-4 space-y-3 border-r-2 border-slate-200/80 dark:border-slate-700/70 mr-4 sm:mr-8 pr-4">
                                    @foreach ($comment->replies as $reply)
                                        <div class="p-4 rounded-2xl bg-slate-50/80 border border-slate-200/60 dark:bg-slate-800/50 dark:border-slate-700/60">
                                            <div class="flex items-center gap-3 mb-2">
                                                @if ($reply->admin_id)
                                                    <span class="flex items-center justify-center text-xs font-bold text-white rounded-full w-7 h-7 bg-gradient-to-br from-accent-500 to-primary-500 shrink-0">{{ mb_substr($reply->authorName(), 0, 1) }}</span>
                                                @elseif ($reply->user?->avatar)
                                                    <img src="{{ user_avatar_url($reply->user) }}" alt="{{ $reply->user->name }}" class="object-cover w-7 h-7 rounded-full shrink-0" loading="lazy">
                                                @else
                                                    <span class="flex items-center justify-center text-xs font-bold text-white rounded-full w-7 h-7 bg-gradient-to-br from-primary-500 to-accent-500 shrink-0">{{ mb_substr($reply->authorName(), 0, 1) }}</span>
                                                @endif
                                                <div>
                                                    <p class="flex items-center gap-2 text-xs font-bold text-slate-800 dark:text-slate-200">
                                                        {{ $reply->authorName() }}
                                                        @if ($reply->admin_id)
                                                            <span class="px-1.5 py-0.5 text-[10px] font-bold rounded-lg bg-accent-100 text-accent-700 dark:bg-accent-900/40 dark:text-accent-300">{{ __('blog.admin_badge') }}</span>
                                                        @endif
                                                    </p>
                                                    <p class="text-[11px] text-slate-400">{{ $reply->created_at->diffForHumans() }}</p>
                                                </div>
                                            </div>
                                            <p class="text-sm leading-relaxed text-slate-600 dark:text-slate-300">{{ $reply->content }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <p class="mb-5 text-sm text-slate-500 dark:text-slate-400">{{ __('blog.first_comment') }}</p>
            @endif

            {{-- فرم ثبت نظر --}}
            @auth
                <div class="p-6 glass-card reveal">
                    <h3 class="mb-4 font-bold text-slate-900 dark:text-white">{{ __('blog.new_comment') }}</h3>
                    <form method="POST" action="{{ route('public.blog.comment', $post->slug) }}" class="space-y-4">
                        @csrf
                        <div>
                            <label for="comment-content" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">{{ __('blog.comment_field') }}</label>
                            <textarea name="content" id="comment-content" rows="4" required
                                      placeholder="{{ __('blog.comment_placeholder') }}"
                                      class="w-full px-4 py-3 transition-all border outline-none resize-y rounded-xl bg-white/80 dark:bg-slate-800/60 border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 placeholder:text-slate-400 focus:border-primary-400 focus:ring-2 focus:ring-primary-400/20">{{ old('content') }}</textarea>
                            @error('content') <p class="text-rose-500 text-xs mt-1.5">{{ $message }}</p> @enderror
                        </div>
                        <p class="text-xs text-slate-400">{{ __('blog.comment_approval_note') }}</p>
                        <button type="submit" class="w-full btn-primary sm:w-auto">{{ __('blog.submit_comment') }}</button>
                    </form>
                </div>
            @else
                <div class="p-6 text-center glass-card reveal">
                    <p class="mb-4 text-sm text-slate-500 dark:text-slate-400">{{ __('blog.login_to_comment') }}</p>
                    <a href="{{ route('auth.login') }}" class="btn-outline">{{ __('blog.login') }}</a>
                </div>
            @endauth
        </section>

        {{-- مقالات مرتبط --}}
        @if ($related->count())
            <section class="scroll-mt-28">
                <div class="mb-6 reveal">
                    <div class="section-badge">{{ __('blog.more_articles') }}</div>
                    <h2 class="text-2xl font-extrabold text-slate-900 dark:text-white">{{ __('blog.related_articles') }}</h2>
                </div>
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($related as $relatedPost)
                        @if ($relatedPost->id !== $post->id)
                            <a href="{{ route('public.blog.show', $relatedPost->slug) }}" class="p-3 project-card group reveal glass-card">
                                <div class="post-card-image rounded-xl">
                                    @if ($relatedPost->featured_image)
                                        <img src="{{ featured_image_url($relatedPost->featured_image) }}" alt="{{ $relatedPost->featured_image_alt ?: $relatedPost->title }}" class="project-image" loading="lazy">
                                    @else
                                        <div class="flex items-center justify-center p-4 project-image bg-gradient-to-br from-primary-500 via-primary-600 to-accent-500">
                                            <span class="font-bold leading-relaxed text-center text-white">{{ \Illuminate\Support\Str::limit($relatedPost->title, 60) }}</span>
                                        </div>
                                    @endif
                                </div>
                                <div class="p-5">
                                    <div class="flex items-center gap-2 mb-3 text-xs text-slate-400">
                                        @if ($relatedPost->published_at)
                                            <span class="flex items-center gap-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                                {{ fa_digits($relatedPost->published_at->format('Y/m/d')) }}
                                            </span>
                                        @endif
                                    </div>
                                    <h3 class="mb-2 font-bold leading-relaxed transition text-slate-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400">{{ $relatedPost->title }}</h3>
                                    <p class="text-sm leading-relaxed text-slate-500 dark:text-slate-400">{{ \Illuminate\Support\Str::limit($relatedPost->excerpt, 90) }}</p>
                                    <span class="inline-flex items-center gap-1.5 text-sm font-bold text-primary-600 dark:text-primary-400 mt-4 group-hover:gap-2.5 transition-all">
                                        {{ __('public.blog.read_more') }}
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                                    </span>
                                </div>
                            </a>
                        @endif
                    @endforeach
                </div>
            </section>
        @endif
    </article>
</x-public.layout>
