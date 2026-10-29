<div>
    {{-- پیام عملیات (موفق / خطای برگشت‌ناپذیری «عدم حضور») --}}
    @if ($notice)
        <div class="mb-4 rounded-xl p-4 text-sm font-bold {{ $noticeType === 'error' ? 'bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400' : 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400' }}"
             x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show">
            {{ $notice }}
        </div>
    @endif

    {{-- فیلترها --}}
    <div class="glass-card mb-5 grid gap-3 p-4 sm:grid-cols-2">
        <input type="search" wire:model.live.debounce.300ms="search" placeholder="جستجوی نام کاربر یا مشاور..."
               class="w-full rounded-xl border border-slate-200 bg-white/80 px-4 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-100">
        <select wire:model.live="status" class="w-full rounded-xl border border-slate-200 bg-white/80 px-4 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-100">
            <option value="">همه وضعیت‌ها</option>
            @foreach ($statuses as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </select>
    </div>

    {{-- جدول رزروها --}}
    <div class="glass-card overflow-x-auto p-0">
        <table class="w-full text-right text-sm">
            <thead>
                <tr class="border-b border-slate-200 text-xs text-slate-500 dark:border-slate-700 dark:text-slate-400">
                    <th class="p-4 font-medium">کاربر</th>
                    <th class="p-4 font-medium">مشاور</th>
                    <th class="p-4 font-medium">نوع</th>
                    <th class="p-4 font-medium">زمان</th>
                    <th class="p-4 font-medium">وضعیت</th>
                    <th class="p-4 font-medium">عملیات</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($consultations as $c)
                    <tr class="border-b border-slate-100 last:border-0 dark:border-slate-800" wire:key="cons-{{ $c->id }}">
                        <td class="p-4 font-bold">{{ $c->user?->name }}</td>
                        <td class="p-4">{{ $c->consultant?->personName() }}</td>
                        <td class="p-4 text-xs">{{ $c->type?->name }}</td>
                        <td class="p-4 text-xs">{{ $c->slot?->date ? jdate('Y/m/d', $c->slot->date) : '—' }} {{ substr($c->slot?->start_time ?? '', 0, 5) }}</td>
                        <td class="p-4">
                            <span class="rounded-md px-2 py-0.5 text-xs font-semibold bg-{{ $c->statusEnum()->color() }}-100 text-{{ $c->statusEnum()->color() }}-700 dark:bg-{{ $c->statusEnum()->color() }}-950/60 dark:text-{{ $c->statusEnum()->color() }}-400">{{ $c->statusEnum()->label() }}</span>
                        </td>
                        <td class="p-4">
                            <div class="flex gap-2">
                                @if ($c->status === 'pending')
                                    <button type="button" wire:click="confirm({{ $c->id }})" class="btn-outline !px-3 !py-1.5 !text-xs !text-emerald-600">تایید</button>
                                    <button type="button" wire:click="cancel({{ $c->id }})" class="btn-outline !px-3 !py-1.5 !text-xs !border-rose-200 !text-rose-500">لغو</button>
                                @elseif (in_array($c->status, ['confirmed'], true))
                                    <button type="button" wire:click="setStatus({{ $c->id }}, 'done')" class="btn-outline !px-3 !py-1.5 !text-xs">انجام شد</button>
                                    <button type="button" wire:click="setStatus({{ $c->id }}, 'no_show')" class="btn-outline !px-3 !py-1.5 !text-xs">عدم حضور</button>
                                @elseif ($c->status === 'no_show')
                                    {{-- «عدم حضور» برگشت‌ناپذیر است — عمداً هیچ دکمه بازگشتی به حالت حاضر/تاییدشده ندارد --}}
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-slate-400">
                                        ثبت‌شده — قابل بازگشت به «حاضر» نیست
                                    </span>
                                @elseif ($c->status === 'done')
                                    <button type="button" wire:click="setStatus({{ $c->id }}, 'confirmed')" class="btn-outline !px-3 !py-1.5 !text-xs !text-sky-600">بازگشت به تایید شده</button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="p-10 text-center text-sm text-slate-500">هنوز رزروی ثبت نشده است.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $consultations->links() }}</div>
</div>