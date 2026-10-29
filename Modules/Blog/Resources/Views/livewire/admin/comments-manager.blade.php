<div>
    {{-- هدر صفحه --}}
    <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
        <div>
            <h2 class="text-2xl font-bold sm:text-3xl">مدیریت نظرات</h2>
            <p class="mt-1 text-slate-500 dark:text-slate-400">{{ $comments->total() }} نظر ثبت‌شده</p>
        </div>
    </div>

    {{-- کارت‌های آمار --}}
    <div class="grid grid-cols-2 gap-4 mb-6 animate-slide-up lg:grid-cols-4">
        <div class="chart-card">
            <p class="text-xs text-slate-500 dark:text-slate-400">کل نظرات</p>
            <p class="text-2xl font-bold gradient-text">{{ number_format($stats['total']) }}</p>
        </div>
        <div class="chart-card">
            <p class="text-xs text-slate-500 dark:text-slate-400">در انتظار تایید</p>
            <p class="text-2xl font-bold text-amber-500">{{ number_format($stats['pending']) }}</p>
        </div>
        <div class="chart-card">
            <p class="text-xs text-slate-500 dark:text-slate-400">تایید شده</p>
            <p class="text-2xl font-bold text-emerald-500">{{ number_format($stats['approved']) }}</p>
        </div>
        <div class="chart-card">
            <p class="text-xs text-slate-500 dark:text-slate-400">رد شده</p>
            <p class="text-2xl font-bold text-rose-500">{{ number_format($stats['rejected']) }}</p>
        </div>
    </div>

    {{-- جستجو و فیلتر --}}
    <div class="mb-6 animate-slide-up chart-card">
        <div class="flex flex-wrap gap-3">
            <div class="relative flex-1 min-w-[200px]">
                <span class="absolute -translate-y-1/2 pointer-events-none right-3 top-1/2 text-slate-400">
                    <x-icon name="search" size="4" />
                </span>
                <input type="text" wire:model.live.debounce.400ms="search" placeholder="جستجوی متن نظر یا نام کاربر..."
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pr-10 pl-4 text-sm transition focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-surface-800" />
            </div>
            <select wire:model.live="statusFilter"
                    class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm transition focus:border-primary-400 focus:outline-none dark:border-slate-700 dark:bg-surface-800">
                <option value="">همه وضعیت‌ها</option>
                <option value="pending">در انتظار تایید</option>
                <option value="approved">تایید شده</option>
                <option value="rejected">رد شده</option>
            </select>
            @if($search || $statusFilter)
                <button wire:click="resetFilters" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-600 transition hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-700">
                    حذف فیلترها
                </button>
            @endif
        </div>
    </div>

    {{-- نوتیفیکیشن موفقیت --}}
    @if($successToast)
        <div class="px-4 py-3 mb-6 text-sm rounded-xl bg-emerald-100 text-emerald-700 animate-slide-up dark:bg-emerald-900/30 dark:text-emerald-300"
             x-data="{ show: true }" x-init="setTimeout(() => show = false, 3500)" x-show="show" x-transition.opacity.duration.500ms>
            {{ $successMessage }}
        </div>
    @endif

    {{-- ایندیکیتور لودینگ — شفاف و بدون جابجایی صفحه --}}
    <div wire:loading.delay.200ms class="fixed z-40 items-center hidden gap-2 px-4 py-2 text-xs font-medium text-white rounded-full shadow-lg bottom-6 left-6 bg-primary-500/90 backdrop-blur-sm" wire:target="search, statusFilter, resetFilters, approve, reject, pend, deleteComment, startReply, submitReply">
        <svg class="w-3.5 h-3.5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
        </svg>
        در حال بارگذاری...
    </div>

    {{-- لیست نظرات — بدون wire:loading.remove تا صفحه فلیکر نزند --}}
    <div class="space-y-4 animate-slide-up" wire:loading.class="pointer-events-none opacity-60" wire:target="approve, reject, pend, deleteComment, submitReply">
        @forelse($comments as $comment)
            <section class="chart-card">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-2 mb-2">
                            <span class="flex items-center justify-center w-8 h-8 text-xs font-bold rounded-full {{ $comment->admin_id ? 'bg-accent-100 text-accent-600 dark:bg-accent-900/40 dark:text-accent-400' : 'bg-primary-100 text-primary-600 dark:bg-primary-900/40 dark:text-primary-400' }}">
                                {{ mb_substr($comment->authorName(), 0, 1) }}
                            </span>
                            <span class="text-sm font-bold">{{ $comment->authorName() }}</span>
                            @if ($comment->admin_id)
                                <span class="px-2 py-0.5 text-xs font-bold rounded-lg bg-accent-100 text-accent-700 dark:bg-accent-900/40 dark:text-accent-300">پاسخ ادمین</span>
                            @endif
                            <span class="px-2 py-0.5 text-xs rounded-lg {{ $comment->status === 'approved' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' : ($comment->status === 'pending' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300' : 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300') }}">
                                {{ $comment->status_label }}
                            </span>
                            <span class="text-xs text-slate-400 dark:text-slate-500">{{ $comment->created_at->format('Y/m/d H:i') }}</span>
                        </div>
                        @if ($comment->parent_id && $comment->parent)
                            <p class="mb-2 text-xs text-slate-400 dark:text-slate-500">
                                ↩ پاسخ به «{{ \Illuminate\Support\Str::limit($comment->parent->content, 80) }}»
                                — {{ $comment->parent->authorName() }}
                            </p>
                        @endif
                        <p class="mb-2 text-sm leading-6 text-slate-600 dark:text-slate-300">{{ $comment->content }}</p>
                        @if($comment->blogPost)
                            <a href="{{ route('admin.blog.show', $comment->blogPost) }}" class="text-xs font-medium text-primary-500 hover:text-primary-600 dark:text-primary-400">
                                روی مقاله: {{ $comment->blogPost->title }}
                            </a>
                        @endif
                    </div>

                    {{-- دکمه‌های عملیات ریاکتیو --}}
                    @can('update', $comment)
                        <div class="flex flex-wrap gap-2">
                            <button wire:click="startReply({{ $comment->id }})" wire:loading.attr="disabled"
                                    class="px-3 py-1.5 text-xs font-medium transition rounded-lg bg-primary-100 text-primary-700 hover:bg-primary-200 dark:bg-primary-900/40 dark:text-primary-300 dark:hover:bg-primary-900/60">
                                پاسخ
                            </button>
                            @if($comment->status !== 'approved')
                                <button wire:click="approve({{ $comment->id }})" wire:loading.attr="disabled"
                                        class="px-3 py-1.5 text-xs font-medium transition rounded-lg bg-emerald-100 text-emerald-700 hover:bg-emerald-200 dark:bg-emerald-900/40 dark:text-emerald-300 dark:hover:bg-emerald-900/60">
                                    تایید
                                </button>
                            @endif
                            @if($comment->status !== 'rejected')
                                <button wire:click="reject({{ $comment->id }})" wire:loading.attr="disabled"
                                        class="px-3 py-1.5 text-xs font-medium transition rounded-lg bg-rose-100 text-rose-700 hover:bg-rose-200 dark:bg-rose-900/40 dark:text-rose-300 dark:hover:bg-rose-900/60">
                                    رد
                                </button>
                            @endif
                            @if($comment->status !== 'pending')
                                <button wire:click="pend({{ $comment->id }})" wire:loading.attr="disabled"
                                        class="px-3 py-1.5 text-xs font-medium transition rounded-lg bg-amber-100 text-amber-700 hover:bg-amber-200 dark:bg-amber-900/40 dark:text-amber-300 dark:hover:bg-amber-900/60">
                                    در انتظار
                                </button>
                            @endif
                        </div>
                    @endcan
                    @can('delete', $comment)
                        <button wire:click="confirmDelete({{ $comment->id }})"
                                class="px-3 py-1.5 text-xs font-medium transition rounded-lg bg-slate-100 text-rose-500 hover:bg-rose-100 dark:bg-slate-700 dark:text-rose-400 dark:hover:bg-rose-900/30">
                            حذف
                        </button>
                    @endcan
                </div>
            </section>
        @empty
            <div class="py-16 text-center chart-card">
                <span class="flex mx-auto mb-4 w-fit text-slate-300 dark:text-slate-600">
                    <x-icon name="mail" size="12" />
                </span>
                <p class="font-medium text-slate-600 dark:text-slate-300">{{ $search ? 'نظری مطابق جستجوی شما یافت نشد' : 'هنوز نظری ثبت نشده است' }}</p>
            </div>
        @endforelse
    </div>

    {{-- صفحه‌بندی --}}
    @if($comments->hasPages())
        <div class="mt-6">
            {{ $comments->links() }}
        </div>
    @endif

    {{-- مودال پاسخ ادمین به نظر --}}
    @if($replying)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
            <div class="w-full max-w-xl p-5 bg-white border shadow-2xl rounded-2xl border-slate-200/50 animate-slide-up dark:border-slate-700/50 dark:bg-surface-800">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-3">
                        <span class="flex items-center justify-center h-9 w-9 rounded-xl bg-primary-100 text-primary-600 dark:bg-primary-900/40 dark:text-primary-400">
                            <x-icon name="edit" size="4" />
                        </span>
                        <div>
                            <h3 class="text-base font-bold">پاسخ به نظر</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">پاسخ شما پس از تأیید منتشر می‌شود</p>
                        </div>
                    </div>
                    <button wire:click="cancelReply" class="p-2 transition rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-700 dark:hover:text-slate-300">
                        <x-icon name="close" />
                    </button>
                </div>

                @php($replyParent = \Modules\Core\Models\Comment::find($replyId))
                @if($replyParent)
                    <div class="p-3 mb-4 text-xs rounded-xl bg-slate-50 text-slate-600 dark:bg-slate-700/40 dark:text-slate-300">
                        <span class="font-bold">{{ $replyParent->authorName() }}:</span>
                        {{ \Illuminate\Support\Str::limit($replyParent->content, 200) }}
                    </div>
                @endif

                <textarea wire:model="replyContent" rows="4" placeholder="متن پاسخ خود را بنویسید..."
                          class="w-full px-3 py-2 text-sm transition border rounded-xl border-slate-200 bg-white focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white"></textarea>
                @error('replyContent') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror

                <div class="flex gap-3 mt-4">
                    <button wire:click="submitReply" wire:loading.attr="disabled" wire:target="submitReply"
                            class="flex-1 rounded-xl bg-primary-500 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-primary-500/25 transition hover:bg-primary-600 disabled:opacity-50">
                        ثبت پاسخ
                    </button>
                    <button wire:click="cancelReply"
                            class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-700">
                        لغو
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- مودال تایید حذف --}}
    @if($confirmingDelete)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
            <div class="w-full max-w-md p-5 bg-white border shadow-2xl rounded-2xl border-slate-200/50 animate-slide-up dark:border-slate-700/50 dark:bg-surface-800">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-3">
                        <span class="flex items-center justify-center h-9 w-9 rounded-xl bg-rose-100 text-rose-600 dark:bg-rose-900/40 dark:text-rose-400">
                            <x-icon name="trash" size="4" />
                        </span>
                        <div>
                            <h3 class="text-base font-bold">حذف نظر</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">این عملیات قابل بازگشت نیست</p>
                        </div>
                    </div>
                    <button wire:click="$set('confirmingDelete', false)" class="p-2 transition rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-700 dark:hover:text-slate-300">
                        <x-icon name="close" />
                    </button>
                </div>
                <p class="text-sm text-slate-600 dark:text-slate-300">آیا از حذف این نظر مطمئن هستید؟</p>
                <div class="flex gap-3 mt-5">
                    <button wire:click="deleteComment" wire:loading.attr="disabled"
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
</div>
