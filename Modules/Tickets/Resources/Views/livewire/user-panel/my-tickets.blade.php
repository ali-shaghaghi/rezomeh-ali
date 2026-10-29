<div>
    {{-- ===== سربرگ + دکمه تیکت جدید — الگوی my-consultations ===== --}}
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <h2 class="text-lg font-bold text-slate-900 dark:text-white">{{ __('panel.my_tickets') }}</h2>
        <a href="{{ route('user.tickets.create') }}" wire:navigate class="btn-primary !px-5 !py-2.5 !text-xs">
            <x-icon name="plus" size="4" />
            {{ __('panel.new_ticket') }}
        </a>
    </div>

    {{-- ===== فیلتر وضعیت (زنده — بدون رفرش) ===== --}}
    <div class="mb-5 flex flex-wrap gap-2">
        <button type="button" wire:click="$set('status', '')" class="skill-tag !py-1.5 !px-4 !text-xs {{ $status === '' ? 'text-primary-600 border-primary-300 bg-primary-50 dark:bg-primary-950/50 dark:text-primary-400' : '' }}">{{ __('panel.filter_all') }}</button>
        @foreach (['open' => __('panel.filter_open'), 'in_progress' => __('panel.filter_in_progress'), 'waiting' => __('panel.filter_waiting'), 'closed' => __('panel.filter_closed')] as $key => $label)
            <button type="button" wire:click="$set('status', '{{ $key }}')" class="skill-tag !py-1.5 !px-4 !text-xs {{ $status === $key ? 'text-primary-600 border-primary-300 bg-primary-50 dark:bg-primary-950/50 dark:text-primary-400' : '' }}">{{ $label }}</button>
        @endforeach
    </div>

    {{-- ===== لیست تیکت‌ها — الگوی کارت‌های my-consultations ===== --}}
    <div class="space-y-4">
        @php
            $statusBadges = [
                'open' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
                'in_progress' => 'bg-primary-100 text-primary-700 dark:bg-primary-900/40 dark:text-primary-300',
                'waiting' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
                'closed' => 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400',
            ];
            $priorityBadges = [
                'urgent' => 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300',
                'high' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
            ];
            $categories = [
                'general' => __('tickets.category_general'),
                'order' => __('tickets.category_order'),
                'billing' => __('tickets.category_billing'),
                'technical' => __('tickets.category_technical'),
                'suggestion' => __('tickets.category_suggestion'),
            ];
        @endphp

        @forelse ($tickets as $ticket)
            <a href="{{ route('user.tickets.show', $ticket->id) }}" wire:navigate class="glass-card glass-card-hover block p-5" wire:key="ticket-{{ $ticket->id }}">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <div class="mb-1.5 flex flex-wrap items-center gap-2">
                            <span class="rounded-md px-2 py-0.5 text-xs font-semibold {{ $statusBadges[$ticket->status] ?? $statusBadges['closed'] }}">{{ $ticket->status_label }}</span>
                            <span class="rounded-md px-2 py-0.5 text-xs font-semibold {{ $priorityBadges[$ticket->priority] ?? 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300' }}">{{ __('panel.priority_'.$ticket->priority) }}</span>
                            <span class="rounded-md bg-slate-100 px-2 py-0.5 text-xs text-slate-500 dark:bg-slate-800 dark:text-slate-400">{{ $categories[$ticket->category] ?? $categories['general'] }}</span>
                        </div>
                        <h3 class="font-bold text-slate-900 dark:text-white">{{ $ticket->subject }}</h3>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ \Illuminate\Support\Str::limit($ticket->message, 120) }}</p>
                        <p class="mt-2 text-xs text-slate-400">
                            تاریخ: <span dir="ltr">{{ fa_digits($ticket->created_at->format('Y/m/d')) }}</span>
                            @if ($ticket->replies_count)
                                · {{ __('panel.replies', ['count' => fa_digits($ticket->replies_count)]) }}
                            @endif
                        </p>
                    </div>
                    <span class="btn-outline !px-3.5 !py-2 !text-xs">{{ __('panel.view_conversation') }}</span>
                </div>
            </a>
        @empty
            <div class="glass-card p-10 text-center">
                <p class="mb-4 text-sm text-slate-500 dark:text-slate-400">
                    @if ($status !== '')
                        {{ __('tickets.no_tickets_status') }}
                    @else
                        {{ __('tickets.no_tickets_yet') }}
                    @endif
                </p>
                <a href="{{ route('user.tickets.create') }}" wire:navigate class="btn-primary !px-6 !py-3 !text-xs">{{ __('tickets.first_ticket') }}</a>
            </div>
        @endforelse
    </div>

    <div class="mt-8">{{ $tickets->links() }}</div>
</div>
