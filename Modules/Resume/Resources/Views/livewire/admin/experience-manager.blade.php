<div>
    {{-- هدر صفحه --}}
    <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
        <div>
            <h2 class="text-2xl font-bold sm:text-3xl">مدیریت تجربیات کاری</h2>
            <p class="mt-1 text-slate-500 dark:text-slate-400">{{ $experiences->total() }} سابقه کاری ثبت‌شده</p>
        </div>
        @can('create', \Modules\Resume\Models\Experience::class)
            <button wire:click="openCreateModal"
                    class="group flex items-center gap-2 rounded-xl bg-primary-500 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary-500/25 transition-all duration-200 hover:shadow-xl hover:shadow-primary-500/35 hover:-translate-y-0.5 active:translate-y-0">
                <span class="flex items-center justify-center w-5 h-5 transition duration-300 rounded-md bg-white/20 group-hover:rotate-90">
                    <x-icon name="plus" size="3" />
                </span>
                اضافه کردن سابقه کاری
            </button>
        @endcan
    </div>

    {{-- جستجو --}}
    <div class="mb-6 chart-card">
        <div class="relative">
            
            <input type="text" wire:model.live.debounce.400ms="search" placeholder="جستجوی شرکت یا عنوان شغلی..."
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

    {{-- گرید تجربیات (الگوی Roles) --}}
    <div class="grid gap-6 sm:grid-cols-2 animate-slide-up" wire:loading.class="opacity-60 pointer-events-none" wire:target="save, deleteExperience, toggleActive, toggleResume, move">
        @forelse($experiences as $experience)
            <section class="flex flex-col experience-card chart-card">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-3">
                        <span class="flex items-center justify-center h-9 w-9 rounded-xl bg-primary-100 text-primary-600 dark:bg-primary-900/40 dark:text-primary-400">
                            <x-icon name="users" size="4" />
                        </span>
                        <div>
                            <h3 class="text-base font-bold">{{ $experience->position }}</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                {{ $experience->company }} · {{ $experience->employment_type_label }}
                            </p>
                        </div>
                    </div>
                    @if($experience->is_current)
                        <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                            شغل فعلی
                        </span>
                    @endif
                </div>

                {{-- بازه زمانی و موقعیت --}}
                <div class="mb-3 text-sm">
                    <p class="text-slate-500 dark:text-slate-400" dir="ltr">
                        {{ $experience->start_date->format('Y/m/d') }}
                        {{ $experience->is_current ? '— اکنون' : ' تا ' . ($experience->end_date?->format('Y/m/d') ?? '—') }}
                    </p>
                    @if($experience->location)
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">موقعیت: {{ $experience->location }}</p>
                    @endif
                </div>

                {{-- توضیحات --}}
                @if($experience->description)
                    <p class="mb-3 text-sm text-slate-500 dark:text-slate-400">{{ \Illuminate\Support\Str::limit($experience->description, 100) }}</p>
                @endif

                {{-- تکنولوژی‌ها --}}
                @if($experience->technologies)
                    <div class="flex flex-wrap gap-1.5 mb-3">
                        @foreach(array_slice($experience->technologies, 0, 5) as $tech)
                            <span class="px-2 py-1 text-xs rounded-lg bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300">{{ $tech }}</span>
                        @endforeach
                    </div>
                @endif

                {{-- وضعیت‌ها --}}
                <div class="flex flex-wrap gap-2 mb-3">
                    <span class="px-2 py-1 text-xs rounded-lg {{ $experience->is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400' }}">
                        {{ $experience->is_active ? 'فعال' : 'غیرفعال' }}
                    </span>
                    <span class="px-2 py-1 text-xs rounded-lg {{ $experience->show_in_resume ? 'bg-primary-100 text-primary-700 dark:bg-primary-900/40 dark:text-primary-300' : 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400' }}">
                        {{ $experience->show_in_resume ? 'نمایش در رزومه' : 'خارج از رزومه' }}
                    </span>
                </div>

                {{-- دکمه‌های عملیات --}}
                <div class="flex flex-wrap gap-2 pt-3 mt-auto border-t border-slate-100 dark:border-slate-700">
                    @can('update', $experience)
                        <button wire:click="toggleActive({{ $experience->id }})"
                                class="flex-1 px-3 py-2 text-xs font-medium text-center transition rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-slate-700 dark:text-slate-300 dark:hover:bg-slate-600">
                            {{ $experience->is_active ? 'غیرفعال' : 'فعال' }}
                        </button>
                        <button wire:click="toggleResume({{ $experience->id }})"
                                class="flex-1 px-3 py-2 text-xs font-medium text-center transition rounded-lg bg-slate-100 text-slate-600 hover:bg-primary-100 hover:text-primary-600 dark:bg-slate-700 dark:text-slate-300 dark:hover:bg-primary-900/40 dark:hover:text-primary-300">
                            {{ $experience->show_in_resume ? 'حذف از رزومه' : 'نمایش در رزومه' }}
                        </button>
                        <button wire:click="openEditModal({{ $experience->id }})"
                                class="px-3 py-2 text-xs font-medium transition rounded-lg bg-slate-100 text-slate-600 hover:bg-primary-100 hover:text-primary-600 dark:bg-slate-700 dark:text-slate-300 dark:hover:bg-primary-900/40 dark:hover:text-primary-300">
                            ویرایش
                        </button>
                    @endcan
                    @can('delete', $experience)
                        <button wire:click="confirmDelete({{ $experience->id }})"
                                class="px-3 py-2 text-xs font-medium transition rounded-lg bg-slate-100 text-rose-500 hover:bg-rose-100 dark:bg-slate-700 dark:text-rose-400 dark:hover:bg-rose-900/30">
                            حذف
                        </button>
                    @endcan
                </div>
            </section>
        @empty
            <div class="py-16 text-center col-span-full chart-card">
                <span class="flex mx-auto mb-4 w-fit text-slate-300 dark:text-slate-600">
                    <x-icon name="users" size="12" />
                </span>
                <p class="font-medium text-slate-600 dark:text-slate-300">{{ $search ? 'سابقه‌ای یافت نشد' : 'هنوز سابقه کاری ثبت نشده است' }}</p>
                <p class="mt-1 text-sm text-slate-400 dark:text-slate-500">{{ $search ? 'عبارت دیگری را جستجو کنید' : 'اولین سابقه کاری خود را اضافه کنید' }}</p>
            </div>
        @endforelse
    </div>

    {{-- صفحه‌بندی --}}
    @if($experiences->hasPages())
        <div class="mt-6">
            {{ $experiences->links() }}
        </div>
    @endif

    {{-- مودال افزودن/ویرایش --}}
    @if($modalOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
            <div class="w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto rounded-2xl border border-slate-200/50 bg-white p-4 shadow-2xl animate-slide-up dark:border-slate-700/50 dark:bg-surface-800 sm:p-5">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-3">
                        <span class="flex items-center justify-center h-9 w-9 rounded-xl bg-primary-100 text-primary-600 dark:bg-primary-900/40 dark:text-primary-400">
                            <x-icon name="users" size="4" />
                        </span>
                        <div>
                            <h3 class="text-base font-bold">{{ $editing ? 'ویرایش سابقه کاری' : 'اضافه کردن سابقه کاری' }}</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $editing ? 'اطلاعات را به‌روزرسانی کنید' : 'اطلاعات سابقه کاری را وارد کنید' }}</p>
                        </div>
                    </div>
                    <button wire:click="$set('modalOpen', false)" class="p-2 transition rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-700 dark:hover:text-slate-300">
                        <x-icon name="close" />
                    </button>
                </div>

                <form wire:submit.prevent="save" class="space-y-3">
                    {{-- شرکت و عنوان شغلی --}}
                    <div class="grid gap-3 mb-1 sm:grid-cols-2">
                        <div>
                            <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">شرکت <span class="text-red-500">*</span></label>
                            <input type="text" wire:model.blur="company" required class="w-full px-3 py-2 text-sm transition border rounded-xl border-slate-200 bg-slate-50 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white" placeholder="مثال: شرکت آمازون" />
                            @error('company') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">عنوان شغلی <span class="text-red-500">*</span></label>
                            <input type="text" wire:model.blur="position" required class="w-full px-3 py-2 text-sm transition border rounded-xl border-slate-200 bg-slate-50 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white" placeholder="مثال: توسعه‌دهنده ارشد" />
                            @error('position') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- نوع همکاری و موقعیت --}}
                    <div class="grid gap-3 mb-1 sm:grid-cols-2">
                        <div>
                            <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">نوع همکاری <span class="text-red-500">*</span></label>
                            <select wire:model.live="employment_type" class="w-full px-3 py-2 text-sm transition border rounded-xl border-slate-200 bg-slate-50 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white">
                                <option value="full-time">تمام‌وقت</option>
                                <option value="part-time">پاره‌وقت</option>
                                <option value="freelance">فریلنسری</option>
                                <option value="remote">دورکار</option>
                                <option value="internship">کارآموزی</option>
                            </select>
                            @error('employment_type') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">موقعیت مکانی</label>
                            <input type="text" wire:model.blur="location" class="w-full px-3 py-2 text-sm transition border rounded-xl border-slate-200 bg-slate-50 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white" placeholder="مثال: تهران" />
                            @error('location') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
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
                            شغل فعلی
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
                        <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">شرح وظایف</label>
                        <textarea wire:model.blur="description" rows="3" class="w-full px-3 py-2 text-sm transition border resize-none rounded-xl border-slate-200 bg-slate-50 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white" placeholder="شرح وظایف و دستاوردها..."></textarea>
                        @error('description') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- تکنولوژی‌ها و لینک شرکت --}}
                    <div class="grid gap-3 mb-1 sm:grid-cols-2">
                        <div>
                            <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">تکنولوژی‌ها (با کاما جدا کنید)</label>
                            <input type="text" wire:model.blur="technologies_input" dir="ltr" class="w-full px-3 py-2 text-sm transition border rounded-xl border-slate-200 bg-slate-50 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white" placeholder="Laravel, Vue.js, Docker" />
                        </div>
                        <div>
                            <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">وب‌سایت شرکت</label>
                            <input type="url" wire:model.blur="company_url" dir="ltr" class="w-full px-3 py-2 text-sm transition border rounded-xl border-slate-200 bg-slate-50 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white" placeholder="https://company.com" />
                            @error('company_url') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- دکمه‌ها --}}
                    <div class="flex gap-3 pt-3 mt-2">
                        <button type="submit" wire:loading.attr="disabled"
                                class="flex-1 rounded-xl bg-primary-500 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-primary-500/25 transition-all hover:shadow-lg hover:shadow-primary-500/35 disabled:opacity-50">
                            {{ $editing ? 'ذخیره تغییرات' : 'ذخیره سابقه' }}
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
                            <h3 class="text-base font-bold">حذف سابقه کاری</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">این عملیات قابل بازگشت نیست</p>
                        </div>
                    </div>
                    <button wire:click="$set('confirmingDelete', false)" class="p-2 transition rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-700 dark:hover:text-slate-300">
                        <x-icon name="close" />
                    </button>
                </div>
                <p class="text-sm text-slate-600 dark:text-slate-300">آیا از حذف این سابقه کاری مطمئن هستید؟</p>
                <div class="flex gap-3 mt-5">
                    <button wire:click="deleteExperience" wire:loading.attr="disabled"
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
