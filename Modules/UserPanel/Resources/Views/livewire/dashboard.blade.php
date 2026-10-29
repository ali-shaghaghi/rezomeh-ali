<div wire:poll.20s>
    {{-- کارت‌های آمار (به‌روزرسانی زنده) --}}
    <div class="grid grid-cols-2 gap-4">
        <div class="stat-card">
            <div class="flex items-center gap-3">
                <span class="icon-box !w-11 !h-11 shrink-0"><x-icon name="cart" /></span>
                <div>
                    <div class="stat-value !text-xl">{{ fa_digits($stats['orders_total']) }}</div>
                    <div class="stat-label">{{ __('panel.stats.orders_total') }}</div>
                </div>
            </div>
        </div>
        <div class="stat-card">
            <div class="flex items-center gap-3">
                <span class="icon-box !w-11 !h-11 shrink-0"><x-icon name="bell" /></span>
                <div>
                    <div class="stat-value !text-xl">{{ fa_digits($stats['orders_active']) }}</div>
                    <div class="stat-label">{{ __('panel.stats.orders_active') }}</div>
                </div>
            </div>
        </div>
        <div class="stat-card">
            <div class="flex items-center gap-3">
                <span class="icon-box !w-11 !h-11 shrink-0"><x-icon name="check" /></span>
                <div>
                    <div class="stat-value !text-xl">{{ fa_digits($stats['orders_completed']) }}</div>
                    <div class="stat-label">{{ __('panel.stats.orders_completed') }}</div>
                </div>
            </div>
        </div>
        <div class="stat-card">
            <div class="flex items-center gap-3">
                <span class="icon-box !w-11 !h-11 shrink-0"><x-icon name="wallet" /></span>
                <div>
                    <div class="stat-value !text-xl">{{ fa_digits(number_format((float) $stats['total_spent'])) }}</div>
                    <div class="stat-label">{{ __('panel.stats.total_spent') }}</div>
                </div>
            </div>
        </div>
        <div class="stat-card">
            <div class="flex items-center gap-3">
                <span class="icon-box !w-11 !h-11 shrink-0"><x-icon name="mail" /></span>
                <div>
                    <div class="stat-value !text-xl">{{ fa_digits($stats['tickets_total']) }}</div>
                    <div class="stat-label">{{ __('panel.stats.tickets_total') }}</div>
                </div>
            </div>
        </div>
        <div class="stat-card">
            <div class="flex items-center gap-3">
                <span class="icon-box !w-11 !h-11 shrink-0"><x-icon name="eye" /></span>
                <div>
                    <div class="stat-value !text-xl">{{ fa_digits($stats['tickets_open']) }}</div>
                    <div class="stat-label">{{ __('panel.stats.tickets_open') }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- دسترسی سریع: درخواست مشاوره — مانند جریان مستقل projects/request --}}
    <div class="glass-card mt-6 flex flex-wrap items-center justify-between gap-4 p-5">
        <div class="flex items-center gap-3">
            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-violet-100 text-violet-600 dark:bg-violet-900/40 dark:text-violet-400">
                <x-icon name="users" />
            </span>
            <div>
                <p class="text-sm font-bold text-slate-900 dark:text-white">{{ __('panel.need_consultation') }}</p>
                <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ __('panel.need_consultation_desc') }}</p>
            </div>
        </div>
        <a href="{{ route('public.consultation.index') }}" class="btn-primary !px-5 !py-2.5 !text-xs">
            <x-icon name="plus" size="4" />
            {{ __('panel.request_consultation') }}
        </a>
    </div>

    {{-- آخرین‌ها --}}
    <div class="grid grid-cols-1 gap-6 mt-6">
        {{-- سفارش‌ها --}}
        <div class="glass-card p-6">
            <div class="flex items-center justify-between mb-5">
                <h2 class="flex items-center gap-2 font-bold text-slate-900 dark:text-white">
                    <x-icon name="orders" class="text-primary-500" />
                    {{ __('panel.latest_orders') }}
                </h2>
                <a href="{{ url('/dashboard?tab=orders') }}" wire:navigate class="text-xs font-bold text-primary-600 dark:text-primary-400 hover:underline">{{ __('panel.view_all') }}</a>
            </div>

            @if (count($latestOrders))
                <div class="space-y-3">
                    @foreach ($latestOrders as $order)
                        <a href="{{ route('user.orders.show', $order->order_number) }}"
                           class="flex items-center justify-between gap-3 w-full p-3 text-right rounded-xl border border-slate-200/70 dark:border-slate-700/50 hover:border-primary-300 dark:hover:border-primary-600 transition group">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-bold text-slate-800 dark:text-slate-200 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition">{{ $order->title }}</p>
                                <p class="mt-0.5 text-xs text-slate-400" dir="ltr">{{ $order->order_number }}</p>
                            </div>
                            <span class="shrink-0 px-2.5 py-1 text-xs font-semibold rounded-lg bg-primary-50 dark:bg-primary-950/50 text-primary-600 dark:text-primary-400">{{ $order->status_label }}</span>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="py-8 text-center">
                    <p class="mb-4 text-sm text-slate-500 dark:text-slate-400">{{ __('panel.no_orders_yet') }}</p>
                    <a href="{{ route('public.home') }}#contact" class="btn-outline !px-5 !py-2.5 !text-xs">{{ __('panel.request_project') }}</a>
                </div>
            @endif
        </div>

        {{-- تیکت‌ها --}}
        <div class="glass-card p-6">
            <div class="flex items-center justify-between mb-5">
                <h2 class="flex items-center gap-2 font-bold text-slate-900 dark:text-white">
                    <x-icon name="mail" class="text-primary-500" />
                    {{ __('panel.latest_tickets') }}
                </h2>
                <a href="{{ url('/dashboard?tab=tickets') }}" wire:navigate class="text-xs font-bold text-primary-600 dark:text-primary-400 hover:underline">{{ __('panel.view_all') }}</a>
            </div>

            @if (count($latestTickets))
                <div class="space-y-3">
                    @foreach ($latestTickets as $ticket)
                        <a href="{{ route('user.tickets.show', $ticket->id) }}"
                           class="flex items-center justify-between gap-3 w-full p-3 text-right rounded-xl border border-slate-200/70 dark:border-slate-700/50 hover:border-primary-300 dark:hover:border-primary-600 transition group">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-bold text-slate-800 dark:text-slate-200 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition">{{ $ticket->subject }}</p>
                                <p class="mt-0.5 text-xs text-slate-400">{{ $ticket->created_at->diffForHumans() }}</p>
                            </div>
                            <span class="shrink-0 px-2.5 py-1 text-xs font-semibold rounded-lg bg-primary-50 dark:bg-primary-950/50 text-primary-600 dark:text-primary-400">{{ $ticket->status_label }}</span>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="py-8 text-center">
                    <p class="mb-4 text-sm text-slate-500 dark:text-slate-400">{{ __('panel.no_tickets_yet') }}</p>                        <a href="{{ url('/dashboard?tab=tickets') }}" wire:navigate class="btn-outline !px-5 !py-2.5 !text-xs">{{ __('panel.create_new_ticket') }}</a>
                </div>
            @endif
        </div>
    </div>
</div>