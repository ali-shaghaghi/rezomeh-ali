<div>
    {{-- کارت‌های آمار محتوا --}}
    <div class="mb-8 grid gap-4 grid-cols-2 xl:grid-cols-4">
        <div class="glass-card p-5">
            <div class="flex items-center justify-between">
                <p class="text-[11px] text-slate-500 dark:text-slate-400">محصولات</p>
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-primary-100 text-primary-600 dark:bg-primary-900/40 dark:text-primary-400">
                    <x-icon name="products" size="5" />
                </span>
            </div>
            <p class="mt-2 text-2xl font-black text-slate-900 dark:text-white">{{ fa_digits($productStats['total']) }}</p>
            <p class="mt-1 text-[11px] text-slate-400">منتشرشده: {{ fa_digits($productStats['published']) }}</p>
        </div>

        <div class="glass-card p-5">
            <div class="flex items-center justify-between">
                <p class="text-[11px] text-slate-500 dark:text-slate-400">فروش کل</p>
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-violet-100 text-violet-600 dark:bg-violet-900/40 dark:text-violet-400">
                    <x-icon name="cart" size="5" />
                </span>
            </div>
            <p class="mt-2 text-2xl font-black text-slate-900 dark:text-white">{{ fa_digits($productStats['total_sales']) }}</p>
            <p class="mt-1 text-[11px] text-slate-400">لایسنس: {{ fa_digits($productStats['licenses']) }} · دانلود: {{ fa_digits($productStats['downloads']) }}</p>
        </div>

        <div class="glass-card p-5">
            <div class="flex items-center justify-between">
                <p class="text-[11px] text-slate-500 dark:text-slate-400">درآمد فروشگاه</p>
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600 dark:bg-emerald-900/40 dark:text-emerald-400">
                    <x-icon name="wallet" size="5" />
                </span>
            </div>
            <p class="mt-2 text-2xl font-black text-emerald-500">{{ fa_digits(number_format($productStats['sales_revenue'] / 1000000, 1)) }}<span class="text-sm font-medium text-slate-500"> میلیون</span></p>
            <p class="mt-1 text-[11px] text-slate-400">از فروش محصولات</p>
        </div>

        <div class="glass-card p-5">
            <div class="flex items-center justify-between">
                <p class="text-[11px] text-slate-500 dark:text-slate-400">مشاوره‌ها</p>
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-cyan-100 text-cyan-600 dark:bg-cyan-900/40 dark:text-cyan-400">
                    <x-icon name="users" size="5" />
                </span>
            </div>
            <p class="mt-2 text-2xl font-black text-slate-900 dark:text-white">{{ fa_digits($consultationStats['total']) }}</p>
            <p class="mt-1 text-[11px] text-slate-400">تکمیل‌شده: {{ fa_digits($consultationStats['completed']) }} · لغوشده: {{ fa_digits($consultationStats['cancelled']) }}</p>
        </div>
    </div>

    {{-- مقالات پربازدید — کارت‌های موبایل‌پسند --}}
    <h3 class="mb-4 text-lg font-bold text-slate-900 dark:text-white">مقالات پربازدید</h3>
    <div class="mb-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @forelse ($topPosts as $post)
            <article class="glass-card glass-card-hover flex flex-col p-5" wire:key="toppost-{{ $post->id }}">
                <div class="mb-2 flex items-center justify-between gap-2">
                    <span class="rounded-md px-2 py-0.5 text-[10px] font-semibold {{ $post->status === 'published' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-400' }}">
                        {{ $post->status_label }}
                    </span>
                    <span class="text-[10px] text-slate-400">{{ $post->published_at?->format('Y/m/d') ?? '—' }}</span>
                </div>

                <h4 class="line-clamp-2 text-sm font-bold leading-6 text-slate-800 dark:text-slate-100">{{ $post->localizedTitle() }}</h4>

                <div class="mt-auto flex items-center justify-between border-t border-slate-100 pt-3 dark:border-slate-800">
                    <div class="flex items-center gap-3">
                        <span class="flex items-center gap-1 text-xs font-bold text-primary-600 dark:text-primary-400">
                            <x-icon name="eye" size="3.5" />
                            {{ fa_digits($post->views) }}
                        </span>
                        <span class="flex items-center gap-1 text-xs text-slate-500 dark:text-slate-400">
                            <x-icon name="mail" size="3.5" />
                            {{ fa_digits($post->comments()->count()) }}
                        </span>
                    </div>
                    <span class="text-[10px] text-slate-400">بازدید · نظر</span>
                </div>
            </article>
        @empty
            <div class="glass-card col-span-full p-10 text-center text-sm text-slate-500 dark:text-slate-400">هنوز مقاله‌ای منتشر نشده است.</div>
        @endforelse
    </div>
</div>
