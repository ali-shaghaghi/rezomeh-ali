<div>
    @if ($successToast)
        <div class="mb-5 p-4 text-sm font-bold text-emerald-600 dark:text-emerald-400" x-data="{ show: true }" x-init="setTimeout(() => show = false, 3500)" x-show="show">{{ $successMessage }}</div>
    @endif

    {{-- انتخاب مشاور --}}
    <div class="glass-card mb-6 p-5" wire:key="pick-consultant">
        <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">انتخاب مشاور</label>
        {{-- wire:model.live → انتخاب مشاور بلافاصله اجرا می‌شود (قبلاً بدون live بود و کار نمی‌کرد) --}}
        <select wire:model.live="consultantId" class="w-full max-w-md rounded-xl border border-slate-200 bg-white/80 px-4 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-100">
            <option value="">انتخاب کنید...</option>
            @foreach ($consultants as $c)
                <option value="{{ $c->id }}">{{ $c->user?->name }} — {{ $c->specialty }}</option>
            @endforeach
        </select>
        @error('consultantId') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
    </div>

    @if ($consultantId)
        {{-- تقویم شمسی ۱۴ روز آینده — دقیقاً همان اسلات‌هایی که پنل کاربران می‌بیند --}}
        <div class="glass-card mb-6 p-5">
            <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">تقویم شمسی ۱۴ روز آینده (اتصال به پنل کاربران)</h3>
                <span class="text-[11px] text-slate-500">اسلاتها از همین زمان‌بندی تولید و در پنل کاربران نمایش داده می‌شوند</span>
            </div>
            <div class="grid grid-cols-2 gap-2 sm:grid-cols-4 lg:grid-cols-7">
                @forelse ($calendar as $day)
                    <div class="rounded-xl border border-slate-200 p-2.5 text-center dark:border-slate-700" wire:key="cal-{{ $day['date'] }}">
                        <p class="text-[10px] text-slate-500">{{ $day['day_label'] }}</p>
                        <p class="text-base font-black text-slate-900 dark:text-white">{{ $day['jalali_date'] }}</p>
                        <p class="mt-1 text-[10px] {{ $day['free_count'] > 0 ? 'text-emerald-500' : 'text-slate-400' }}">
                            آزاد: {{ fa_digits($day['free_count']) }}
                        </p>
                        <p class="text-[10px] {{ $day['booked_count'] > 0 ? 'text-primary-500' : 'text-slate-400' }}">
                            رزروشده: {{ fa_digits($day['booked_count']) }}
                        </p>
                    </div>
                @empty
                    <p class="col-span-full text-xs text-slate-500">ابتدا بازه کاری تعریف کنید تا تقویم پر شود.</p>
                @endforelse
            </div>
        </div>

        {{-- دکمه بازه جدید --}}
        <div class="mb-5 flex justify-end">
            <button type="button" wire:click="openCreateForm" class="btn-primary !px-4 !py-2 !text-xs">بازه کاری جدید</button>
        </div>

        {{-- فرم --}}
        @if ($showForm)
            <div class="glass-card mb-6 space-y-4 p-6" wire:key="schedule-form">
                <h3 class="font-bold text-slate-900 dark:text-white">{{ $editId ? 'ویرایش بازه' : 'بازه کاری جدید' }}</h3>
                <form wire:submit="save" class="grid gap-4 sm:grid-cols-4">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">روز هفته</label>
                        <select wire:model="form.day_of_week" class="w-full rounded-xl border border-slate-200 bg-white/80 px-4 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-100">
                            @foreach ($days as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">از ساعت</label>
                        <input type="time" wire:model="form.start_time" class="w-full rounded-xl border border-slate-200 bg-white/80 px-4 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-100">
                        @error('form.start_time') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">تا ساعت</label>
                        <input type="time" wire:model="form.end_time" class="w-full rounded-xl border border-slate-200 bg-white/80 px-4 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-100">
                        @error('form.end_time') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>
                    <label class="flex items-center justify-between gap-3 self-end rounded-xl bg-slate-50 p-3 dark:bg-slate-800/60">
                        <span class="text-sm font-medium text-slate-700 dark:text-slate-300">فعال</span>
                        <input type="checkbox" wire:model="form.is_active" class="h-4 w-4 rounded border-slate-300 text-primary-600">
                    </label>
                    <div class="flex gap-3 sm:col-span-4">
                        <button type="submit" class="btn-primary !px-5 !py-2.5 !text-xs">ذخیره</button>
                        <button type="button" wire:click="$set('showForm', false)" class="btn-outline !px-5 !py-2.5 !text-xs">انصراف</button>
                    </div>
                </form>
            </div>
        @endif

        {{-- لیست بازه‌ها --}}
        <div class="space-y-3">
            @forelse ($schedules as $s)
                <div class="glass-card flex flex-wrap items-center justify-between gap-3 p-4" wire:key="sch-{{ $s->id }}">
                    <div class="flex items-center gap-3">
                        <span class="rounded-lg bg-primary-50 px-3 py-1.5 text-xs font-bold text-primary-600 dark:bg-primary-950/50 dark:text-primary-400">{{ $s->dayLabel() }}</span>
                        <span class="text-sm font-bold text-slate-800 dark:text-slate-200" dir="ltr">{{ substr($s->start_time, 0, 5) }} - {{ substr($s->end_time, 0, 5) }}</span>
                        @if (! $s->is_active)
                            <span class="rounded-md bg-slate-100 px-2 py-0.5 text-xs text-slate-500">غیرفعال</span>
                        @endif
                    </div>
                    <div class="flex gap-2">
                        <button type="button" wire:click="edit({{ $s->id }})" class="btn-outline !px-3 !py-1.5 !text-xs">ویرایش</button>
                        <button type="button" wire:click="delete({{ $s->id }})" wire:confirm="حذف این بازه کاری؟" class="btn-outline !px-3 !py-1.5 !text-xs !border-rose-200 !text-rose-500">حذف</button>
                    </div>
                </div>
            @empty
                <div class="glass-card p-8 text-center text-sm text-slate-500">این مشاور هنوز بازه کاری ندارد.</div>
            @endforelse
        </div>
    @else
        <div class="glass-card p-10 text-center text-sm text-slate-500">ابتدا یک مشاور انتخاب کنید.</div>
    @endif
</div>