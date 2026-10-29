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
    <div class="glass-card overflow-x-auto p-0">
        <table class="w-full text-right text-sm">
            <thead>
                <tr class="border-b border-slate-200 text-xs text-slate-500 dark:border-slate-700 dark:text-slate-400">
                    <th class="p-4 font-medium">نوع</th>
                    <th class="p-4 font-medium">عنوان</th>
                    <th class="p-4 font-medium">توضیحات</th>
                    <th class="p-4 font-medium">تاریخ</th>
                    <th class="p-4 font-medium">وضعیت</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($notifications as $notification)
                    @php
                        $style = $service->styleFor($notification->type);
                        $instance = $notification->type ? new $notification->type($notification->data) : null;
                    @endphp
                    <tr class="border-b border-slate-100 last:border-0 dark:border-slate-800 {{ is_null($notification->read_at) ? 'bg-primary-50/40 dark:bg-primary-900/5' : '' }}"
                        wire:key="notif-{{ $notification->id }}">
                        <td class="p-4">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-{{ $style['color'] }}-100 text-{{ $style['color'] }}-600 dark:bg-{{ $style['color'] }}-900/40">
                                <x-icon name="{{ $style['icon'] }}" size="4" />
                            </span>
                        </td>
                        <td class="p-4 font-medium text-slate-700 dark:text-slate-200">{{ $instance?->title() ?? $style['label'] }}</td>
                        <td class="max-w-sm truncate p-4 text-slate-500 dark:text-slate-400">{{ $instance?->description() ?? '' }}</td>
                        <td class="p-4 text-xs text-slate-500">{{ $notification->created_at?->diffForHumans() }}</td>
                        <td class="p-4">
                            @if (is_null($notification->read_at))
                                <button type="button" wire:click="markAsRead('{{ $notification->id }}')"
                                        class="rounded-md bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-700 transition hover:bg-amber-200 dark:bg-amber-950/60 dark:text-amber-400">
                                    جدید
                                </button>
                            @else
                                <span class="rounded-md bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400">
                                    خوانده‌شده
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-10 text-center text-sm text-slate-500 dark:text-slate-400">
                            @if ($filter === 'unread')
                                همه اعلان‌ها خوانده شده‌اند. ✅
                            @else
                                هنوز اعلانی ثبت نشده است.
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $notifications->links() }}</div>
</div>
