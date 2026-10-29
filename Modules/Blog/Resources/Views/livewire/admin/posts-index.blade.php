<div>
    {{-- هدر صفحه --}}
    <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
        <div>
            <h2 class="text-2xl font-bold sm:text-3xl">مقالات وبلاگ</h2>
            <p class="mt-1 text-slate-500 dark:text-slate-400">{{ $posts->total() }} مقاله ثبت‌شده</p>
        </div>
        @can('create', \Modules\Core\Models\BlogPost::class)
            <a href="{{ route('admin.blog.create') }}"
               class="group flex items-center gap-2 rounded-xl bg-primary-500 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary-500/25 transition-all duration-200 hover:shadow-xl hover:shadow-primary-500/35 hover:-translate-y-0.5 active:translate-y-0">
                <span class="flex items-center justify-center w-5 h-5 transition duration-300 rounded-md bg-white/20 group-hover:rotate-90">
                    <x-icon name="plus" size="3" />
                </span>
                افزودن مقاله جدید
            </a>
        @endcan
    </div>

    {{-- جستجو و فیلتر ریاکتیو --}}
    <div class="mb-6 animate-slide-up chart-card">
        <div class="flex flex-wrap gap-3">
            <div class="relative flex-1 min-w-[200px]">
                <span class="absolute -translate-y-1/2 pointer-events-none right-3 top-1/2 text-slate-400">
                    <x-icon name="search" size="4" />
                </span>
                <input type="text" wire:model.live.debounce.400ms="search" placeholder="جستجوی عنوان، اسلاگ یا خلاصه..."
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pr-10 pl-4 text-sm transition focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-surface-800" />
            </div>
            <select wire:model.live="status"
                    class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm transition focus:border-primary-400 focus:outline-none dark:border-slate-700 dark:bg-surface-800">
                <option value="">همه وضعیت‌ها</option>
                <option value="published">منتشر شده</option>
                <option value="draft">پیش‌نویس</option>
            </select>
            <select wire:model.live="categoryId"
                    class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm transition focus:border-primary-400 focus:outline-none dark:border-slate-700 dark:bg-surface-800">
                <option value="">همه دسته‌بندی‌ها</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>
            @if($search || $status || $categoryId)
                <button wire:click="resetFilters" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-600 transition hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-700">
                    حذف فیلترها
                </button>
            @endif
        </div>
    </div>

    {{-- نوتیفیکیشن موفقیت (ریاکتیو) --}}
    @if($successToast)
        <div class="flex items-center justify-between gap-3 px-4 py-3 mb-6 text-sm rounded-xl bg-emerald-100 text-emerald-700 animate-slide-up dark:bg-emerald-900/30 dark:text-emerald-300"
             x-data="{ show: true }" x-init="setTimeout(() => show = false, 3500)" x-show="show" x-transition.opacity.duration.500ms>
            <span class="flex items-center gap-2">
                <x-icon name="check" size="4" />
                {{ $successMessage }}
            </span>
        </div>
    @endif

    {{-- ایندیکیتور لودینگ — فقط هنگام فیلتر/جستجو، شفاف و بدون جابجایی صفحه --}}
    <div wire:loading.delay.200ms class="fixed z-40 items-center hidden gap-2 px-4 py-2 text-xs font-medium text-white rounded-full shadow-lg bottom-6 left-6 bg-primary-500/90 backdrop-blur-sm" wire:target="search, status, categoryId, resetFilters, deletePost, toggleStatus">
        <svg class="w-3.5 h-3.5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
        </svg>
        در حال بارگذاری...
    </div>

    {{-- گرید مقالات — بدون wire:loading.remove تا صفحه فلیکر نزند --}}
    <div class="grid gap-5 animate-slide-up sm:grid-cols-2 xl:grid-cols-3" wire:loading.class="pointer-events-none opacity-60" wire:target="deletePost, toggleStatus">
        @forelse($posts as $post)
            <article class="flex flex-col p-0 mt-2 mb-2 ml-2 mr-2 overflow-hidden chart-card group">
                {{-- پیش‌نمایش تصویر / گرادیان --}}
                <div class="relative flex items-center justify-center h-40 transition bg-gradient-to-bl from-slate-100 to-slate-50 group-hover:from-primary-50 group-hover:to-violet-50 dark:from-slate-800 dark:to-slate-900 dark:group-hover:from-primary-950/50">
                    @if($post->featured_image)
                        <img src="{{ featured_image_url($post->featured_image) }}" alt="{{ $post->featured_image_alt ?: $post->title }}"
                             class="object-cover w-full h-full" />
                    @else
                        <span class="text-primary-400"><x-icon name="analytics" size="10" /></span>
                    @endif

                    {{-- بج وضعیت --}}
                    <span class="absolute top-3 left-3 px-2.5 py-1 text-xs font-medium rounded-full backdrop-blur-sm {{ $post->status === 'published' ? 'bg-emerald-100/90 text-emerald-700 dark:bg-emerald-900/70 dark:text-emerald-300' : 'bg-amber-100/90 text-amber-700 dark:bg-amber-900/70 dark:text-amber-300' }}">
                        {{ $post->status_label }}
                    </span>
                </div>

                <div class="flex flex-col flex-1 p-4">
                    <h3 class="font-bold">{{ $post->title }}</h3>
                    <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">
                        {{ $post->user?->name ?? $post->admin?->name ?? '—' }} — {{ $post->published_at?->format('Y/m/d') ?: $post->created_at->format('Y/m/d') }}
                    </p>

                    {{-- خلاصه --}}
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                        {{ strip_inline_media($post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->content), 90)) }}
                    </p>

                    {{-- دسته‌ها و برچسب‌ها --}}
                    <div class="mt-3 flex flex-wrap gap-1.5">
                        @foreach($post->categories->take(2) as $cat)
                            <span class="px-2 py-1 text-xs rounded-lg bg-primary-100 text-primary-600 dark:bg-primary-900/40 dark:text-primary-300">{{ $cat->name }}</span>
                        @endforeach
                        @foreach($post->tags->take(3) as $tag)
                            <span class="px-2 py-1 text-xs rounded-lg bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300">{{ $tag->name }}</span>
                        @endforeach
                    </div>

                    {{-- بازدید و نظرات --}}
                    <div class="flex items-center justify-between mt-3 text-xs text-slate-400 dark:text-slate-500">
                        <span class="flex items-center gap-1">
                            <x-icon name="eye" size="3" />
                            {{ number_format($post->views) }} بازدید
                        </span>
                        <span>{{ $post->comments->count() }} نظر</span>
                    </div>

                    {{-- اکشن انتشار (ریاکتیو) --}}
                    @can('update', $post)
                        <div class="mt-3">
                            <button wire:click="toggleStatus({{ $post->id }})"
                                    wire:loading.attr="disabled"
                                    class="w-full rounded-lg border py-2 text-xs font-medium transition hover:border-primary-300 dark:border-slate-600 {{ $post->status === 'published' ? 'text-emerald-600' : 'text-slate-600 dark:text-slate-300' }}">
                                {{ $post->status === 'published' ? 'لغو انتشار' : 'انتشار' }}
                            </button>
                        </div>
                    @endcan

                    {{-- دکمه‌های اصلی --}}
                    <div class="flex gap-2 mt-3">
                        <a href="{{ route('admin.blog.show', $post) }}"
                           class="flex-1 py-2 text-xs font-medium text-center transition border rounded-lg border-slate-200 hover:border-primary-300 dark:border-slate-600">
                            مشاهده
                        </a>
                        @can('update', $post)
                            <a href="{{ route('admin.blog.edit', $post) }}"
                               class="flex-1 py-2 text-xs font-medium text-center transition border rounded-lg border-slate-200 hover:border-primary-300 dark:border-slate-600">
                                ویرایش
                            </a>
                        @endcan
                        @can('delete', $post)
                            <button wire:click="confirmDelete({{ $post->id }})"
                                    class="flex-1 py-2 text-xs font-medium transition border rounded-lg border-slate-200 text-rose-500 hover:bg-rose-50 dark:border-slate-600 dark:text-rose-400 dark:hover:bg-rose-900/30">
                                حذف
                            </button>
                        @endcan
                    </div>
                </div>
            </article>
        @empty
            {{-- خالی بودن استیت --}}
            <div class="py-16 text-center col-span-full chart-card">
                <span class="flex mx-auto mb-4 w-fit text-slate-300 dark:text-slate-600">
                    <x-icon name="analytics" size="12" />
                </span>
                <p class="font-medium text-slate-600 dark:text-slate-300">{{ $search ? 'مقاله‌ای مطابق جستجوی شما یافت نشد' : 'هنوز مقاله‌ای ثبت نشده است' }}</p>
                <p class="mt-1 text-sm text-slate-400 dark:text-slate-500">{{ $search ? 'عبارت دیگری را جستجو کنید یا فیلترها را حذف کنید' : 'اولین مقاله خود را بنویسید' }}</p>
                @can('create', \Modules\Core\Models\BlogPost::class)
                    <a href="{{ route('admin.blog.create') }}"
                       class="inline-flex items-center gap-2 px-4 py-2 mt-4 text-sm font-semibold text-white transition shadow-md rounded-xl bg-primary-500 shadow-primary-500/30 hover:brightness-110">
                        <x-icon name="plus" size="3" />
                        افزودن مقاله
                    </a>
                @endcan
            </div>
        @endforelse
    </div>

    {{-- صفحه‌بندی --}}
    @if($posts->hasPages())
        <div class="mt-6">
            {{ $posts->links() }}
        </div>
    @endif

    {{-- مودال تایید حذف (پس‌زمینه بلور + انیمیشن) --}}
    @if($confirmingDelete)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
            <div class="w-full max-w-md p-5 bg-white border shadow-2xl rounded-2xl border-slate-200/50 animate-slide-up dark:border-slate-700/50 dark:bg-surface-800">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-3">
                        <span class="flex items-center justify-center h-9 w-9 rounded-xl bg-rose-100 text-rose-600 dark:bg-rose-900/40 dark:text-rose-400">
                            <x-icon name="trash" size="4" />
                        </span>
                        <div>
                            <h3 class="text-base font-bold">حذف مقاله</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">این عملیات قابل بازگشت نیست</p>
                        </div>
                    </div>
                    <button wire:click="$set('confirmingDelete', false)" class="p-2 transition rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-700 dark:hover:text-slate-300">
                        <x-icon name="close" />
                    </button>
                </div>
                <p class="text-sm text-slate-600 dark:text-slate-300">آیا از حذف این مقاله مطمئن هستید؟ دسته‌بندی‌ها، برچسب‌ها و نظرات آن نیز حذف خواهند شد.</p>
                <div class="flex gap-3 mt-5">
                    <button wire:click="deletePost" wire:loading.attr="disabled"
                            class="flex-1 rounded-xl bg-rose-500 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-rose-500/25 transition hover:bg-rose-600 disabled:opacity-50">
                        بله، حذف شود
                    </button>
                    <button wire:click="$set('confirmingDelete', false)"
                            class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-700">
                        لغو
                    </button>
                </div>
            </div>
        </div>
    @endif

    @once
        @push('scripts')
            <script>
                // انیمیشن ورود کارت‌ها با stagger
                document.addEventListener('DOMContentLoaded', () => {
                    document.querySelectorAll('.chart-card').forEach((card, i) => {
                        setTimeout(() => card.classList.add('show'), i * 60);
                    });
                });
            </script>
        @endpush
    @endonce
</div>
