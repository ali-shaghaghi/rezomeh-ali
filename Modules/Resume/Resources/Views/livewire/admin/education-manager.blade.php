<div>
    {{-- هدر صفحه --}}
    <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
        <div>
            <h2 class="text-2xl font-bold sm:text-3xl">مدیریت تحصیلات</h2>
            <p class="mt-1 text-slate-500 dark:text-slate-400">{{ $educations->total() }} مقطع تحصیلی ثبت‌شده</p>
        </div>
        @can('create', \Modules\Resume\Models\Education::class)
            <button wire:click="openCreateModal"
                    class="group flex items-center gap-2 rounded-xl bg-primary-500 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary-500/25 transition-all duration-200 hover:shadow-xl hover:shadow-primary-500/35 hover:-translate-y-0.5 active:translate-y-0">
                <span class="flex items-center justify-center w-5 h-5 transition duration-300 rounded-md bg-white/20 group-hover:rotate-90">
                    <x-icon name="plus" size="3" />
                </span>
                اضافه کردن مقطع تحصیلی
            </button>
        @endcan
    </div>

    {{-- جستجو --}}
    <div class="mb-6 chart-card">
        <div class="relative">
            
            <input type="text" wire:model.live.debounce.400ms="search" placeholder="جستجوی دانشگاه، رشته یا مقطع..."
                   class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pr-3 text-sm transition focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-surface-800" />
        </div>
    </div>

    {{-- نوتیفیکیشن موفقیت --}}
    @if($successToast)
        <div class="px-4 py-3 mb-6 text-sm rounded-xl bg-emerald-100 text-emerald-700 animate-slide-up dark:bg-emerald-900/30 dark:text-emerald-300"
             x-data="{ show: true }" x-init="setTimeout(() => show = false, 3500)" x-show="show" x-transition.opacity.duration.500ms>
            {{ $successMessage }}
        </div>
    @endif

    {{-- گرید تحصیلات (الگوی Roles) — بدون wire:loading.remove تا صفحه فلیکر نزند --}}
    <div class="grid gap-6 sm:grid-cols-2 animate-slide-up" wire:loading.class="opacity-60 pointer-events-none" wire:target="save, deleteEducation, toggleActive, toggleResume, move">
        @forelse($educations as $education)
            <section class="flex flex-col education-card chart-card">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-3">
                        <span class="flex items-center justify-center h-9 w-9 rounded-xl bg-primary-100 text-primary-600 dark:bg-primary-900/40 dark:text-primary-400">
                            <x-icon name="shield" size="4" />
                        </span>
                        <div>
                            <h3 class="text-base font-bold">{{ $education->institution }}</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $education->degree }} · {{ $education->field_of_study }}</p>
                        </div>
                    </div>
                    @if($education->is_current)
                        <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                            در حال تحصیل
                        </span>
                    @endif
                </div>

                {{-- بازه زمانی و معدل --}}
                <div class="mb-3 text-sm">
                    <p class="text-slate-500 dark:text-slate-400" dir="ltr">
                        {{ $education->start_date->format('Y/m/d') }}
                        {{ $education->is_current ? '— اکنون' : ' تا ' . ($education->end_date?->format('Y/m/d') ?? '—') }}
                    </p>
                    @if($education->grade)
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">معدل: <span class="font-medium text-slate-700 dark:text-slate-200">{{ $education->grade }}</span></p>
                    @endif
                </div>

                {{-- توضیحات --}}
                @if($education->description)
                    <p class="mb-3 text-sm text-slate-500 dark:text-slate-400">{{ \Illuminate\Support\Str::limit($education->description, 100) }}</p>
                @endif

                {{-- وضعیت‌ها --}}
                <div class="flex flex-wrap gap-2 mb-3">
                    <span class="px-2 py-1 text-xs rounded-lg {{ $education->is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400' }}">
                        {{ $education->is_active ? 'فعال' : 'غیرفعال' }}
                    </span>
                    <span class="px-2 py-1 text-xs rounded-lg {{ $education->show_in_resume ? 'bg-primary-100 text-primary-700 dark:bg-primary-900/40 dark:text-primary-300' : 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400' }}">
                        {{ $education->show_in_resume ? 'نمایش در رزومه' : 'خارج از رزومه' }}
                    </span>
                </div>

                {{-- دکمه‌های عملیات --}}
                <div class="flex flex-wrap gap-2 pt-3 mt-auto border-t border-slate-100 dark:border-slate-700">
                    @can('update', $education)
                        <button wire:click="toggleActive({{ $education->id }})"
                                class="flex-1 px-3 py-2 text-xs font-medium text-center transition rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-slate-700 dark:text-slate-300 dark:hover:bg-slate-600">
                            {{ $education->is_active ? 'غیرفعال' : 'فعال' }}
                        </button>
                        <button wire:click="toggleResume({{ $education->id }})"
                                class="flex-1 px-3 py-2 text-xs font-medium text-center transition rounded-lg bg-slate-100 text-slate-600 hover:bg-primary-100 hover:text-primary-600 dark:bg-slate-700 dark:text-slate-300 dark:hover:bg-primary-900/40 dark:hover:text-primary-300">
                            {{ $education->show_in_resume ? 'حذف از رزومه' : 'نمایش در رزومه' }}
                        </button>
                        <button wire:click="openEditModal({{ $education->id }})"
                                class="px-3 py-2 text-xs font-medium transition rounded-lg bg-slate-100 text-slate-600 hover:bg-primary-100 hover:text-primary-600 dark:bg-slate-700 dark:text-slate-300 dark:hover:bg-primary-900/40 dark:hover:text-primary-300">
                            ویرایش
                        </button>
                    @endcan
                    @can('delete', $education)
                        <button wire:click="confirmDelete({{ $education->id }})"
                                class="px-3 py-2 text-xs font-medium transition rounded-lg bg-slate-100 text-rose-500 hover:bg-rose-100 dark:bg-slate-700 dark:text-rose-400 dark:hover:bg-rose-900/30">
                            حذف
                        </button>
                    @endcan
                </div>
            </section>
        @empty
            <div class="py-16 text-center col-span-full chart-card">
                <span class="flex mx-auto mb-4 w-fit text-slate-300 dark:text-slate-600">
                    <x-icon name="shield" size="12" />
                </span>
                <p class="font-medium text-slate-600 dark:text-slate-300">{{ $search ? 'مقطعی یافت نشد' : 'هنوز مقطع تحصیلی ثبت نشده است' }}</p>
                <p class="mt-1 text-sm text-slate-400 dark:text-slate-500">{{ $search ? 'عبارت دیگری را جستجو کنید' : 'اولین مقطع تحصیلی خود را اضافه کنید' }}</p>
            </div>
        @endforelse
    </div>

    {{-- صفحه‌بندی --}}
    @if($educations->hasPages())
        <div class="mt-6">
            {{ $educations->links() }}
        </div>
    @endif

    {{-- مودال افزودن/ویرایش --}}
    @if($modalOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
            <div class="w-full max-w-md mx-4 max-h-[90vh] overflow-y-auto rounded-2xl border border-slate-200/50 bg-white p-4 shadow-2xl animate-slide-up dark:border-slate-700/50 dark:bg-surface-800 sm:p-5">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-3">
                        <span class="flex items-center justify-center h-9 w-9 rounded-xl bg-primary-100 text-primary-600 dark:bg-primary-900/40 dark:text-primary-400">
                            <x-icon name="shield" size="4" />
                        </span>
                        <div>
                            <h3 class="text-base font-bold">{{ $editing ? 'ویرایش مقطع تحصیلی' : 'اضافه کردن مقطع تحصیلی' }}</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $editing ? 'اطلاعات را به‌روزرسانی کنید' : 'اطلاعات مقطع تحصیلی را وارد کنید' }}</p>
                        </div>
                    </div>
                    <button wire:click="$set('modalOpen', false)" class="p-2 transition rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-700 dark:hover:text-slate-300">
                        <x-icon name="close" />
                    </button>
                </div>

                <form wire:submit.prevent="save" class="space-y-3">
                    {{-- دانشگاه و مقطع --}}
                    <div class="grid gap-3 mb-1 sm:grid-cols-2">
                        <div>
                            <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">دانشگاه / موسسه <span class="text-red-500">*</span></label>
                            <input type="text" wire:model.blur="institution" required class="w-full px-3 py-2 text-sm transition border rounded-xl border-slate-200 bg-slate-50 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white" placeholder="مثال: دانشگاه تهران" />
                            @error('institution') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">مقطع <span class="text-red-500">*</span></label>
                            <input type="text" wire:model.blur="degree" required class="w-full px-3 py-2 text-sm transition border rounded-xl border-slate-200 bg-slate-50 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white" placeholder="مثال: کارشناسی" />
                            @error('degree') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- رشته و معدل --}}
                    <div class="grid gap-3 mb-1 sm:grid-cols-2">
                        <div>
                            <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">رشته تحصیلی <span class="text-red-500">*</span></label>
                            <input type="text" wire:model.blur="field_of_study" required class="w-full px-3 py-2 text-sm transition border rounded-xl border-slate-200 bg-slate-50 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white" placeholder="مثال: مهندسی نرم‌افزار" />
                            @error('field_of_study') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">معدل</label>
                            <input type="text" wire:model.blur="grade" class="w-full px-3 py-2 text-sm transition border rounded-xl border-slate-200 bg-slate-50 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white" placeholder="مثال: ۱۷.۵" />
                            @error('grade') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- بازه زمانی --}}
                    <div class="grid gap-3 mb-1 sm:grid-cols-2">
                        <div>
                            <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">تاریخ شروع <span class="text-red-500">*</span></label>
                            <input type="date" wire:model.blur="start_date" required dir="ltr" class="w-full px-3 py-2 text-sm transition border rounded-xl border-slate-200 bg-slate-50 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white" />
                            @error('start_date') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">تاریخ پایان</label>
                            <input type="date" wire:model.blur="end_date" dir="ltr" {{ $is_current ? 'disabled' : '' }} class="w-full px-3 py-2 text-sm transition border rounded-xl border-slate-200 bg-slate-50 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 disabled:opacity-50 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white" />
                            @error('end_date') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- چک‌باکس‌ها --}}
                    <div class="flex flex-wrap gap-4 p-3 rounded-xl bg-slate-50 dark:bg-slate-700/50">
                        <label class="flex items-center gap-2 text-xs cursor-pointer text-slate-700 dark:text-slate-300">
                            <input type="checkbox" wire:model.live="is_current" class="rounded border-slate-300 text-primary-500 focus:ring-primary-500/20" />
                            در حال تحصیل
                        </label>
                        <label class="flex items-center gap-2 text-xs cursor-pointer text-slate-700 dark:text-slate-300">
                            <input type="checkbox" wire:model="is_active" class="rounded border-slate-300 text-primary-500 focus:ring-primary-500/20" />
                            فعال
                        </label>
                        <label class="flex items-center gap-2 text-xs cursor-pointer text-slate-700 dark:text-slate-300">
                            <input type="checkbox" wire:model="show_in_resume" class="rounded border-slate-300 text-primary-500 focus:ring-primary-500/20" />
                            نمایش در رزومه
                        </label>
                    </div>

                    {{-- توضیحات --}}
                    <div>
                        <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">توضیحات</label>
                        <textarea wire:model.blur="description" rows="3" class="w-full px-3 py-2 text-sm transition border resize-none rounded-xl border-slate-200 bg-slate-50 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white" placeholder="توضیحات تکمیلی..."></textarea>
                        @error('description') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- دکمه‌ها --}}
                    <div class="flex gap-3 pt-3 mt-2">
                        <button type="submit" wire:loading.attr="disabled"
                                class="flex-1 rounded-xl bg-primary-500 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-primary-500/25 transition-all hover:shadow-lg hover:shadow-primary-500/35 disabled:opacity-50">
                            {{ $editing ? 'ذخیره تغییرات' : 'ذخیره مقطع' }}
                        </button>
                        <button type="button" wire:click="$set('modalOpen', false)"
                                class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-700">
                            لغو
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- مودال تایید حذف --}}
    @if($confirmingDelete)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
            <div class="w-full max-w-md p-5 bg-white border shadow-2xl rounded-2xl border-slate-200/50 animate-slide-up dark:border-slate-700/50 dark:bg-surface-800">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-3">
                        <span class="flex items-center justify-center h-9 w-9 rounded-xl bg-rose-100 text-rose-600 dark:bg-rose-900/40 dark:text-rose-400">
                            <x-icon name="trash" size="4" />
                        </span>
                        <div>
                            <h3 class="text-base font-bold">حذف مقطع تحصیلی</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">این عملیات قابل بازگشت نیست</p>
                        </div>
                    </div>
                    <button wire:click="$set('confirmingDelete', false)" class="p-2 transition rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-700 dark:hover:text-slate-300">
                        <x-icon name="close" />
                    </button>
                </div>
                <p class="text-sm text-slate-600 dark:text-slate-300">آیا از حذف این مقطع تحصیلی مطمئن هستید؟</p>
                <div class="flex gap-3 mt-5">
                    <button wire:click="deleteEducation" wire:loading.attr="disabled"
                            class="flex-1 rounded-xl bg-rose-500 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-rose-500/25 transition hover:bg-rose-600 disabled:opacity-50">
                        بله، حذف شود
                    </button>
                    <button wire:click="$set('confirmingDelete', false)"
                            class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-700">
                        لغو
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
