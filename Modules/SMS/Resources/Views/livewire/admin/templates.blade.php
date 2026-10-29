<div>
    @if ($successToast)
        <div class="mb-5 flex items-center gap-2 p-4 text-sm font-bold rounded-2xl glass-card !border-emerald-300/60 dark:!border-emerald-700/40 !text-emerald-600 dark:!text-emerald-400"
             x-data="{ show: true }" x-init="setTimeout(() => show = false, 3500)" x-show="show" x-transition.opacity.duration.500ms>
            <x-icon name="check" size="4" />
            {{ $successMessage }}
        </div>
    @endif

    {{-- دکمه قالب جدید --}}
    <div class="mb-5 flex justify-end">
        <button type="button" wire:click="openCreateForm" class="btn-primary !px-4 !py-2 !text-xs">
            <x-icon name="plus" size="4" />
            قالب جدید
        </button>
    </div>

    {{-- فرم ایجاد/ویرایش --}}
    @if ($showForm)
        <div class="glass-card mb-6 space-y-4 p-6" wire:key="sms-template-form">
            <h3 class="font-bold text-slate-900 dark:text-white">{{ $editing ? 'ویرایش قالب' : 'قالب جدید' }}</h3>

            <form wire:submit="save" class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">کلید (key)</label>
                    <input type="text" wire:model="form.key" dir="ltr" placeholder="consultation_booked"
                           class="w-full rounded-xl border border-slate-200 bg-white/80 px-4 py-2.5 text-sm text-slate-800 outline-none focus:border-primary-400 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-100">
                    @error('form.key') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">عنوان</label>
                    <input type="text" wire:model="form.title" placeholder="ثبت مشاوره"
                           class="w-full rounded-xl border border-slate-200 bg-white/80 px-4 py-2.5 text-sm text-slate-800 outline-none focus:border-primary-400 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-100">
                    @error('form.title') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">متن قالب</label>
                    <textarea wire:model="form.body" rows="3" placeholder="سلام {name}، مشاوره شما برای {date} ثبت شد."
                              class="w-full rounded-xl border border-slate-200 bg-white/80 px-4 py-2.5 text-sm text-slate-800 outline-none focus:border-primary-400 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-100"></textarea>
                    @error('form.body') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">متغیرها (با کاما)</label>
                    <input type="text" wire:model="form.variables" dir="ltr" placeholder="name, date"
                           class="w-full rounded-xl border border-slate-200 bg-white/80 px-4 py-2.5 text-sm text-slate-800 outline-none focus:border-primary-400 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-100">
                </div>

                <label class="flex items-center justify-between gap-3 self-end rounded-xl bg-slate-50 p-3 dark:bg-slate-800/60">
                    <span class="text-sm font-medium text-slate-700 dark:text-slate-300">فعال</span>
                    <input type="checkbox" wire:model="form.is_active" class="h-4 w-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                </label>

                @if ($preview)
                    <div class="sm:col-span-2 rounded-xl bg-primary-50 p-4 text-xs text-slate-600 dark:bg-primary-950/40 dark:text-slate-300">
                        <b class="mb-1 block">پیش‌نمایش:</b>
                        {{ $preview }}
                    </div>
                @endif

                <div class="flex gap-3 sm:col-span-2">
                    <button type="submit" class="btn-primary !px-5 !py-2.5 !text-xs">ذخیره</button>
                    <button type="button" wire:click="$set('showForm', false)" class="btn-outline !px-5 !py-2.5 !text-xs">انصراف</button>
                </div>
            </form>
        </div>
    @endif

    {{-- لیست قالب‌ها --}}
    <div class="space-y-4">
        @forelse ($templates as $template)
            <div class="glass-card p-5" wire:key="tpl-{{ $template->id }}">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <div class="mb-1.5 flex flex-wrap items-center gap-2">
                            <span class="rounded-lg bg-slate-100 px-2.5 py-1 font-mono text-xs text-slate-500 dark:bg-slate-800 dark:text-slate-400" dir="ltr">{{ $template->key }}</span>
                            <span class="rounded-lg px-2.5 py-1 text-xs font-semibold {{ $template->is_active ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400' : 'bg-slate-100 text-slate-500 dark:bg-slate-800' }}">
                                {{ $template->is_active ? 'فعال' : 'غیرفعال' }}
                            </span>
                        </div>
                        <h3 class="font-bold text-slate-900 dark:text-white">{{ $template->title }}</h3>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $template->body }}</p>
                        @if ($template->variables)
                            <p class="mt-2 text-xs text-slate-400" dir="ltr">{{ implode(', ', $template->variables) }}</p>
                        @endif
                    </div>
                    <div class="flex shrink-0 gap-2">
                        <button type="button" wire:click="edit({{ $template->id }})" class="btn-outline !px-3.5 !py-2 !text-xs">ویرایش</button>
                        <button type="button" wire:click="delete({{ $template->id }})" wire:confirm="آیا از حذف این قالب مطمئن هستید؟"
                                class="btn-outline !px-3.5 !py-2 !text-xs !border-rose-200 !text-rose-500 hover:!border-rose-300">حذف</button>
                    </div>
                </div>
            </div>
        @empty
            <div class="glass-card p-10 text-center">
                <p class="text-sm text-slate-500 dark:text-slate-400">هنوز قالبی تعریف نشده است.</p>
            </div>
        @endforelse
    </div>
</div>