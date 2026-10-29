<div class="space-y-6">
    {{-- نوتیفیکیشن --}}
    @if ($successToast)
        <div class="flex items-center gap-2 p-4 text-sm font-bold rounded-2xl glass-card !border-emerald-300/60 dark:!border-emerald-700/40 !text-emerald-600 dark:!text-emerald-400 animate-slide-up"
             x-data="{ show: true }" x-init="setTimeout(() => show = false, 3500)" x-show="show" x-transition.opacity.duration.500ms>
            <x-icon name="check" size="4" />
            {{ $successMessage }}
        </div>
    @endif

    {{-- کارت‌های آمار --}}
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="chart-card"><p class="text-xs text-slate-500 dark:text-slate-400">کل درخواست‌ها</p><p class="mt-1 text-2xl font-black">{{ fa_digits($stats['total']) }}</p></div>
        <div class="chart-card"><p class="text-xs text-slate-500 dark:text-slate-400">در انتظار بررسی</p><p class="mt-1 text-2xl font-black text-amber-500">{{ fa_digits($stats['pending']) }}</p></div>
        <div class="chart-card"><p class="text-xs text-slate-500 dark:text-slate-400">تایید شده</p><p class="mt-1 text-2xl font-black text-sky-500">{{ fa_digits($stats['approved']) }}</p></div>
        <div class="chart-card"><p class="text-xs text-slate-500 dark:text-slate-400">تبدیل به سفارش</p><p class="mt-1 text-2xl font-black text-emerald-500">{{ fa_digits($stats['converted']) }}</p></div>
    </div>

    {{-- جستجو + فیلتر --}}
    <div class="flex flex-wrap items-center gap-3">
        <div class="relative flex-1 min-w-[220px] max-w-md">
            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400"><x-icon name="search" size="4" /></span>
            <input type="search" wire:model.live.debounce.400ms="search" placeholder="جستجوی موضوع / نوع / نام کاربر..."
                   class="w-full py-2.5 pr-10 pl-4 text-sm rounded-xl border border-slate-200 bg-white focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-surface-800" />
        </div>
        <div class="flex flex-wrap gap-2">
            <button type="button" wire:click="$set('status', '')" class="skill-tag !py-1.5 !px-4 !text-xs {{ $status === '' ? 'text-primary-600 border-primary-300 bg-primary-50 dark:bg-primary-950/50' : '' }}">همه</button>
            @foreach (['pending' => 'در انتظار', 'approved' => 'تایید شده', 'rejected' => 'رد شده', 'converted' => 'تبدیل شده'] as $key => $label)
                <button type="button" wire:click="$set('status', '{{ $key }}')" class="skill-tag !py-1.5 !px-4 !text-xs {{ $status === $key ? 'text-primary-600 border-primary-300 bg-primary-50 dark:bg-primary-950/50' : '' }}">{{ $label }}</button>
            @endforeach
        </div>
        @if ($search || $status)
            <button type="button" wire:click="resetFilters" class="text-xs text-slate-400 hover:text-rose-500">پاک کردن فیلترها</button>
        @endif
    </div>

    {{-- لیست درخواست‌ها --}}
    @if ($requests->count())
        <div class="space-y-4">
            @foreach ($requests as $request)
                <div class="chart-card" wire:key="req-{{ $request->id }}">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2 mb-1.5">
                                <span class="rounded-full bg-{{ $request->status_color }}-100 px-2.5 py-1 text-xs font-medium text-{{ $request->status_color }}-700 dark:bg-{{ $request->status_color }}-900/40 dark:text-{{ $request->status_color }}-300">{{ $request->status_label }}</span>
                                <span class="px-2.5 py-1 text-xs rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-500">{{ $request->project_type_name }}</span>
                                @if ($request->difficulty)
                                    <span class="px-2.5 py-1 text-xs rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-500">سختی: {{ $request->difficulty_label }}</span>
                                @endif
                            </div>
                            <h3 class="font-bold">{{ $request->title }}</h3>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                کاربر: <b>{{ $request->user?->name ?? '—' }}</b>
                                @if ($request->deadline) · ددلاین: {{ fa_digits($request->deadline->format('Y/m/d')) }} @endif
                            </p>
                        </div>
                        <div class="text-left shrink-0">
                            <p class="text-[11px] text-slate-400">تخمین سیستم: {{ $request->formatted_estimated_price }} تومان</p>
                            <p class="text-sm font-black {{ $request->final_price !== null ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400' }}">
                                نهایی: {{ $request->formatted_final_price }} {{ $request->final_price !== null ? 'تومان' : '' }}
                            </p>
                        </div>
                    </div>

                    {{-- ویژگی‌های انتخابی کاربر --}}
                    @if ($request->features_snapshot)
                        @php($snapshot = $request->features_snapshot)
                        <div class="mt-3 pt-3 border-t border-slate-100 dark:border-slate-700/50">
                            @if (! empty($snapshot['options']))
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($snapshot['options'] as $opt)
                                        <span class="px-2.5 py-1 text-xs rounded-lg bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-300">{{ $opt['name'] }} <b>+{{ number_format((float) $opt['price']) }}</b></span>
                                    @endforeach
                                </div>
                            @endif
                            @if (! empty($snapshot['custom']))
                                <div class="flex flex-wrap gap-2 mt-2">
                                    @foreach ($snapshot['custom'] as $custom)
                                        <span class="px-2.5 py-1 text-xs rounded-lg bg-amber-50 dark:bg-amber-950/30 text-amber-700 dark:text-amber-300">دلخواه: {{ $custom['name'] }}@if($custom['note']) — {{ $custom['note'] }}@endif</span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- عملیات --}}
                    <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-700/50 flex flex-wrap gap-2">
                        @if (in_array($request->status, ['pending', 'approved']))
                            <button type="button" wire:click="openReview({{ $request->id }}, false)" class="btn-outline !px-4 !py-2 !text-xs">
                                <x-icon name="edit" size="3.5" />
                                بررسی و قیمت‌گذاری
                            </button>
                            <button type="button" wire:click="openReview({{ $request->id }}, true)" class="btn-primary !px-4 !py-2 !text-xs">
                                <x-icon name="orders" size="3.5" />
                                تایید و تبدیل به سفارش
                            </button>
                            @if ($request->status === 'pending')
                                <button type="button" wire:click="reject({{ $request->id }})" class="btn-outline !px-4 !py-2 !text-xs !text-rose-500">رد</button>
                            @endif
                        @endif
                        <button type="button" wire:click="confirmDelete({{ $request->id }})" class="btn-outline !px-4 !py-2 !text-xs !text-rose-500">
                            <x-icon name="trash" size="3.5" />
                            حذف
                        </button>
                        @if ($request->order_id)
                            <a href="{{ route('admin.orders.show', $request->order_id) }}" class="btn-outline !px-4 !py-2 !text-xs">مشاهده سفارش</a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-8">{{ $requests->links() }}</div>
    @else
        <div class="chart-card p-10 text-center">
            <p class="text-sm text-slate-500 dark:text-slate-400">
                @if ($search || $status)
                    درخواستی با این فیلترها پیدا نشد.
                @else
                    هنوز درخواست پروژه‌ای ثبت نشده است.
                @endif
            </p>
        </div>
    @endif

    {{-- ===================== مودال بررسی / تبدیل ===================== --}}
    @if ($reviewModalOpen)
        @php($reviewRequest = \Modules\ProjectDev\Models\ProjectRequest::find($reviewId))
        @if ($reviewRequest)
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm overflow-y-auto">
                <div class="w-full max-w-lg glass-card p-6 rounded-3xl animate-slide-up my-8">
                    <h3 class="mb-1 font-bold text-lg">{{ $reviewMode ? 'تایید و تبدیل به سفارش' : 'بررسی و قیمت‌گذاری' }}</h3>
                    <p class="mb-5 text-xs text-slate-500 dark:text-slate-400">{{ $reviewRequest->title }} — تخمین سیستم: {{ $reviewRequest->formatted_estimated_price }} تومان</p>

                    <form wire:submit="saveReview" class="space-y-4">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="block mb-1.5 text-sm font-medium">قیمت نهایی (تومان) <span class="text-rose-500">*</span></label>
                                <input type="number" wire:model="reviewForm.final_price" min="0" step="1000"
                                       class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 bg-white focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white" />
                                @error('reviewForm.final_price') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block mb-1.5 text-sm font-medium">سختی پروژه</label>
                                <select wire:model="reviewForm.difficulty"
                                        class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 bg-white focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white">
                                    <option value="low">کم</option>
                                    <option value="medium">متوسط</option>
                                    <option value="high">زیاد</option>
                                </select>
                            </div>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="block mb-1.5 text-sm font-medium">زمان انجام (روز)</label>
                                <input type="number" wire:model="reviewForm.estimated_days" min="1" max="365"
                                       class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 bg-white focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white" />
                                @error('reviewForm.estimated_days') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block mb-1.5 text-sm font-medium">ددلاین تحویل</label>
                                <input type="date" wire:model="reviewForm.deadline" dir="ltr"
                                       class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 bg-white focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white" />
                                @error('reviewForm.deadline') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <div>
                            <label class="block mb-1.5 text-sm font-medium">یادداشت برای کاربر</label>
                            <textarea wire:model="reviewForm.admin_note" rows="3" class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 bg-white focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white resize-y"></textarea>
                        </div>

                        {{-- پیش‌نمایش قسط‌ها 30/30/40 --}}
                        <div class="p-4 rounded-xl bg-gradient-to-l from-primary-50 to-indigo-50/50 dark:from-primary-950/20 dark:to-indigo-950/10">
                            <p class="mb-2 text-xs font-bold">برنامه پرداخت (قانون پروژه):</p>
                            @php($price = (float) $reviewForm['final_price'])
                            <div class="grid grid-cols-3 gap-2 text-center text-xs">
                                <div class="p-2 rounded-lg bg-white/70 dark:bg-slate-800/60"><b class="block text-sm">۳۰٪</b>{{ number_format($price * 0.3) }}</div>
                                <div class="p-2 rounded-lg bg-white/70 dark:bg-slate-800/60"><b class="block text-sm">۳۰٪</b>{{ number_format($price * 0.3) }}</div>
                                <div class="p-2 rounded-lg bg-white/70 dark:bg-slate-800/60"><b class="block text-sm">۴۰٪</b>{{ number_format($price * 0.4) }}</div>
                            </div>
                            <p class="mt-2 text-[10px] text-slate-400">پیش‌پرداخت آنی، نیمه‌کار و تحویل نهایی — با توجه به ددلاین پروژه</p>
                        </div>

                        <div class="flex gap-3 pt-2">
                            <button type="submit" wire:loading.attr="disabled" wire:target="saveReview" class="btn-primary !px-6 !py-2.5 !text-xs">
                                {{ $reviewMode ? 'تایید نهایی و ساخت فاکتور' : 'ثبت بررسی' }}
                            </button>
                            <button type="button" wire:click="$set('reviewModalOpen', false)" class="btn-outline !px-6 !py-2.5 !text-xs">انصراف</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    @endif

    {{-- مودال حذف --}}
    @if ($confirmingDelete)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
            <div class="w-full max-w-sm glass-card p-6 rounded-3xl text-center animate-slide-up">
                <h3 class="font-bold text-lg">حذف درخواست پروژه</h3>
                <p class="mt-2 mb-5 text-xs text-slate-500 dark:text-slate-400">مطمئن هستید؟</p>
                <div class="flex gap-3 justify-center">
                    <button type="button" wire:click="deleteRequest" class="btn-primary !bg-rose-500 !px-6 !py-2.5 !text-xs">حذف کن</button>
                    <button type="button" wire:click="$set('confirmingDelete', false)" class="btn-outline !px-6 !py-2.5 !text-xs">انصراف</button>
                </div>
            </div>
        </div>
    @endif
</div>
