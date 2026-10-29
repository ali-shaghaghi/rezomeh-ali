<div>
    {{-- هدر صفحه --}}
    <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
        <div>
            <h2 class="text-2xl font-bold sm:text-3xl">مدیریت زبان‌ها</h2>
            <p class="mt-1 text-slate-500 dark:text-slate-400">{{ $languages->total() }} زبان ثبت‌شده</p>
        </div>
        @can('create', \Modules\Resume\Models\Language::class)
            <button wire:click="openCreateModal"
                    class="group flex items-center gap-2 rounded-xl bg-primary-500 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary-500/25 transition-all duration-200 hover:shadow-xl hover:shadow-primary-500/35 hover:-translate-y-0.5 active:translate-y-0">
                <span class="flex items-center justify-center w-5 h-5 transition duration-300 rounded-md bg-white/20 group-hover:rotate-90">
                    <x-icon name="plus" size="3" />
                </span>
                اضافه کردن زبان
            </button>
        @endcan
    </div>

    {{-- جستجو --}}
    <div class="mb-6 chart-card">
        <div class="flex flex-wrap gap-3">
            <div class="relative flex-1 min-w-[200px]">
                <input type="text" wire:model.live.debounce.400ms="search" placeholder="جستجوی زبان..."
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pr-3 text-sm transition focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-surface-800" />
            </div>
            @if($search)
                <button wire:click="resetFilters" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-600 transition hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-700">
                    حذف فیلترها
                </button>
            @endif
        </div>
    </div>

    {{-- نوتیفیکیشن موفقیت --}}
    @if($successToast)
        <div class="px-4 py-3 mb-6 text-sm rounded-xl bg-emerald-100 text-emerald-700 animate-slide-up dark:bg-emerald-900/30 dark:text-emerald-300"
             x-data="{ show: true }" x-init="setTimeout(() => show = false, 3500)" x-show="show" x-transition.opacity.duration.500ms>
            {{ $successMessage }}
        </div>
    @endif

    {{-- گرید زبان‌ها (الگوی Roles) --}}
    <div class="grid gap-6 sm:grid-cols-2 animate-slide-up" wire:loading.class="opacity-60 pointer-events-none" wire:target="save, deleteLanguage, toggleActive, toggleResume, move">
        @forelse($languages as $language)
            <section class="flex flex-col chart-card">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-3">
                        <span class="flex items-center justify-center h-9 w-9 rounded-xl bg-primary-100 text-primary-600 dark:bg-primary-900/40 dark:text-primary-400">
                            <x-icon name="mail" size="4" />
                        </span>
                        <div>
                            <h3 class="text-base font-bold">{{ $language->name }}</h3>
                            @if($language->code)
                                <p class="text-xs text-slate-500 dark:text-slate-400" dir="ltr">{{ strtoupper($language->code) }}</p>
                            @endif
                        </div>
                    </div>
                    <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300">
                        {{ $language->level_label }}
                    </span>
                </div>

                {{-- نوار درصد تسلط --}}
                <div class="mb-3">
                    <div class="flex items-center justify-between mb-1 text-xs">
                        <span class="text-slate-500 dark:text-slate-400">میزان تسلط</span>
                        <span class="font-bold text-primary-600 dark:text-primary-400">{{ $language->proficiency }}٪</span>
                    </div>
                    <div class="w-full h-2 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-700">
                        <div class="h-full transition-all duration-500 rounded-full bg-gradient-to-l from-primary-500 to-violet-600"
                             style="width: {{ $language->proficiency }}%"></div>
                    </div>
                </div>

                {{-- توضیح --}}
                @if($language->description)
                    <p class="mb-3 text-sm text-slate-500 dark:text-slate-400">{{ $language->description }}</p>
                @endif

                {{-- وضعیت‌ها --}}
                <div class="flex flex-wrap gap-2 mb-3">
                    <span class="px-2 py-1 text-xs rounded-lg {{ $language->is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400' }}">
                        {{ $language->is_active ? 'فعال' : 'غیرفعال' }}
                    </span>
                    <span class="px-2 py-1 text-xs rounded-lg {{ $language->show_in_resume ? 'bg-primary-100 text-primary-700 dark:bg-primary-900/40 dark:text-primary-300' : 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400' }}">
                        {{ $language->show_in_resume ? 'نمایش در رزومه' : 'خارج از رزومه' }}
                    </span>
                    <span class="px-2 py-1 text-xs rounded-lg bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400" dir="ltr">
                        ترتیب: {{ $language->order }}
                    </span>
                </div>

                {{-- دکمه‌های عملیات --}}
                <div class="flex flex-wrap gap-2 pt-3 mt-auto border-t border-slate-100 dark:border-slate-700">
                    @can('update', $language)
                        <button wire:click="toggleActive({{ $language->id }})"
                                class="flex-1 px-3 py-2 text-xs font-medium text-center transition rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-slate-700 dark:text-slate-300 dark:hover:bg-slate-600">
                            {{ $language->is_active ? 'غیرفعال' : 'فعال' }}
                        </button>
                        <button wire:click="toggleResume({{ $language->id }})"
                                class="flex-1 px-3 py-2 text-xs font-medium text-center transition rounded-lg bg-slate-100 text-slate-600 hover:bg-primary-100 hover:text-primary-600 dark:bg-slate-700 dark:text-slate-300 dark:hover:bg-primary-900/40 dark:hover:text-primary-300">
                            {{ $language->show_in_resume ? 'حذف از رزومه' : 'نمایش در رزومه' }}
                        </button>
                        <button wire:click="openEditModal({{ $language->id }})"
                                class="px-3 py-2 text-xs font-medium transition rounded-lg bg-slate-100 text-slate-600 hover:bg-primary-100 hover:text-primary-600 dark:bg-slate-700 dark:text-slate-300 dark:hover:bg-primary-900/40 dark:hover:text-primary-300">
                            ویرایش
                        </button>
                    @endcan
                    @can('delete', $language)
                        <button wire:click="confirmDelete({{ $language->id }})"
                                class="px-3 py-2 text-xs font-medium transition rounded-lg bg-slate-100 text-rose-500 hover:bg-rose-100 dark:bg-slate-700 dark:text-rose-400 dark:hover:bg-rose-900/30">
                            حذف
                        </button>
                    @endcan
                </div>
            </section>
        @empty
            <div class="py-16 text-center col-span-full chart-card">
                <span class="flex mx-auto mb-4 w-fit text-slate-300 dark:text-slate-600">
                    <x-icon name="mail" size="12" />
                </span>
                <p class="font-medium text-slate-600 dark:text-slate-300">{{ $search ? 'زبانی یافت نشد' : 'هنوز زبانی ثبت نشده است' }}</p>
                <p class="mt-1 text-sm text-slate-400 dark:text-slate-500">{{ $search ? 'عبارت دیگری را جستجو کنید' : 'اولین زبان خارجی خود را اضافه کنید' }}</p>
            </div>
        @endforelse
    </div>

    {{-- صفحه‌بندی --}}
    @if($languages->hasPages())
        <div class="mt-6">
            {{ $languages->links() }}
        </div>
    @endif

    {{-- مودال افزودن/ویرایش (پس‌زمینه بلور + انیمیشن — الگوی Roles) --}}
    @if($modalOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm animate-slide-up sm:p-4">
            <div class="w-full max-w-md mx-4 max-h-[90vh] overflow-y-auto rounded-2xl border border-slate-200/50 bg-white p-4 shadow-2xl animate-slide-up dark:border-slate-700/50 dark:bg-surface-800 sm:p-5">
                {{-- هدر مودال --}}
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-3">
                        <span class="flex items-center justify-center h-9 w-9 rounded-xl bg-primary-100 text-primary-600 dark:bg-primary-900/40 dark:text-primary-400">
                            <x-icon name="mail" size="4" />
                        </span>
                        <div>
                            <h3 class="text-base font-bold">{{ $editing ? 'ویرایش زبان: ' . $name : 'اضافه کردن زبان جدید' }}</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $editing ? 'اطلاعات زبان را به‌روزرسانی کنید' : 'اطلاعات زبان را وارد کنید' }}</p>
                        </div>
                    </div>
                    <button wire:click="$set('modalOpen', false)" class="p-2 transition rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-700 dark:hover:text-slate-300">
                        <x-icon name="close" />
                    </button>
                </div>

                <form wire:submit.prevent="save" class="space-y-3">
                    {{-- نام و کد --}}
                    <div class="grid gap-3 mb-1 sm:grid-cols-2">
                        <div>
                            <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">نام زبان <span class="text-red-500">*</span></label>
                            <input type="text" wire:model.blur="name" required class="w-full px-3 py-2 text-sm transition border rounded-xl border-slate-200 bg-slate-50 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white" placeholder="مثال: انگلیسی" />
                            @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">کد زبان</label>
                            <input type="text" wire:model.blur="code" dir="ltr" class="w-full px-3 py-2 text-sm transition border rounded-xl border-slate-200 bg-slate-50 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white" placeholder="en" />
                            @error('code') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- سطح تسلط --}}
                    <div>
                        <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">سطح تسلط <span class="text-red-500">*</span></label>
                        <select wire:model.live="level" class="w-full px-3 py-2 text-sm transition border rounded-xl border-slate-200 bg-slate-50 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white">
                            <option value="beginner">مقدماتی</option>
                            <option value="intermediate">متوسط</option>
                            <option value="advanced">پیشرفته</option>
                            <option value="fluent">روان</option>
                            <option value="native">زبان مادری</option>
                        </select>
                        @error('level') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- درصد تسلط --}}
                    <div>
                        <label class="flex items-center justify-between mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">
                            <span>میزان تسلط <span class="text-red-500">*</span></span>
                            <span class="font-bold text-primary-600 dark:text-primary-400">{{ $proficiency }}٪</span>
                        </label>
                        <input type="range" wire:model.live="proficiency" min="0" max="100" step="5"
                               class="w-full h-2 accent-primary-500" />
                        @error('proficiency') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- توضیحات --}}
                    <div>
                        <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">توضیحات</label>
                        <input type="text" wire:model.blur="description" class="w-full px-3 py-2 text-sm transition border rounded-xl border-slate-200 bg-slate-50 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white" placeholder="مثال: مدرک آیلتس ۷.۵ (اختیاری)" />
                        @error('description') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- چک‌باکس‌ها --}}
                    <div class="flex flex-wrap gap-4 p-3 rounded-xl bg-slate-50 dark:bg-slate-700/50">
                        <label class="flex items-center gap-2 text-xs cursor-pointer text-slate-700 dark:text-slate-300">
                            <input type="checkbox" wire:model="is_active" class="rounded border-slate-300 text-primary-500 focus:ring-primary-500/20" />
                            فعال
                        </label>
                        <label class="flex items-center gap-2 text-xs cursor-pointer text-slate-700 dark:text-slate-300">
                            <input type="checkbox" wire:model="show_in_resume" class="rounded border-slate-300 text-primary-500 focus:ring-primary-500/20" />
                            نمایش در رزومه
                        </label>
                    </div>

                    {{-- دکمه‌ها --}}
                    <div class="flex gap-3 pt-3 mt-2">
                        <button type="submit" wire:loading.attr="disabled"
                                class="flex-1 rounded-xl bg-primary-500 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-primary-500/25 transition-all hover:shadow-lg hover:shadow-primary-500/35 disabled:opacity-50">
                            {{ $editing ? 'ذخیره تغییرات' : 'ذخیره زبان' }}
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
                            <h3 class="text-base font-bold">حذف زبان</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">این عملیات قابل بازگشت نیست</p>
                        </div>
                    </div>
                    <button wire:click="$set('confirmingDelete', false)" class="p-2 transition rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-700 dark:hover:text-slate-300">
                        <x-icon name="close" />
                    </button>
                </div>
                <p class="text-sm text-slate-600 dark:text-slate-300">آیا از حذف این زبان مطمئن هستید؟</p>
                <div class="flex gap-3 mt-5">
                    <button wire:click="deleteLanguage" wire:loading.attr="disabled"
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
