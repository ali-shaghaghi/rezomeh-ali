<div>
    {{-- ===== سربرگ + دکمه سفارش جدید — الگوی my-consultations ===== --}}
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <h2 class="text-lg font-bold text-slate-900 dark:text-white">{{ __('panel.my_orders') }}</h2>
        <a href="{{ route('public.products.index') }}" class="btn-primary !px-5 !py-2.5 !text-xs">
            <x-icon name="plus" size="4" />
            {{ __('panel.new_order') }}
        </a>
    </div>

    {{-- ===== جستجو در شماره/عنوان سفارش (زنده — با debounce) ===== --}}
    <div class="relative mb-5">
        <span class="absolute -translate-y-1/2 pointer-events-none right-4 top-1/2 text-slate-400">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        </span>
        <input type="search" wire:model.live.debounce.400ms="search" placeholder="{{ __('panel.search_orders') }}"
               class="w-full pr-12 pl-4 py-3.5 rounded-xl bg-white/80 dark:bg-slate-900/60 backdrop-blur-xl border border-slate-200/70 dark:border-slate-700/50 shadow-card text-sm text-slate-800 dark:text-slate-100 placeholder:text-slate-400 focus:border-primary-400 focus:ring-2 focus:ring-primary-400/20 outline-none transition-all">
        <span wire:loading wire:target="search" class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
        </span>
    </div>

    {{-- ===== فیلتر وضعیت (زنده) ===== --}}
    <div class="flex flex-wrap gap-2 mb-5">
        <button type="button" wire:click="$set('status', '')" class="skill-tag !py-1.5 !px-4 !text-xs {{ $status === '' ? 'text-primary-600 border-primary-300 bg-primary-50 dark:bg-primary-950/50 dark:text-primary-400' : '' }}">{{ __('panel.filter_all') }}</button>
        @foreach (['pending' => __('panel.filter_pending'), 'review' => __('panel.filter_review'), 'accepted' => __('panel.filter_accepted'), 'development' => __('panel.filter_developing'), 'completed' => __('panel.filter_completed'), 'cancelled' => __('panel.filter_cancelled')] as $key => $label)
            <button type="button" wire:click="$set('status', '{{ $key }}')" class="skill-tag !py-1.5 !px-4 !text-xs {{ $status === $key ? 'text-primary-600 border-primary-300 bg-primary-50 dark:bg-primary-950/50 dark:text-primary-400' : '' }}">{{ $label }}</button>
        @endforeach
    </div>

    {{-- ===== لیست سفارش‌ها ===== --}}
    <div class="space-y-4">
        @php
            $statusColors = [
                'pending' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
                'review' => 'bg-sky-100 text-sky-700 dark:bg-sky-950/60 dark:text-sky-400',
                'accepted' => 'bg-primary-100 text-primary-700 dark:bg-primary-900/40 dark:text-primary-300',
                'development' => 'bg-violet-100 text-violet-700 dark:bg-violet-950/60 dark:text-violet-400',
                'completed' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
                'cancelled' => 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300',
            ];
        @endphp

        @forelse ($orders as $order)
            <a href="{{ route('user.orders.show', $order->order_number) }}" wire:navigate class="block p-5 glass-card glass-card-hover" wire:key="order-{{ $order->id }}">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="flex-1 min-w-0">
                        <div class="mb-1.5 flex flex-wrap items-center gap-2">
                            <span class="rounded-md bg-slate-100 px-2 py-0.5 text-xs font-mono font-semibold text-slate-500 dark:bg-slate-800 dark:text-slate-400" dir="ltr">{{ $order->order_number }}</span>
                            <span class="rounded-md px-2 py-0.5 text-xs font-semibold {{ $statusColors[$order->status] ?? 'bg-primary-100 text-primary-700 dark:bg-primary-900/40 dark:text-primary-300' }}">{{ $order->status_label }}</span>
                            @if ($order->is_paid)
                                <span class="rounded-md bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">{{ __('panel.paid') }}</span>
                            @endif
                        </div>

                        <h3 class="font-bold text-slate-900 dark:text-white">{{ $order->title }}</h3>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ \Illuminate\Support\Str::limit($order->description, 120) }}</p>

                        <p class="mt-2 text-xs text-slate-400">
                            {{ __('panel.amount') }} <span class="font-bold text-slate-600 dark:text-slate-300">{{ format_price_for_locale((float) $order->amount) }}</span>
                            @if ($order->deadline)
                                · {{ __('panel.deadline') }} <span dir="ltr">{{ fa_digits($order->deadline->format('Y/m/d')) }}</span>
                            @endif
                        </p>

                        {{-- پیشرفت مالی --}}
                        @if ((float) $order->amount > 0)
                            @php($progress = (int) min(100, max(0, round(((float) $order->paid_amount / (float) $order->amount) * 100))))
                            <div class="mt-3">
                                <div class="flex justify-between text-xs text-slate-400 mb-1.5">
                                    <span>{{ __('panel.paid_amount', ['amount' => format_price_for_locale((float) $order->paid_amount)]) }}</span>
                                    <span>{{ fa_digits($progress) }}٪</span>
                                </div>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="--progress:{{ $progress }}%; width: {{ $progress }}%"></div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <span class="btn-outline !px-3.5 !py-2 !text-xs">{{ __('panel.view_order') }}</span>
                </div>
            </a>
        @empty
            <div class="p-10 text-center glass-card">
                <p class="mb-1 font-bold text-slate-600 dark:text-slate-300">{{ __('panel.no_orders') }}</p>
                <p class="mb-4 text-sm text-slate-500 dark:text-slate-400">
                    @if (trim($search) !== '' || $status !== '')
                        {{ __('panel.change_filters') }}
                    @else
                        {{ __('panel.no_orders_desc') }}
                    @endif
                </p>
                <a href="{{ route('public.products.index') }}" class="btn-primary !px-6 !py-3 !text-xs">{{ __('panel.create_order') }}</a>
            </div>
        @endforelse
    </div>

    <div class="mt-8">{{ $orders->links() }}</div>
</div>
