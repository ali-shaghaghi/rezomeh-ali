<div wire:poll.30s>
    {{-- سربرگ + دکمه ثبت درخواست پروژه --}}
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <h2 class="text-lg font-bold text-slate-900 dark:text-white">درخواست‌های پروژه</h2>
        <a href="{{ route('user.projectdev.create') }}" class="btn-primary !px-5 !py-2.5 !text-xs">
            <x-icon name="plus" size="4" />
            ثبت درخواست پروژه
        </a>
    </div>

    {{-- کارت‌های آمار پروژه --}}
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 mb-5">
        <div class="glass-card p-4 text-center"><p class="text-[11px] text-slate-500 dark:text-slate-400">کل درخواست‌ها</p><p class="mt-1 text-xl font-black">{{ fa_digits($projectStats['projects_total']) }}</p></div>
        <div class="glass-card p-4 text-center"><p class="text-[11px] text-slate-500 dark:text-slate-400">در انتظار بررسی</p><p class="mt-1 text-xl font-black text-amber-500">{{ fa_digits($projectStats['projects_pending']) }}</p></div>
        <div class="glass-card p-4 text-center"><p class="text-[11px] text-slate-500 dark:text-slate-400">تایید شده</p><p class="mt-1 text-xl font-black text-sky-500">{{ fa_digits($projectStats['projects_approved']) }}</p></div>
        <div class="glass-card p-4 text-center"><p class="text-[11px] text-slate-500 dark:text-slate-400">رد شده</p><p class="mt-1 text-xl font-black text-rose-500">{{ fa_digits($projectStats['projects_rejected']) }}</p></div>
    </div>

    {{-- لیست درخواست‌های پروژه --}}
    @if ($projectRequests->count())
        <div class="space-y-4">
            @foreach ($projectRequests as $request)
                <div class="glass-card-hover p-5 sm:p-6 rounded-2xl" wire:key="prj-{{ $request->id }}">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2 mb-1.5">
                                <span class="rounded-full bg-{{ $request->status_color }}-100 px-2.5 py-1 text-xs font-semibold text-{{ $request->status_color }}-700 dark:bg-{{ $request->status_color }}-900/40 dark:text-{{ $request->status_color }}-300">{{ $request->status_label }}</span>
                                <span class="px-2.5 py-1 text-xs font-medium rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400">{{ $request->project_type_name }}</span>
                                @if ($request->difficulty)
                                    <span class="px-2.5 py-1 text-xs font-medium rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400">سختی: {{ $request->difficulty_label }}</span>
                                @endif
                            </div>
                            <h3 class="font-bold text-slate-900 dark:text-white">{{ $request->title }}</h3>
                            @if ($request->description)
                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ \Illuminate\Support\Str::limit($request->description, 120) }}</p>
                            @endif
                        </div>
                        <div class="shrink-0 text-left">
                            @if ($request->status === 'pending' && $request->formatted_estimated_price)
                                <span class="text-xs text-slate-400">در انتظار قیمت‌گذاری — تخمین: {{ $request->formatted_estimated_price }} تومان</span>
                            @endif
                        </div>
                    </div>

                    @if ($request->order_id && $request->order)
                        <a href="{{ route('user.orders.show', $request->order->order_number) }}" wire:navigate class="btn-outline !px-4 !py-2 !text-xs mt-3 inline-flex items-center gap-1.5">
                            <x-icon name="orders" size="3.5" />
                            فاکتور و پرداخت اقساط
                        </a>
                    @endif

                    {{-- پیش‌نمایش برنامه پرداخت 30/30/40 --}}
                    @if (in_array($request->status, ['approved', 'converted']) && $request->effective_price > 0)
                        @php($price = (float) $request->effective_price)
                        <div class="mt-3 grid grid-cols-3 gap-2 text-center text-[11px]">
                            <div class="p-2 rounded-lg bg-slate-50 dark:bg-slate-700/40"><b class="block text-sm text-slate-700 dark:text-slate-200">۳۰٪</b><span class="text-slate-400">{{ number_format($price * 0.3) }} تومان</span></div>
                            <div class="p-2 rounded-lg bg-slate-50 dark:bg-slate-700/40"><b class="block text-sm text-slate-700 dark:text-slate-200">۳۰٪</b><span class="text-slate-400">{{ number_format($price * 0.3) }} تومان</span></div>
                            <div class="p-2 rounded-lg bg-slate-50 dark:bg-slate-700/40"><b class="block text-sm text-slate-700 dark:text-slate-200">۴۰٪</b><span class="text-slate-400">{{ number_format($price * 0.4) }} تومان</span></div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="mt-8">{{ $projectRequests->links() }}</div>
    @else
        <div class="glass-card p-10 text-center">
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">هنوز درخواست پروژه‌ای ثبت نکرده‌اید.</p>
            <a href="{{ route('user.projectdev.create') }}" class="btn-primary !px-6 !py-3 !text-xs">ثبت اولین درخواست پروژه</a>
        </div>
    @endif
</div>
