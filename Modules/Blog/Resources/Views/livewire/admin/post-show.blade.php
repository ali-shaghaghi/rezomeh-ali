<div>
    @php
        // نمایش دو‌زبانه با fallback — مقاله انگلیسی هم به‌درستی نمایش داده می‌شود
        $showTitle = localized_field($post, 'title');
        $showExcerpt = localized_field($post, 'excerpt');
        $showContent = localized_field($post, 'content');
        $showSeoTitle = localized_field($post, 'seo_title');
        $showSeoDescription = localized_field($post, 'seo_description');
        $isEnglish = current_locale() === 'en' && has_en_version($post, 'title');
        $contentDir = $isEnglish ? 'ltr' : 'rtl';
    @endphp

    {{-- هدر --}}
    <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
        <div>
            <h2 class="text-2xl font-bold sm:text-3xl">{{ $showTitle }}</h2>
            @if ($isEnglish)
                <span class="mt-1 inline-block rounded-full bg-sky-100 px-2.5 py-0.5 text-xs font-bold text-sky-600 dark:bg-sky-900/40 dark:text-sky-400">English version</span>
            @endif
            <p class="mt-1 text-slate-500 dark:text-slate-400" dir="ltr">{{ $post->slug }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.blog.edit', $post) }}"
               class="px-4 py-2 text-sm font-medium text-white transition rounded-xl bg-primary-500 hover:brightness-110">
                ویرایش مقاله
            </a>
            <a href="{{ route('admin.blog.index') }}"
               class="px-4 py-2 text-sm font-medium transition border rounded-xl border-slate-200 text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-400 dark:hover:bg-surface-800">
                بازگشت به لیست
            </a>
        </div>
    </div>

    {{-- اطلاعات مقاله --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            {{-- تصویر شاخص --}}
            @if($post->featured_image)
                <div class="p-0 overflow-hidden chart-card">
                    <img src="{{ featured_image_url($post->featured_image) }}" alt="{{ $post->featured_image_alt ?: $post->title }}" class="w-full max-h-80 object-cover" />
                </div>
            @endif

            {{-- خلاصه و محتوا --}}
            <div class="p-6 chart-card" dir="{{ $contentDir }}">
                @if($showExcerpt)
                    <p class="pb-4 mb-4 text-sm leading-7 border-b text-slate-600 dark:text-slate-300 dark:border-slate-700">{{ $showExcerpt }}</p>
                @endif
                <div class="leading-8 {{ $isEnglish ? 'text-left' : '' }}">{!! render_inline_media($showContent) !!}</div>
            </div>
        </div>

        {{-- ستون کناری --}}
        <div class="space-y-6">
            {{-- وضعیت و آمار --}}
            <div class="p-6 chart-card">
                <h3 class="mb-4 text-lg font-bold">وضعیت و آمار</h3>
                <dl class="space-y-3 text-sm">
                    <div class="flex items-center justify-between">
                        <dt class="text-slate-500 dark:text-slate-400">وضعیت</dt>
                        <dd class="px-2.5 py-1 text-xs font-medium rounded-full {{ $post->status === 'published' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300' }}">
                            {{ $post->status_label }}
                        </dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-slate-500 dark:text-slate-400">نویسنده</dt>
                        <dd class="font-medium">{{ $post->user?->name ?? $post->admin?->name ?? '—' }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-slate-500 dark:text-slate-400">تاریخ انتشار</dt>
                        <dd class="font-medium">{{ $post->published_at?->format('Y/m/d H:i') ?? '—' }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-slate-500 dark:text-slate-400">بازدید</dt>
                        <dd class="font-medium">{{ number_format($post->views) }}</dd>
                    </div>
                </dl>
            </div>

            {{-- دسته‌بندی‌ها و برچسب‌ها --}}
            <div class="p-6 chart-card">
                <h3 class="mb-4 text-lg font-bold">دسته‌بندی و برچسب‌ها</h3>
                <div class="space-y-4">
                    <div>
                        <p class="mb-2 text-xs font-medium text-slate-500">دسته‌بندی‌ها:</p>
                        <div class="flex flex-wrap gap-1.5">
                            @forelse($post->categories as $cat)
                                <span class="px-2.5 py-1 text-xs rounded-lg bg-primary-100 text-primary-700 dark:bg-primary-900/40 dark:text-primary-300">{{ $cat->name }}</span>
                            @empty
                                <span class="text-xs text-slate-400">بدون دسته‌بندی</span>
                            @endforelse
                        </div>
                    </div>
                    <div>
                        <p class="mb-2 text-xs font-medium text-slate-500">برچسب‌ها:</p>
                        <div class="flex flex-wrap gap-1.5">
                            @forelse($post->tags as $tag)
                                <span class="px-2.5 py-1 text-xs rounded-lg bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300">{{ $tag->name }}</span>
                            @empty
                                <span class="text-xs text-slate-400">بدون برچسب</span>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            {{-- نظرات --}}
            <div class="p-6 chart-card">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold">نظرات ({{ $post->comments->count() }})</h3>
                    <a href="{{ route('admin.blog.comments.index') }}" class="text-xs font-medium text-primary-500 hover:text-primary-600 dark:text-primary-400">
                        مدیریت نظرات
                    </a>
                </div>
                <div class="space-y-3">
                    @forelse($post->comments->take(5) as $comment)
                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-700/50">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs font-bold">{{ $comment->user?->name ?? 'کاربر حذف‌شده' }}</span>
                                <span class="px-2 py-0.5 text-xs rounded-lg {{ $comment->status === 'approved' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' : ($comment->status === 'pending' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300' : 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300') }}">
                                    {{ $comment->status_label }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-2">{{ \Illuminate\Support\Str::limit($comment->content, 100) }}</p>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400">هنوز نظری ثبت نشده است</p>
                    @endforelse
                </div>
            </div>

            {{-- SEO --}}
            @if($showSeoTitle || $showSeoDescription)
                <div class="p-6 chart-card">
                    <h3 class="mb-4 text-lg font-bold">SEO</h3>
                    <div class="space-y-2 text-sm">
                        <p><span class="text-slate-500">عنوان:</span> {{ $showSeoTitle ?? '—' }}</p>
                        <p><span class="text-slate-500">توضیحات:</span> {{ $showSeoDescription ?? '—' }}</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
