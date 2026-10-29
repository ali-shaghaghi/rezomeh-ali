<div>
    {{-- هدر صفحه --}}
    <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
        <div>
            <h2 class="text-2xl font-bold sm:text-3xl">مدیریت سفارشات</h2>
            <p class="mt-1 text-slate-500 dark:text-slate-400">{{ $orders->total() }} سفارش ثبت‌شده — مرتب بر اساس ددلاین</p>
        </div>
        @can('create', \Modules\Orders\Models\Order::class)
            <button wire:click="openCreateForm"
                    class="group flex items-center gap-2 rounded-xl bg-primary-500 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary-500/25 transition-all duration-200 hover:shadow-xl hover:shadow-primary-500/35 hover:-translate-y-0.5 active:translate-y-0">
                <span class="flex items-center justify-center w-5 h-5 transition duration-300 rounded-md bg-white/20 group-hover:rotate-90">
                    <x-icon name="plus" size="3" />
                </span>
                افزودن سفارش جدید
            </button>
        @endcan
    </div>

    {{-- کارت‌های آمار --}}
    <div class="grid gap-4 mb-8 animate-slide-up sm:grid-cols-2 xl:grid-cols-5">
        <div class="chart-card">
            <p class="text-xs text-slate-500 dark:text-slate-400">کل سفارشات</p>
            <p class="text-2xl font-bold gradient-text">{{ number_format($stats['total']) }}</p>
        </div>
        <div class="chart-card">
            <p class="text-xs text-slate-500 dark:text-slate-400">در انتظار</p>
            <p class="text-2xl font-bold text-amber-500">{{ number_format($stats['pending']) }}</p>
        </div>
        <div class="chart-card">
            <p class="text-xs text-slate-500 dark:text-slate-400">در حال توسعه</p>
            <p class="text-2xl font-bold text-violet-500">{{ number_format($stats['development']) }}</p>
        </div>
        <div class="chart-card">
            <p class="text-xs text-slate-500 dark:text-slate-400">تکمیل شده</p>
            <p class="text-2xl font-bold text-emerald-500">{{ number_format($stats['completed']) }}</p>
        </div>
        <div class="chart-card">
            <p class="text-xs text-slate-500 dark:text-slate-400">درآمد کل</p>
            <p class="text-2xl font-bold text-primary-600 dark:text-primary-400">{{ number_format($stats['total_revenue'] / 1000000, 1) }}م</p>
        </div>
    </div>

    {{-- جستجو و فیلتر ریاکتیو --}}
    <div class="mb-6 animate-slide-up chart-card">
        <div class="flex flex-wrap gap-3">
            <div class="relative flex-1 min-w-[200px]">
                <span class="absolute -translate-y-1/2 pointer-events-none right-3 top-1/2 text-slate-400">
                    <x-icon name="search" size="4" />
                </span>
                <input type="text" wire:model.live.debounce.400ms="search" placeholder="جستجوی شماره، عنوان یا کاربر..."
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pr-10 pl-4 text-sm transition focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-surface-800" />
            </div>
            <select wire:model.live="status"
                    class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm transition focus:border-primary-400 focus:outline-none dark:border-slate-700 dark:bg-surface-800">
                <option value="">همه وضعیت‌ها</option>
                <option value="pending">در انتظار بررسی</option>
                <option value="review">در حال بررسی</option>
                <option value="accepted">پذیرفته شده</option>
                <option value="development">در حال توسعه</option>
                <option value="completed">تکمیل شده</option>
                <option value="cancelled">لغو شده</option>
            </select>
            <select wire:model.live="priority"
                    class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm transition focus:border-primary-400 focus:outline-none dark:border-slate-700 dark:bg-surface-800">
                <option value="">همه اولویت‌ها</option>
                <option value="low">کم</option>
                <option value="medium">متوسط</option>
                <option value="high">زیاد</option>
                <option value="urgent">فوری</option>
            </select>
            <select wire:model.live="sortBy"
                    class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm transition focus:border-primary-400 focus:outline-none dark:border-slate-700 dark:bg-surface-800">
                <option value="deadline">نزدیک‌ترین ددلاین (برنامه کاری)</option>
                <option value="deadline_desc">دورترین ددلاین</option>
                <option value="status">مرحله اجرا</option>
                <option value="newest">جدیدترین</option>
                <option value="amount">بیشترین مبلغ</option>
            </select>
            @if($search || $status || $priority || $sortBy !== 'deadline')
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

    {{-- ایندیکیتور لودینگ — فقط هنگام فیلتر/جستجو، شفاف و بدون جابجایی صفحه --}}
    <div wire:loading.delay.200ms class="fixed z-40 items-center hidden gap-2 px-4 py-2 text-xs font-medium text-white rounded-full shadow-lg bottom-6 left-6 bg-primary-500/90 backdrop-blur-sm" wire:target="search, status, priority, sortBy, resetFilters, saveOrder, deleteOrder, savePayment, saveTask, deleteTask, quickTaskProgress, toggleTaskDone">
        <svg class="w-3.5 h-3.5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
        </svg>
        در حال بارگذاری...
    </div>

    {{-- گرید سفارش‌ها — بدون wire:loading.remove تا صفحه فلیکر نزند --}}
    <div class="grid gap-5 animate-slide-up sm:grid-cols-2 xl:grid-cols-3" wire:loading.class="pointer-events-none opacity-60" wire:target="saveOrder, deleteOrder, quickStatus, quickPriority, savePayment, saveTask, deleteTask">
        @forelse($orders as $order)
            <article class="flex flex-col p-5 chart-card group">
                <div class="flex items-start justify-between gap-2 mb-3">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="flex items-center justify-center w-8 h-8 rounded-xl bg-primary-100 text-primary-600 dark:bg-primary-900/40 dark:text-primary-400">
                                <x-icon name="orders" size="4" />
                            </span>
                            <span class="font-mono text-xs text-slate-400 dark:text-slate-500" dir="ltr">{{ $order->order_number }}</span>
                        </div>
                        <h3 class="font-bold truncate">{{ $order->title }}</h3>
                        <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ $order->user?->name }}</p>
                    </div>
                    <span class="shrink-0 px-2.5 py-1 text-xs font-medium rounded-full {{ $order->status === 'completed' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' : ($order->status === 'cancelled' ? 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300' : ($order->status === 'pending' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300' : 'bg-primary-100 text-primary-700 dark:bg-primary-900/40 dark:text-primary-300')) }}">
                        {{ $order->status_label }}
                    </span>
                </div>

                {{-- پیشرفت پروژه (تسک‌محور) --}}
                <div class="mb-3">
                    <div class="flex items-center justify-between mb-1 text-xs">
                        <span class="flex items-center gap-1 text-slate-500 dark:text-slate-400">
                            <x-icon name="analytics" size="3.5" />
                            پیشرفت پروژه
                        </span>
                        <span class="font-bold {{ $order->progress_percent >= 100 ? 'text-emerald-600 dark:text-emerald-400' : 'text-primary-600 dark:text-primary-400' }}">
                            {{ $order->progress_percent }}٪
                            @if($order->tasks->isNotEmpty())
                                <span class="font-normal text-slate-400">({{ $order->done_tasks_count }}/{{ $order->tasks->count() }} تسک)</span>
                            @endif
                        </span>
                    </div>
                    <div class="w-full h-2 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-700">
                        <div class="h-full transition-all duration-500 rounded-full {{ $order->progress_percent >= 100 ? 'bg-gradient-to-l from-emerald-500 to-emerald-400' : 'bg-gradient-to-l from-violet-500 to-primary-500' }}"
                             style="width: {{ $order->progress_percent }}%"></div>
                    </div>
                </div>

                {{-- مبلغ و پرداخت --}}
                <div class="mb-3">
                    <div class="flex items-center justify-between mb-1 text-xs">
                        <span class="text-slate-500 dark:text-slate-400">مبلغ: {{ number_format($order->amount) }} تومان</span>
                        <span class="{{ $order->is_paid ? 'text-emerald-600 dark:text-emerald-400 font-bold' : 'text-slate-400' }}">
                            {{ $order->is_paid ? 'تسویه شده' : 'پرداخت‌شده: ' . number_format($order->paid_amount) }}
                        </span>
                    </div>
                    @if((float) $order->amount > 0)
                        <div class="w-full h-2 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-700">
                            <div class="h-full transition-all duration-500 rounded-full bg-gradient-to-l from-emerald-500 to-primary-500"
                                 style="width: {{ min(100, (float) $order->paid_amount / max(1, (float) $order->amount) * 100) }}%"></div>
                        </div>
                    @endif
                </div>

                {{-- اولویت و ددلاین --}}
                <div class="flex flex-wrap items-center gap-2 mb-3 text-xs">
                    <span class="px-2 py-1 rounded-lg {{ $order->priority === 'urgent' ? 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300' : ($order->priority === 'high' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300') }}">
                        اولویت: {{ $order->priority_label }}
                    </span>
                    @if($order->deadline)
                        @php($days = $order->days_remaining)
                        <span class="flex items-center gap-1 px-2 py-1 rounded-lg {{ match($order->deadline_status) {
                            'overdue' => 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300',
                            'urgent' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
                            'soon' => 'bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-300',
                            default => 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300',
                        } }}">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            {{ $order->deadline->format('Y/m/d') }}
                            @if($days !== null)
                                @if($days < 0)
                                    <span class="font-bold">({{ abs($days) }} روز گذشته)</span>
                                @elseif($days === 0)
                                    <span class="font-bold">(امروز!)</span>
                                @else
                                    <span class="font-bold">({{ $days }} روز)</span>
                                @endif
                            @endif
                        </span>
                    @endif
                </div>

                {{-- تغییر سریع وضعیت (ریاکتیو) --}}
                @can('update', $order)
                    <select wire:change="quickStatus({{ $order->id }}, $event.target.value)"
                            class="w-full px-3 py-2 mb-2 text-xs transition border rounded-lg border-slate-200 bg-slate-50 focus:outline-none dark:border-slate-600 dark:bg-slate-700/50 dark:text-white">
                        @foreach(['pending' => 'در انتظار بررسی', 'review' => 'در حال بررسی', 'accepted' => 'پذیرفته شده', 'development' => 'در حال توسعه', 'completed' => 'تکمیل شده', 'cancelled' => 'لغو شده'] as $st => $stLabel)
                            <option value="{{ $st }}" {{ $order->status === $st ? 'selected' : '' }}>{{ $stLabel }}</option>
                        @endforeach
                    </select>
                @endcan

                {{-- دکمه‌های اصلی --}}
                <div class="grid grid-cols-5 gap-1.5 pt-3 mt-auto border-t border-slate-100 dark:border-slate-700">
                    <a href="{{ route('admin.orders.show', $order) }}"
                    class="flex items-center justify-center py-2 text-xs font-medium transition border rounded-lg border-slate-200 hover:border-primary-300 dark:border-slate-600" title="مشاهده">
                        <x-icon name="eye" size="4" />
                    </a>
                    @can('update', $order)
                        <button wire:click="openTasksModal({{ $order->id }})"
                                class="flex items-center justify-center py-2 text-xs font-medium transition border rounded-lg border-slate-200 text-violet-600 hover:border-violet-300 dark:border-slate-600 dark:text-violet-400" title="برنامه کاری و تسک‌ها">
                            <x-icon name="analytics" size="4" />
                        </button>
                        <button wire:click="openEditForm({{ $order->id }})"
                                class="flex items-center justify-center py-2 text-xs font-medium transition border rounded-lg border-slate-200 hover:border-primary-300 dark:border-slate-600" title="ویرایش">
                            <x-icon name="edit" size="4" />
                        </button>
                        <button wire:click="openPaymentModal({{ $order->id }})"
                                class="flex items-center justify-center py-2 text-xs font-medium transition border rounded-lg border-slate-200 text-emerald-600 hover:border-emerald-300 dark:border-slate-600 dark:text-emerald-400" title="ثبت پرداخت">
                            <x-icon name="wallet" size="4" />
                        </button>
                    @endcan
                    @can('delete', $order)
                        <button wire:click="confirmDelete({{ $order->id }})"
                                class="flex items-center justify-center py-2 text-xs font-medium transition border rounded-lg border-slate-200 text-rose-500 hover:bg-rose-50 dark:border-slate-600 dark:text-rose-400 dark:hover:bg-rose-900/30" title="حذف">
                            <x-icon name="trash" size="4" />
                        </button>
                    @endcan
                </div>
            </article>
        @empty
            <div class="py-16 text-center col-span-full chart-card">
                <span class="flex mx-auto mb-4 w-fit text-slate-300 dark:text-slate-600">
                    <x-icon name="orders" size="12" />
                </span>
                <p class="font-medium text-slate-600 dark:text-slate-300">{{ $search ? 'سفارشی مطابق جستجوی شما یافت نشد' : 'هنوز سفارشی ثبت نشده است' }}</p>
                <p class="mt-1 text-sm text-slate-400 dark:text-slate-500">{{ $search ? 'عبارت دیگری را جستجو کنید یا فیلترها را حذف کنید' : 'اولین سفارش را ثبت کنید' }}</p>
            </div>
        @endforelse
    </div>

    {{-- صفحه‌بندی (Tailwind RTL) --}}
    @if($orders->hasPages())
        {{ $orders->links() }}
    @endif

    {{-- ==================== فرم ایجاد/ویرایش (مودال بزرگ) ==================== --}}
    @if($showForm)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm animate-slide-up sm:p-4">
            <div class="w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto rounded-2xl border border-slate-200/50 bg-white p-4 shadow-2xl animate-slide-up dark:border-slate-700/50 dark:bg-surface-800 sm:p-5">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <span class="flex items-center justify-center h-9 w-9 rounded-xl bg-primary-100 text-primary-600 dark:bg-primary-900/40 dark:text-primary-400">
                            <x-icon name="orders" size="4" />
                        </span>
                        <div>
                            <h3 class="text-base font-bold">{{ $editing ? 'ویرایش سفارش' . ($editingOrder?->order_number ? ': ' . $editingOrder->order_number : '') : 'افزودن سفارش جدید' }}</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $editing ? 'اطلاعات سفارش را به‌روزرسانی کنید' : 'اطلاعات سفارش را وارد کنید' }}</p>
                        </div>
                    </div>
                    <button wire:click="$set('showForm', false)" class="p-2 transition rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-700 dark:hover:text-slate-300">
                        <x-icon name="close" />
                    </button>
                </div>

                <form wire:submit.prevent="saveOrder" class="space-y-3">
                    {{-- عنوان و کاربر --}}
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">عنوان سفارش <span class="text-red-500">*</span></label>
                            <input type="text" wire:model.blur="title" required class="w-full px-3 py-2 text-sm transition border rounded-xl border-slate-200 bg-slate-50 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white" placeholder="مثال: طراحی سایت فروشگاهی" />
                            @error('title') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">کاربر سفارش‌دهنده <span class="text-red-500">*</span></label>
                            <input type="text" wire:model.blur="user_id" required dir="ltr" class="w-full px-3 py-2 text-sm transition border rounded-xl border-slate-200 bg-slate-50 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white" placeholder="شناسه کاربر" />
                            @error('user_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- وضعیت و اولویت --}}
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">وضعیت <span class="text-red-500">*</span></label>
                            <select wire:model.live="order_status" class="w-full px-3 py-2 text-sm transition border rounded-xl border-slate-200 bg-slate-50 focus:border-primary-400 focus:outline-none dark:border-slate-600 dark:bg-slate-700/50 dark:text-white">
                                <option value="pending">در انتظار بررسی</option>
                                <option value="review">در حال بررسی</option>
                                <option value="accepted">پذیرفته شده</option>
                                <option value="development">در حال توسعه</option>
                                <option value="completed">تکمیل شده</option>
                                <option value="cancelled">لغو شده</option>
                            </select>
                            @error('order_status') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">اولویت <span class="text-red-500">*</span></label>
                            <select wire:model.live="order_priority" class="w-full px-3 py-2 text-sm transition border rounded-xl border-slate-200 bg-slate-50 focus:border-primary-400 focus:outline-none dark:border-slate-600 dark:bg-slate-700/50 dark:text-white">
                                <option value="low">کم</option>
                                <option value="medium">متوسط</option>
                                <option value="high">زیاد</option>
                                <option value="urgent">فوری</option>
                            </select>
                            @error('order_priority') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- مبلغ و ددلاین + شروع کار --}}
                    <div class="grid gap-3 sm:grid-cols-3">
                        <div>
                            <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">مبلغ کل (تومان) <span class="text-red-500">*</span></label>
                            <input type="number" wire:model.blur="amount" required min="0" dir="ltr" class="w-full px-3 py-2 text-sm transition border rounded-xl border-slate-200 bg-slate-50 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white" placeholder="5000000" />
                            @error('amount') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">شروع کار</label>
                            <input type="date" wire:model.blur="start_date" dir="ltr" class="w-full px-3 py-2 text-sm transition border rounded-xl border-slate-200 bg-slate-50 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white" />
                            @error('start_date') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">ددلاین</label>
                            <input type="date" wire:model.blur="deadline" dir="ltr" class="w-full px-3 py-2 text-sm transition border rounded-xl border-slate-200 bg-slate-50 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white" />
                            @error('deadline') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- توضیحات --}}
                    <div>
                        <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">توضیحات</label>
                        <textarea wire:model.blur="description" rows="4" class="w-full px-3 py-2 text-sm transition border resize-none rounded-xl border-slate-200 bg-slate-50 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white" placeholder="توضیحات سفارش..."></textarea>
                        @error('description') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- دکمه‌ها --}}
                    <div class="flex gap-3 pt-3">
                        <button type="submit" wire:loading.attr="disabled"
                                class="flex-1 rounded-xl bg-primary-500 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-primary-500/25 transition-all hover:shadow-lg hover:shadow-primary-500/35 disabled:opacity-50">
                            {{ $editing ? 'ذخیره تغییرات' : 'ایجاد سفارش' }}
                        </button>
                        <button type="button" wire:click="$set('showForm', false)"
                                class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-700">
                            لغو
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- ==================== مودال برنامه کاری (تسک‌ها) ==================== --}}
    @if($tasksModalOpen && $tasksOrder)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm animate-slide-up sm:p-4">
            <div class="w-full max-w-3xl mx-4 max-h-[90vh] overflow-y-auto rounded-2xl border border-slate-200/50 bg-white p-4 shadow-2xl animate-slide-up dark:border-slate-700/50 dark:bg-surface-800 sm:p-5">
                {{-- هدر مودال --}}
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center min-w-0 gap-3">
                        <span class="flex items-center justify-center w-10 h-10 rounded-xl bg-violet-100 text-violet-600 dark:bg-violet-900/40 dark:text-violet-400">
                            <x-icon name="analytics" size="5" />
                        </span>
                        <div class="min-w-0">
                            <h3 class="text-base font-bold truncate">برنامه کاری: {{ $tasksOrder->title }}</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400" dir="ltr">{{ $tasksOrder->order_number }}</p>
                        </div>
                    </div>
                    <button wire:click="$set('tasksModalOpen', false)" class="p-2 transition rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-700 dark:hover:text-slate-300">
                        <x-icon name="close" />
                    </button>
                </div>

                {{-- خلاصه پیشرفت کلی --}}
                @php($tasks = $tasksOrder->tasks)
                <div class="p-4 mb-4 rounded-xl bg-gradient-to-l from-violet-50 to-primary-50 dark:from-violet-950/30 dark:to-primary-950/20">
                    <div class="flex flex-wrap items-center justify-between gap-3 mb-2">
                        <div class="text-sm">
                            <span class="font-bold">{{ $tasksOrder->progress_percent }}٪</span>
                            <span class="text-slate-500 dark:text-slate-400">پیشرفت کلی پروژه</span>
                        </div>
                        <div class="flex gap-2 text-xs">
                            <span class="px-2.5 py-1 rounded-full bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 shadow-sm">{{ $tasks->where('status', 'done')->count() }} انجام شده</span>
                            <span class="px-2.5 py-1 rounded-full bg-white dark:bg-slate-800 text-primary-600 dark:text-primary-400 shadow-sm">{{ $tasks->where('status', 'in_progress')->count() }} در حال انجام</span>
                            <span class="px-2.5 py-1 rounded-full bg-white dark:bg-slate-800 text-slate-400 shadow-sm">{{ $tasks->where('status', 'todo')->count() }} باز</span>
                        </div>
                    </div>
                    <div class="w-full h-3 overflow-hidden rounded-full bg-white/80 dark:bg-slate-800/80">
                        <div class="h-full transition-all duration-700 rounded-full bg-gradient-to-l from-violet-500 to-primary-500"
                             style="width: {{ $tasksOrder->progress_percent }}%"></div>
                    </div>
                    @if($tasksOrder->start_date || $tasksOrder->deadline)
                        <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                            بازه کاری:
                            {{ $tasksOrder->start_date?->format('Y/m/d') ?? '—' }}
                            تا
                            {{ $tasksOrder->deadline?->format('Y/m/d') ?? '—' }}
                        </p>
                    @endif
                </div>

                {{-- لیست تسک‌ها --}}
                <div class="mb-4 space-y-2" wire:loading.class="pointer-events-none opacity-60" wire:target="toggleTaskDone, deleteTask, quickTaskProgress">
                    @forelse($tasks as $task)
                        <div class="p-3 rounded-xl border {{ $task->status === 'done' ? 'border-emerald-200 bg-emerald-50/50 dark:border-emerald-900/50 dark:bg-emerald-950/20' : 'border-slate-200 bg-slate-50/60 dark:border-slate-700 dark:bg-slate-700/40' }}">
                            <div class="flex items-start gap-3">
                                {{-- چک‌باکس انجام --}}
                                <button wire:click="toggleTaskDone({{ $task->id }})"
                                        class="mt-0.5 flex items-center justify-center w-5 h-5 rounded-lg border-2 transition {{ $task->status === 'done' ? 'bg-emerald-500 border-emerald-500 text-white' : 'border-slate-300 hover:border-primary-400 dark:border-slate-500' }}">
                                    @if($task->status === 'done')
                                        <x-icon name="check" size="3" />
                                    @endif
                                </button>

                                <div class="flex-1 min-w-0" wire:loading.class="opacity-60" wire:target="toggleTaskDone, deleteTask, quickTaskProgress">
                                    <p class="text-sm font-bold {{ $task->status === 'done' ? 'line-through text-slate-400' : '' }}">{{ $task->title }}</p>
                                    @if($task->description)
                                        <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ $task->description }}</p>
                                    @endif
                                    <div class="flex flex-wrap items-center gap-2 mt-1.5 text-[10px]">
                                        @if($task->start_date || $task->due_date)
                                            <span class="px-2 py-0.5 rounded-md bg-violet-100 text-violet-700 dark:bg-violet-900/40 dark:text-violet-300">
                                                {{ $task->start_date?->format('m/d') ?? '؟' }} تا {{ $task->due_date?->format('m/d') ?? '؟' }}
                                            </span>
                                        @endif
                                        <span class="px-2 py-0.5 rounded-md {{ $task->status === 'done' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' : ($task->status === 'in_progress' ? 'bg-primary-100 text-primary-700 dark:bg-primary-900/40 dark:text-primary-300' : 'bg-slate-200 text-slate-500 dark:bg-slate-600 dark:text-slate-300') }}">
                                            {{ $task->status_label }}
                                        </span>
                                    </div>
                                    {{-- نوار پیشرفت + اسلایدر سریع --}}
                                    <div class="flex items-center gap-2 mt-2">
                                        <input type="range" min="0" max="100" step="10"
                                               value="{{ $task->progress }}"
                                               wire:change="quickTaskProgress({{ $task->id }}, $event.target.value)"
                                               class="flex-1 h-1.5 accent-primary-500 cursor-pointer" />
                                        <span class="w-10 text-xs font-bold text-left text-primary-600 dark:text-primary-400" dir="ltr">{{ $task->progress }}٪</span>
                                    </div>
                                </div>

                                <div class="flex flex-col gap-1">
                                    <button wire:click="openTaskEdit({{ $task->id }})"
                                            class="p-1.5 rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-primary-600 dark:hover:bg-slate-600" title="ویرایش">
                                        <x-icon name="edit" size="4" />
                                    </button>
                                    <button wire:click="deleteTask({{ $task->id }})" wire:confirm="تسک حذف شود؟"
                                            class="p-1.5 rounded-lg text-rose-400 transition hover:bg-rose-50 hover:text-rose-600 dark:hover:bg-rose-900/30" title="حذف">
                                        <x-icon name="trash" size="4" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="py-10 text-center border-2 border-dashed rounded-xl border-slate-200 dark:border-slate-700">
                            <p class="font-medium text-slate-600 dark:text-slate-300">هنوز تسکی تعریف نشده است</p>
                            <p class="mt-1 text-xs text-slate-400">تسک اول را با «افزودن تسک جدید» تعریف کنید</p>
                        </div>
                    @endforelse
                </div>

                {{-- فرم افزودن/ویرایش تسک --}}
                <div class="p-4 border rounded-xl border-primary-200 bg-primary-50/50 dark:border-primary-800 dark:bg-primary-950/20">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="flex items-center gap-2 text-sm font-bold">
                            <x-icon name="{{ $taskEditing ? 'edit' : 'plus' }}" size="4" />
                            {{ $taskEditing ? 'ویرایش تسک' : 'افزودن تسک جدید' }}
                        </h4>
                        @if($taskEditing)
                            <button wire:click="openTaskCreate" class="text-xs text-primary-600 dark:text-primary-400 hover:underline">فرم جدید (لغو ویرایش)</button>
                        @endif
                    </div>

                    <form wire:submit.prevent="saveTask" class="space-y-3">
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">عنوان تسک <span class="text-red-500">*</span></label>
                                <input type="text" wire:model.blur="task_title" placeholder="مثال: طراحی دیتابیس پروژه" class="w-full px-3 py-2 text-sm transition bg-white border rounded-xl border-slate-200 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white" />
                                @error('task_title') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">شروع (مثلاً ۱ فروردین)</label>
                                <input type="date" wire:model.blur="task_start_date" dir="ltr" class="w-full px-3 py-2 text-sm transition bg-white border rounded-xl border-slate-200 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white" />
                                @error('task_start_date') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">پایان (مثلاً ۱۰ فروردین)</label>
                                <input type="date" wire:model.blur="task_due_date" dir="ltr" class="w-full px-3 py-2 text-sm transition bg-white border rounded-xl border-slate-200 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white" />
                                @error('task_due_date') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">وضعیت</label>
                                <select wire:model.live="task_status" class="w-full px-3 py-2 text-sm transition bg-white border rounded-xl border-slate-200 focus:border-primary-400 focus:outline-none dark:border-slate-600 dark:bg-slate-700/50 dark:text-white">
                                    <option value="todo">انجام نشده</option>
                                    <option value="in_progress">در حال انجام</option>
                                    <option value="done">انجام شده</option>
                                </select>
                            </div>
                            <div>
                                <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">درصد پیشرفت: <span class="font-bold text-primary-600 dark:text-primary-400">{{ $task_progress }}٪</span></label>
                                <input type="range" min="0" max="100" step="10" wire:model.live="task_progress" class="w-full h-1.5 mt-3 accent-primary-500 cursor-pointer" />
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">توضیحات</label>
                                <textarea wire:model.blur="task_description" rows="2" class="w-full px-3 py-2 text-sm transition bg-white border resize-none rounded-xl border-slate-200 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white" placeholder="توضیح تسک (اختیاری)"></textarea>
                                @error('task_description') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="flex gap-2">
                            <button type="submit" wire:loading.attr="disabled" wire:target="saveTask"
                                    class="rounded-xl bg-primary-500 px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-primary-500/25 transition-all disabled:opacity-50">
                                {{ $taskEditing ? 'ذخیره تغییرات تسک' : 'افزودن تسک' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- مودال ثبت پرداخت --}}
    @if($paymentModalOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm animate-slide-up sm:p-4">
            <div class="w-full max-w-md mx-4 max-h-[90vh] overflow-y-auto rounded-2xl border border-slate-200/50 bg-white p-4 shadow-2xl animate-slide-up dark:border-slate-700/50 dark:bg-surface-800 sm:p-5">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <span class="flex items-center justify-center h-9 w-9 rounded-xl bg-emerald-100 text-emerald-600 dark:bg-emerald-900/40 dark:text-emerald-400">
                            <x-icon name="wallet" size="4" />
                        </span>
                        <div>
                            <h3 class="text-base font-bold">ثبت پرداخت</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $paymentOrder?->title }} — {{ $paymentOrder?->order_number }}</p>
                        </div>
                    </div>
                    <button wire:click="$set('paymentModalOpen', false)" class="p-2 transition rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-700 dark:hover:text-slate-300">
                        <x-icon name="close" />
                    </button>
                </div>

                {{-- اطلاعات سفارش --}}
                <div class="p-3 mb-3 text-xs rounded-xl bg-slate-50 dark:bg-slate-700/50">
                    <div class="flex justify-between mb-1">
                        <span class="text-slate-500">مبلغ کل:</span>
                        <span class="font-bold">{{ number_format($paymentOrder->amount) }} تومان</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">پرداخت‌شده:</span>
                        <span class="font-bold">{{ number_format($paymentOrder->paid_amount) }} تومان</span>
                    </div>
                </div>

                <form wire:submit.prevent="savePayment" class="space-y-3">
                    <div>
                        <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">مبلغ پرداخت (تومان) <span class="text-red-500">*</span></label>
                        <input type="number" wire:model.blur="payment_amount" required min="1000" dir="ltr" class="w-full px-3 py-2 text-sm transition border rounded-xl border-slate-200 bg-slate-50 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white" />
                        @error('payment_amount') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">روش پرداخت <span class="text-red-500">*</span></label>
                            <select wire:model.live="payment_method" class="w-full px-3 py-2 text-sm transition border rounded-xl border-slate-200 bg-slate-50 focus:border-primary-400 focus:outline-none dark:border-slate-600 dark:bg-slate-700/50 dark:text-white">
                                <option value="زرین‌پال">زرین‌پال</option>
                                <option value="کارت به کارت">کارت به کارت</option>
                                <option value="آی‌دی‌پی">آی‌دی‌پی</option>
                                <option value="نقدی">نقدی</option>
                            </select>
                        </div>
                        <div>
                            <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">شماره تراکنش</label>
                            <input type="text" wire:model.blur="payment_transaction_id" dir="ltr" class="w-full px-3 py-2 text-sm transition border rounded-xl border-slate-200 bg-slate-50 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white" placeholder="TX-123..." />
                            @error('payment_transaction_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div>
                        <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">توضیحات</label>
                        <textarea wire:model.blur="payment_description" rows="2" class="w-full px-3 py-2 text-sm transition border resize-none rounded-xl border-slate-200 bg-slate-50 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white" placeholder="توضیح پرداخت (اختیاری)"></textarea>
                    </div>

                    <div class="flex gap-3 pt-3">
                        <button type="submit" wire:loading.attr="disabled"
                                class="flex-1 rounded-xl bg-emerald-500 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-emerald-500/25 transition-all hover:bg-emerald-600 disabled:opacity-50">
                            ثبت پرداخت
                        </button>
                        <button type="button" wire:click="$set('paymentModalOpen', false)"
                                class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-700">
                            لغو
                        </button>
                    </div>
                </form>
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
                            <h3 class="text-base font-bold">حذف سفارش</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">این عملیات قابل بازگشت نیست</p>
                        </div>
                    </div>
                    <button wire:click="$set('confirmingDelete', false)" class="p-2 transition rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-700 dark:hover:text-slate-300">
                        <x-icon name="close" />
                    </button>
                </div>
                <p class="text-sm text-slate-600 dark:text-slate-300">آیا از حذف این سفارش مطمئن هستید؟ تمام پرداخت‌ها و تسک‌های مرتبط با آن نیز حذف خواهند شد.</p>
                <div class="flex gap-3 mt-5">
                    <button wire:click="deleteOrder" wire:loading.attr="disabled"
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
