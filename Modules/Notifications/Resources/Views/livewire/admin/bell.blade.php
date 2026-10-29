@php
    $service = app(\Modules\Notifications\Services\NotificationService::class);
@endphp

<div class="dropdown relative" wire:poll.30s="$refresh">
    <button type="button" data-dropdown-toggle="header-notif" aria-expanded="false"
            class="relative rounded-xl border border-slate-200 p-2.5 text-slate-600 transition hover:border-primary-300 hover:text-primary-600 dark:border-slate-700"
            aria-label="اعلان‌ها">
        <x-icon name="bell" />
        @if ($unreadCount > 0)
            <span class="absolute left-1.5 top-1.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] font-bold text-white">
                {{ fa_digits($unreadCount > 99 ? '۹۹+' : $unreadCount) }}
            </span>
        @endif
    </button>

    {{-- موبایل: پنل ثابت زیر هدر و تمام‌عرض تا هیچ بخشی بیرون صفحه نماند --}}
    <div id="header-notif" class="dropdown-panel max-sm:fixed max-sm:top-[4.75rem] max-sm:inset-x-3 max-sm:mt-0 max-sm:w-auto sm:end-0 sm:w-80">
        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3 dark:border-slate-700">
            <p class="text-sm font-bold">اعلان‌ها</p>
            <div class="flex items-center gap-3">
                @if ($unreadCount > 0)
                    {{-- خواندن همه — آیکون تیک --}}
                    <button type="button" wire:click="markAllRead" wire:loading.attr="disabled" wire:target="markAllRead"
                            title="خواندن همه" aria-label="خواندن همه اعلان‌ها"
                            class="flex items-center gap-1 text-[11px] font-semibold text-primary-600 hover:text-primary-700 dark:text-primary-400 disabled:opacity-50">
                        <x-icon name="check" size="3.5" />
                        خواندن همه
                    </button>
                @endif
                @if ($notifications->isNotEmpty())
                    {{-- پاک کردن همه — آیکون سطل --}}
                    <button type="button" wire:click="clearAll" wire:loading.attr="disabled" wire:target="clearAll"
                            wire:confirm="همه اعلان‌ها پاک شوند؟ این عملیات قابل بازگشت نیست."
                            title="پاک کردن همه" aria-label="پاک کردن همه اعلان‌ها"
                            class="flex items-center gap-1 text-[11px] font-semibold text-rose-500 hover:text-rose-600 disabled:opacity-50">
                        <x-icon name="trash" size="3.5" />
                        پاک کردن
                    </button>
                @endif
            </div>
        </div>

        @forelse ($notifications as $notification)
            @php
                $style = $service->styleFor($notification->type);
                $instance = $notification->type ? new $notification->type($notification->data) : null;
            @endphp
            <a href="{{ $instance?->url() ?? '#' }}"
               wire:click="markAsRead('{{ $notification->id }}')"
               class="dropdown-item {{ is_null($notification->read_at) ? 'bg-primary-50/50 dark:bg-primary-900/10' : '' }}">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-{{ $style['color'] }}-100 text-{{ $style['color'] }}-600 dark:bg-{{ $style['color'] }}-900/40">
                    <x-icon name="{{ $style['icon'] }}" size="4" />
                </span>
                <span class="min-w-0">
                    <span class="block truncate font-medium">{{ $instance?->title() ?? $style['label'] }}</span>
                    <span class="block truncate text-xs text-slate-500 dark:text-slate-400">{{ $instance?->description() ?? '' }}</span>
                    <span class="block text-[10px] text-slate-400 dark:text-slate-500">{{ $notification->created_at?->diffForHumans() }}</span>
                </span>
                @if (is_null($notification->read_at))
                    <span class="absolute left-2 top-2 h-2 w-2 shrink-0 rounded-full bg-primary-500"></span>
                @endif
            </a>
        @empty
            <div class="px-4 py-10 text-center">
                <x-icon name="bell" size="8" class="mx-auto mb-2 text-slate-300 dark:text-slate-600" />
                <p class="text-sm text-slate-500 dark:text-slate-400">اعلان جدیدی وجود ندارد</p>
            </div>
        @endforelse

        @if ($notifications->isNotEmpty())
            <div class="border-t border-slate-100 px-4 py-2 text-center dark:border-slate-700">
                <a href="{{ route('admin.notifications.index') }}" class="text-[11px] font-semibold text-primary-600 hover:text-primary-700 dark:text-primary-400">
                    مشاهده همه اعلان‌ها
                </a>
            </div>
        @endif
    </div>
</div>
