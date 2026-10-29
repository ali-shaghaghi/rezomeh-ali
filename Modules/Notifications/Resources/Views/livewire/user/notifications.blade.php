@php
    $service = app(\Modules\Notifications\Services\NotificationService::class);
@endphp

<div>
    {{-- فیلتر + خواندن همه --}}
    <div class="glass-card mb-5 flex flex-wrap items-center justify-between gap-3 p-4">
        <div class="flex gap-2">
            <button type="button" wire:click="$set('filter', 'all')"
                    class="rounded-lg px-3 py-1.5 text-xs font-medium transition {{ $filter === 'all' ? 'bg-primary-500 text-white' : 'text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700' }}">
                همه ({{ fa_digits(auth()->user()->notifications()->count()) }})
            </button>
            <button type="button" wire:click="$set('filter', 'unread')"
                    class="rounded-lg px-3 py-1.5 text-xs font-medium transition {{ $filter === 'unread' ? 'bg-primary-500 text-white' : 'text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700' }}">
                خوانده‌نشده ({{ fa_digits($unreadCount) }})
            </button>
        </div>
        @if ($unreadCount > 0)
            <button type="button" wire:click="markAllRead" class="btn-outline !px-4 !py-2 !text-xs">
                <x-icon name="bell" size="4" class="ml-1" />
                علامت‌گذاری همه به‌عنوان خوانده
            </button>
        @endif
    </div>

    {{-- لیست اعلان‌ها --}}
    <div class="space-y-3">
        @forelse ($notifications as $notification)
            @php
                $style = $service->styleFor($notification->type);
                $instance = $notification->type ? new $notification->type($notification->data) : null;
            @endphp
            <div class="glass-card flex items-center gap-4 p-4 {{ is_null($notification->read_at) ? 'border-r-2 border-r-primary-400' : '' }}"
                 wire:key="user-notif-{{ $notification->id }}">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-{{ $style['color'] }}-100 text-{{ $style['color'] }}-600 dark:bg-{{ $style['color'] }}-900/40">
                    <x-icon name="{{ $style['icon'] }}" size="5" />
                </span>
                <div class="min-w-0 flex-1">
                    <p class="font-semibold text-slate-700 dark:text-slate-200">{{ $instance?->title() ?? $style['label'] }}</p>
                    <p class="truncate text-sm text-slate-500 dark:text-slate-400">{{ $instance?->description() ?? '' }}</p>
                    <p class="mt-0.5 text-[11px] text-slate-400 dark:text-slate-500">{{ $notification->created_at?->diffForHumans() }}</p>
                </div>
                @if (is_null($notification->read_at))
                    <button type="button" wire:click="markAsRead('{{ $notification->id }}')"
                            class="shrink-0 rounded-lg bg-primary-100 px-3 py-1.5 text-xs font-semibold text-primary-600 transition hover:bg-primary-200 dark:bg-primary-900/50 dark:text-primary-400">
                        خواندن
                    </button>
                @else
                    <span class="shrink-0 text-[11px] text-slate-400">خوانده‌شده</span>
                @endif
            </div>
        @empty
            <div class="glass-card p-12 text-center">
                <x-icon name="bell" size="10" class="mx-auto mb-3 text-slate-300 dark:text-slate-600" />
                <p class="text-sm font-medium text-slate-600 dark:text-slate-300">اعلانی وجود ندارد</p>
                <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">
                    @if ($filter === 'unread')
                        همه اعلان‌ها خوانده شده‌اند
                    @else
                        با فعالیت در سایت (سفارش، تیکت، پرداخت) اعلان‌ها اینجا نمایش داده می‌شوند
                    @endif
                </p>
            </div>
        @endforelse
    </div>

    <div class="mt-6">{{ $notifications->links() }}</div>
</div>
