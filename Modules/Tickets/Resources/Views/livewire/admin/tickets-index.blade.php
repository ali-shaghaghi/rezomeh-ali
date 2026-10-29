<div>
    {{-- هدر صفحه --}}
    <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
        <div>
            <h2 class="text-2xl font-bold sm:text-3xl">مدیریت تیکت‌ها</h2>
            <p class="mt-1 text-slate-500 dark:text-slate-400">{{ $tickets->total() }} تیکت ثبت‌شده</p>
        </div>
    </div>

    {{-- کارت‌های آمار --}}
    <div class="grid grid-cols-2 gap-4 mb-6 animate-slide-up lg:grid-cols-5">
        <div class="chart-card">
            <p class="text-xs text-slate-500 dark:text-slate-400">کل تیکت‌ها</p>
            <p class="text-2xl font-bold gradient-text">{{ number_format($stats['total']) }}</p>
        </div>
        <div class="chart-card">
            <p class="text-xs text-slate-500 dark:text-slate-400">باز</p>
            <p class="text-2xl font-bold text-emerald-500">{{ number_format($stats['open']) }}</p>
        </div>
        <div class="chart-card">
            <p class="text-xs text-slate-500 dark:text-slate-400">در حال بررسی</p>
            <p class="text-2xl font-bold text-primary-500">{{ number_format($stats['in_progress']) }}</p>
        </div>
        <div class="chart-card">
            <p class="text-xs text-slate-500 dark:text-slate-400">در انتظار پاسخ</p>
            <p class="text-2xl font-bold text-amber-500">{{ number_format($stats['waiting']) }}</p>
        </div>
        <div class="chart-card">
            <p class="text-xs text-slate-500 dark:text-slate-400">بسته شده</p>
            <p class="text-2xl font-bold text-slate-400">{{ number_format($stats['closed']) }}</p>
        </div>
    </div>

    {{-- جستجو و فیلتر ریاکتیو --}}
    <div class="mb-6 animate-slide-up chart-card">
        <div class="flex flex-wrap gap-3">
            <div class="relative flex-1 min-w-[200px]">
                <span class="absolute -translate-y-1/2 pointer-events-none right-3 top-1/2 text-slate-400">
                    <x-icon name="search" size="4" />
                </span>
                <input type="text" wire:model.live.debounce.400ms="search" placeholder="جستجوی موضوع، متن یا کاربر..."
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pr-10 pl-4 text-sm transition focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-surface-800" />
            </div>
            <select wire:model.live="status"
                    class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm transition focus:border-primary-400 focus:outline-none dark:border-slate-700 dark:bg-surface-800">
                <option value="">همه وضعیت‌ها</option>
                <option value="open">باز</option>
                <option value="in_progress">در حال بررسی</option>
                <option value="waiting">در انتظار پاسخ</option>
                <option value="closed">بسته شده</option>
            </select>
            <select wire:model.live="priority"
                    class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm transition focus:border-primary-400 focus:outline-none dark:border-slate-700 dark:bg-surface-800">
                <option value="">همه اولویت‌ها</option>
                <option value="low">پایین</option>
                <option value="medium">متوسط</option>
                <option value="high">بالا</option>
                <option value="urgent">فوری</option>
            </select>
            @if($search || $status || $priority)
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
    <div wire:loading.delay.200ms class="fixed z-40 items-center hidden gap-2 px-4 py-2 text-xs font-medium text-white rounded-full shadow-lg bottom-6 left-6 bg-primary-500/90 backdrop-blur-sm" wire:target="search, status, priority, resetFilters, quickStatus, quickPriority, deleteTicket">
        <svg class="w-3.5 h-3.5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
        </svg>
        در حال بارگذاری...
    </div>

    {{-- گرید تیکت‌ها — بدون wire:loading.remove تا صفحه فلیکر نزند --}}
    <div class="grid gap-5 animate-slide-up sm:grid-cols-2 xl:grid-cols-3" wire:loading.class="pointer-events-none opacity-60" wire:target="quickStatus, quickPriority, deleteTicket">
        @forelse($tickets as $ticket)
            <article class="flex flex-col p-5 chart-card group">
                <div class="flex items-start justify-between gap-2 mb-3">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="flex items-center justify-center w-8 h-8 rounded-xl bg-primary-100 text-primary-600 dark:bg-primary-900/40 dark:text-primary-400">
                                <x-icon name="mail" size="4" />
                            </span>
                            <span class="text-xs text-slate-400 dark:text-slate-500">#{{ $ticket->id }}</span>
                            @if($ticket->replies->count() > 0)
                                <span class="px-2 py-0.5 text-xs rounded-full bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400">{{ $ticket->replies->count() }} پاسخ</span>
                            @endif
                        </div>
                        <h3 class="font-bold truncate">{{ $ticket->subject }}</h3>
                        <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ $ticket->user?->name }} — {{ $ticket->created_at->format('Y/m/d H:i') }}</p>
                    </div>
                    <span class="shrink-0 px-2.5 py-1 text-xs font-medium rounded-full {{ $ticket->status === 'open' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' : ($ticket->status === 'closed' ? 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400' : ($ticket->status === 'waiting' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300' : 'bg-primary-100 text-primary-700 dark:bg-primary-900/40 dark:text-primary-300')) }}">
                        {{ $ticket->status_label }}
                    </span>
                </div>

                {{-- خلاصه پیام --}}
                <p class="mb-3 text-sm text-slate-500 dark:text-slate-400 line-clamp-2">{{ \Illuminate\Support\Str::limit($ticket->message, 100) }}</p>

                {{-- اولویت و دسته --}}
                <div class="flex flex-wrap items-center gap-2 mb-3 text-xs">
                    <span class="px-2 py-1 rounded-lg {{ $ticket->priority === 'urgent' ? 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300' : ($ticket->priority === 'high' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300') }}">
                        اولویت: {{ $ticket->priority_label }}
                    </span>
                    @if($ticket->category)
                        <span class="px-2 py-1 rounded-lg bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300">{{ $ticket->category }}</span>
                    @endif
                </div>

                {{-- تغییر سریع وضعیت (ریاکتیو) --}}
                @can('update', $ticket)
                    <select wire:change="quickStatus({{ $ticket->id }}, $event.target.value)"
                            class="w-full px-3 py-2 mb-2 text-xs transition border rounded-lg border-slate-200 bg-slate-50 focus:outline-none dark:border-slate-600 dark:bg-slate-700/50 dark:text-white">
                        @foreach(['open' => 'باز', 'in_progress' => 'در حال بررسی', 'waiting' => 'در انتظار پاسخ', 'closed' => 'بسته شده'] as $st => $stLabel)
                            <option value="{{ $st }}" {{ $ticket->status === $st ? 'selected' : '' }}>{{ $stLabel }}</option>
                        @endforeach
                    </select>
                @endcan

                {{-- دکمه‌های اصلی --}}
                <div class="flex gap-2 pt-3 mt-auto border-t border-slate-100 dark:border-slate-700">
                    <a href="{{ route('admin.tickets.show', $ticket) }}"
                       class="flex-1 py-2 text-xs font-medium text-center transition border rounded-lg border-slate-200 hover:border-primary-300 dark:border-slate-600">
                        مشاهده و پاسخ
                    </a>
                    @can('delete', $ticket)
                        <button wire:click="confirmDelete({{ $ticket->id }})"
                                class="flex-1 py-2 text-xs font-medium transition border rounded-lg border-slate-200 text-rose-500 hover:bg-rose-50 dark:border-slate-600 dark:text-rose-400 dark:hover:bg-rose-900/30">
                            حذف
                        </button>
                    @endcan
                </div>
            </article>
        @empty
            <div class="py-16 text-center col-span-full chart-card">
                <span class="flex mx-auto mb-4 w-fit text-slate-300 dark:text-slate-600">
                    <x-icon name="mail" size="12" />
                </span>
                <p class="font-medium text-slate-600 dark:text-slate-300">{{ $search ? 'تیکتی مطابق جستجوی شما یافت نشد' : 'هنوز تیکتی ثبت نشده است' }}</p>
                <p class="mt-1 text-sm text-slate-400 dark:text-slate-500">{{ $search ? 'عبارت دیگری را جستجو کنید یا فیلترها را حذف کنید' : 'تیکت‌های پشتیبانی اینجا نمایش داده می‌شوند' }}</p>
            </div>
        @endforelse
    </div>

    {{-- صفحه‌بندی --}}
    @if($tickets->hasPages())
        <div class="mt-6">
            {{ $tickets->links() }}
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
                            <h3 class="text-base font-bold">حذف تیکت</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">این عملیات قابل بازگشت نیست</p>
                        </div>
                    </div>
                    <button wire:click="$set('confirmingDelete', false)" class="p-2 transition rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-700 dark:hover:text-slate-300">
                        <x-icon name="close" />
                    </button>
                </div>
                <p class="text-sm text-slate-600 dark:text-slate-300">آیا از حذف این تیکت مطمئن هستید؟ تمام پاسخ‌های آن نیز حذف خواهند شد.</p>
                <div class="flex gap-3 mt-5">
                    <button wire:click="deleteTicket" wire:loading.attr="disabled"
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
