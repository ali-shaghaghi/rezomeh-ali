<div>
    @if ($successToast)
        <div class="mb-5 p-4 text-sm font-bold text-emerald-600 dark:text-emerald-400" x-data="{ show: true }" x-init="setTimeout(() => show = false, 3500)" x-show="show">{{ $successMessage }}</div>
    @endif

    <div class="mb-5 flex justify-end">
        <button type="button" wire:click="openCreateForm" class="btn-primary !px-4 !py-2 !text-xs">مشاور جدید</button>
    </div>

    {{-- ===================== فهرست اعضای تیم (از جدول ادمین‌ها) ===================== --}}
    <div class="glass-card mb-6 p-5">
        <div class="mb-1 flex flex-wrap items-center justify-between gap-2">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">اعضای تیم (خوانده‌شده از جدول ادمین‌ها)</h3>
            <span class="rounded-md bg-accent-100 px-1.5 py-0.5 text-[10px] font-bold text-accent-700 dark:bg-accent-900/40 dark:text-accent-300">شامل خود شما</span>
        </div>
        <p class="mb-4 text-xs text-slate-400">مشاوران از میان اعضای تیم انتخاب می‌شوند؛ هر عضوی که اضافه شود در صفحه مشاوره سایت نمایش داده می‌شود.</p>
        <div class="flex flex-wrap gap-2">
            @foreach ($admins as $admin)
                @php($linked = $linkedConsultants->get($admin->id))
                <div class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white/70 px-3 py-2 text-xs dark:border-slate-700 dark:bg-slate-900/60" wire:key="team-{{ $admin->id }}">
                    <span class="font-bold text-slate-700 dark:text-slate-200">{{ $admin->name }}</span>
                    @if ($linked)
                        <span class="rounded-md bg-emerald-100 px-1.5 py-0.5 text-[10px] font-bold text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400">مشاور</span>
                        <button type="button" wire:click="edit({{ $linked->id }})" class="text-[11px] font-bold text-primary-600 transition hover:text-primary-700 dark:text-primary-400">ویرایش پروفایل</button>
                    @else
                        <button type="button" wire:click="quickAdd({{ $admin->id }})" class="text-[11px] font-bold text-primary-600 transition hover:text-primary-700 dark:text-primary-400">+ افزودن به عنوان مشاور</button>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    {{-- فرم --}}
    @if ($showForm)
        <div class="glass-card mb-6 space-y-4 p-6" wire:key="consultant-form">
            <h3 class="font-bold text-slate-900 dark:text-white">{{ $editing ? 'ویرایش مشاور' : 'مشاور جدید' }}</h3>
            <form wire:submit="save" class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">عضو تیم (ادمین)</label>
                    <select wire:model="form.admin_id" class="w-full rounded-xl border border-slate-200 bg-white/80 px-4 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-100">
                        <option value="">انتخاب کنید...</option>
                        @foreach ($admins as $admin)
                            <option value="{{ $admin->id }}">{{ $admin->name }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-[11px] text-slate-400">فهرست از جدول ادمین‌ها (اعضای تیم) — شامل خود شما.</p>
                    @error('form.admin_id') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">تخصص</label>
                    <input type="text" wire:model="form.specialty" class="w-full rounded-xl border border-slate-200 bg-white/80 px-4 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-100">
                    @error('form.specialty') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">بیوگرافی</label>
                    <textarea wire:model="form.bio" rows="3" class="w-full rounded-xl border border-slate-200 bg-white/80 px-4 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-100"></textarea>
                </div>
                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">عکس مشاور (برای نمایش در صفحه مشاوره)</label>
                    <div class="flex flex-wrap items-center gap-3">
                        @if ($avatarUpload)
                            <img src="{{ $avatarUpload->temporaryUrl() }}" alt="پیش‌نمایش" class="h-14 w-14 rounded-xl object-cover">
                        @elseif ($existingAvatar)
                            <img src="{{ asset('storage/'.$existingAvatar) }}" alt="عکس فعلی" class="h-14 w-14 rounded-xl object-cover">
                        @endif
                        <label class="inline-flex cursor-pointer items-center gap-1.5 rounded-xl border border-slate-200 px-3.5 py-2 text-xs font-bold text-slate-600 transition hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-700">
                            ⇪ آپلود عکس
                            <input type="file" wire:model="avatarUpload" accept="image/*" class="hidden">
                        </label>
                        @if ($existingAvatar && ! $avatarUpload)
                            <button type="button" wire:click="removeAvatar" class="text-xs font-bold text-rose-500 hover:text-rose-600">حذف عکس</button>
                        @endif
                        <span wire:loading wire:target="avatarUpload" class="text-[11px] font-bold text-primary-600">در حال آپلود…</span>
                    </div>
                    @error('avatarUpload') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">نرخ ساعتی (تومان)</label>
                    <input type="number" wire:model="form.hourly_rate" min="0" class="w-full rounded-xl border border-slate-200 bg-white/80 px-4 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-100">
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">امتیاز (0-5)</label>
                    <input type="number" wire:model="form.rating" min="0" max="5" step="0.1" class="w-full rounded-xl border border-slate-200 bg-white/80 px-4 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-100">
                </div>
                <label class="flex items-center justify-between gap-3 self-end rounded-xl bg-slate-50 p-3 dark:bg-slate-800/60">
                    <span class="text-sm font-medium text-slate-700 dark:text-slate-300">فعال</span>
                    <input type="checkbox" wire:model="form.is_active" class="h-4 w-4 rounded border-slate-300 text-primary-600">
                </label>
                <div class="flex gap-3 sm:col-span-2">
                    <button type="submit" class="btn-primary !px-5 !py-2.5 !text-xs">ذخیره</button>
                    <button type="button" wire:click="$set('showForm', false)" class="btn-outline !px-5 !py-2.5 !text-xs">انصراف</button>
                </div>
            </form>
        </div>
    @endif

    {{-- لیست --}}
    <div class="space-y-4">
        @forelse ($consultants as $c)
            <div class="glass-card flex flex-wrap items-center justify-between gap-3 p-5" wire:key="con-{{ $c->id }}">
                <div class="flex min-w-0 flex-1 items-center gap-3">
                    @if ($c->avatar)
                        <img src="{{ asset('storage/'.$c->avatar) }}" alt="{{ $c->personName() }}" class="h-11 w-11 shrink-0 rounded-xl object-cover">
                    @else
                        <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-primary-500 to-accent-500 font-black text-white">{{ mb_substr($c->personName(), 0, 1) }}</span>
                    @endif
                    <div class="min-w-0">
                        <h3 class="flex items-center gap-2 font-bold text-slate-900 dark:text-white">
                            {{ $c->personName() }}
                            @if ($c->admin_id)
                                <span class="rounded-md bg-accent-100 px-1.5 py-0.5 text-[10px] font-bold text-accent-700 dark:bg-accent-900/40 dark:text-accent-300">ادمین</span>
                            @endif
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ $c->specialty }} · {{ $c->hourly_rate > 0 ? fa_digits(number_format((float) $c->hourly_rate)).' تومان/ساعت' : '—' }}</p>
                    </div>
                </div>
                <div class="flex shrink-0 items-center gap-2">
                    <span class="rounded-md px-2 py-0.5 text-xs font-semibold {{ $c->is_active ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400' : 'bg-slate-100 text-slate-500 dark:bg-slate-800' }}">{{ $c->is_active ? 'فعال' : 'غیرفعال' }}</span>
                    <button type="button" wire:click="toggleActive({{ $c->id }})" class="btn-outline !px-3 !py-1.5 !text-xs">{{ $c->is_active ? 'غیرفعال کن' : 'فعال کن' }}</button>
                    <button type="button" wire:click="edit({{ $c->id }})" class="btn-outline !px-3 !py-1.5 !text-xs">ویرایش</button>
                    <button type="button" wire:click="delete({{ $c->id }})" wire:confirm="حذف مشاور همراه زمان‌بندی و اسلات‌هایش؟" class="btn-outline !px-3 !py-1.5 !text-xs !border-rose-200 !text-rose-500">حذف</button>
                </div>
            </div>
        @empty
            <div class="glass-card p-10 text-center text-sm text-slate-500">هنوز مشاوری ثبت نشده است.</div>
        @endforelse
    </div>
</div>