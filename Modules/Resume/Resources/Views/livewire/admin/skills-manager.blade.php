<div>
    {{-- هدر صفحه --}}
    <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
        <div>
            <h2 class="text-2xl font-bold sm:text-3xl">مدیریت مهارت‌ها</h2>
            <p class="mt-1 text-slate-500 dark:text-slate-400">{{ $skills->total() }} مهارت ثبت‌شده</p>
        </div>
        @can('create', \Modules\Resume\Models\Skill::class)
            <button wire:click="openCreateModal"
                    class="group flex items-center gap-2 rounded-xl bg-primary-500 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary-500/25 transition-all duration-200 hover:shadow-xl hover:shadow-primary-500/35 hover:-translate-y-0.5 active:translate-y-0">
                <span class="flex items-center justify-center w-5 h-5 transition duration-300 rounded-md bg-white/20 group-hover:rotate-90">
                    <x-icon name="plus" size="3" />
                </span>
                اضافه کردن مهارت
            </button>
        @endcan
    </div>

    {{-- جستجو و فیلتر --}}
    <div class="mb-6 chart-card">
        <div class="flex flex-wrap gap-3">
            <div class="relative flex-1 min-w-[200px]">
                
                <input type="text" wire:model.live.debounce.400ms="search" placeholder="جستجوی مهارت..."
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pr-3 text-sm transition focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-surface-800" />
            </div>
            <select wire:model.live="categoryFilter"
                    class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm transition focus:border-primary-400 focus:outline-none dark:border-slate-700 dark:bg-surface-800">
                <option value="">همه دسته‌ها</option>
                <option value="frontend">فرانت‌اند</option>
                <option value="backend">بک‌اند</option>
                <option value="devops">دوآپس</option>
                <option value="database">دیتابیس</option>
                <option value="mobile">موبایل</option>
                <option value="soft-skills">مهارت نرم</option>
                <option value="other">سایر</option>
            </select>
            @if($search || $categoryFilter)
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

    {{-- گرید مهارت‌ها (الگوی Roles) — بدون wire:loading.remove تا صفحه فلیکر نزند --}}
    <div class="grid gap-6 sm:grid-cols-2 animate-slide-up" wire:loading.class="opacity-60 pointer-events-none" wire:target="save, deleteSkill, toggleActive, toggleResume, move">
        @forelse($skills as $skill)
            <section class="flex flex-col skill-card chart-card">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-3">
                        <span class="flex items-center justify-center h-9 w-9 rounded-xl bg-primary-100 text-primary-600 dark:bg-primary-900/40 dark:text-primary-400">
                            <x-icon name="star" size="4" />
                        </span>
                        <div>
                            <h3 class="text-base font-bold">{{ $skill->name }}</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400" dir="ltr">{{ $skill->slug }}</p>
                        </div>
                    </div>
                    <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300">
                        {{ $skill->category_label }}
                    </span>
                </div>

                {{-- نوار درصد تسلط --}}
                <div class="mb-3">
                    <div class="flex items-center justify-between mb-1 text-xs">
                        <span class="text-slate-500 dark:text-slate-400">میزان تسلط</span>
                        <span class="font-bold text-primary-600 dark:text-primary-400">{{ $skill->proficiency }}٪</span>
                    </div>
                    <div class="w-full h-2 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-700">
                        <div class="h-full transition-all duration-500 rounded-full bg-gradient-to-l from-primary-500 to-violet-600"
                             style="width: {{ $skill->proficiency }}%"></div>
                    </div>
                </div>

                {{-- وضعیت‌ها --}}
                <div class="flex flex-wrap gap-2 mb-3">
                    <span class="px-2 py-1 text-xs rounded-lg {{ $skill->is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400' }}">
                        {{ $skill->is_active ? 'فعال' : 'غیرفعال' }}
                    </span>
                    <span class="px-2 py-1 text-xs rounded-lg {{ $skill->show_in_resume ? 'bg-primary-100 text-primary-700 dark:bg-primary-900/40 dark:text-primary-300' : 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400' }}">
                        {{ $skill->show_in_resume ? 'نمایش در رزومه' : 'خارج از رزومه' }}
                    </span>
                    <span class="px-2 py-1 text-xs rounded-lg bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400" dir="ltr">
                        ترتیب: {{ $skill->order }}
                    </span>
                </div>

                {{-- دکمه‌های عملیات --}}
                <div class="flex flex-wrap gap-2 pt-3 mt-auto border-t border-slate-100 dark:border-slate-700">
                    @can('update', $skill)
                        <button wire:click="toggleActive({{ $skill->id }})"
                                class="flex-1 px-3 py-2 text-xs font-medium text-center transition rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-slate-700 dark:text-slate-300 dark:hover:bg-slate-600">
                            {{ $skill->is_active ? 'غیرفعال' : 'فعال' }}
                        </button>
                        <button wire:click="toggleResume({{ $skill->id }})"
                                class="flex-1 px-3 py-2 text-xs font-medium text-center transition rounded-lg bg-slate-100 text-slate-600 hover:bg-primary-100 hover:text-primary-600 dark:bg-slate-700 dark:text-slate-300 dark:hover:bg-primary-900/40 dark:hover:text-primary-300">
                            {{ $skill->show_in_resume ? 'حذف از رزومه' : 'نمایش در رزومه' }}
                        </button>
                        <button wire:click="openEditModal({{ $skill->id }})"
                                class="px-3 py-2 text-xs font-medium transition rounded-lg bg-slate-100 text-slate-600 hover:bg-primary-100 hover:text-primary-600 dark:bg-slate-700 dark:text-slate-300 dark:hover:bg-primary-900/40 dark:hover:text-primary-300">
                            ویرایش
                        </button>
                    @endcan
                    @can('delete', $skill)
                        <button wire:click="confirmDelete({{ $skill->id }})"
                                class="px-3 py-2 text-xs font-medium transition rounded-lg bg-slate-100 text-rose-500 hover:bg-rose-100 dark:bg-slate-700 dark:text-rose-400 dark:hover:bg-rose-900/30">
                            حذف
                        </button>
                    @endcan
                </div>
            </section>
        @empty
            <div class="py-16 text-center col-span-full chart-card">
                <span class="flex mx-auto mb-4 w-fit text-slate-300 dark:text-slate-600">
                    <x-icon name="star" size="12" />
                </span>
                <p class="font-medium text-slate-600 dark:text-slate-300">{{ $search ? 'مهارتی یافت نشد' : 'هنوز مهارتی ثبت نشده است' }}</p>
                <p class="mt-1 text-sm text-slate-400 dark:text-slate-500">{{ $search ? 'عبارت دیگری را جستجو کنید' : 'اولین مهارت خود را اضافه کنید' }}</p>
            </div>
        @endforelse
    </div>

    {{-- صفحه‌بندی --}}
    @if($skills->hasPages())
        <div class="mt-6">
            {{ $skills->links() }}
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
                            <x-icon name="star" size="4" />
                        </span>
                        <div>
                            <h3 class="text-base font-bold">{{ $editing ? 'ویرایش مهارت: ' . $name : 'اضافه کردن مهارت جدید' }}</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $editing ? 'اطلاعات مهارت را به‌روزرسانی کنید' : 'اطلاعات مهارت را وارد کنید' }}</p>
                        </div>
                    </div>
                    <button wire:click="$set('modalOpen', false)" class="p-2 transition rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-700 dark:hover:text-slate-300">
                        <x-icon name="close" />
                    </button>
                </div>

                <form wire:submit.prevent="save" class="space-y-3">
                    {{-- نام و اسلاگ --}}
                    <div class="grid gap-3 mb-1 sm:grid-cols-2">
                        <div>
                            <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">نام مهارت <span class="text-red-500">*</span></label>
                            <input type="text" wire:model.blur="name" required class="w-full px-3 py-2 text-sm transition border rounded-xl border-slate-200 bg-slate-50 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white" placeholder="مثال: Laravel" />
                            @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">Slug <span class="text-red-500">*</span></label>
                            <input type="text" wire:model.blur="slug" required dir="ltr" class="w-full px-3 py-2 text-sm transition border rounded-xl border-slate-200 bg-slate-50 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white" placeholder="laravel" />
                            @error('slug') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- دسته‌بندی و آیکون --}}
                    <div class="grid gap-3 mb-1 sm:grid-cols-2">
                        <div>
                            <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">دسته‌بندی <span class="text-red-500">*</span></label>
                            <select wire:model.live="category" class="w-full px-3 py-2 text-sm transition border rounded-xl border-slate-200 bg-slate-50 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white">
                                <option value="frontend">فرانت‌اند</option>
                                <option value="backend">بک‌اند</option>
                                <option value="devops">دوآپس</option>
                                <option value="database">دیتابیس</option>
                                <option value="mobile">موبایل</option>
                                <option value="soft-skills">مهارت نرم</option>
                                <option value="other">سایر</option>
                            </select>
                            @error('category') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">آیکون</label>
                            <input type="text" wire:model.blur="icon" dir="ltr" class="w-full px-3 py-2 text-sm transition border rounded-xl border-slate-200 bg-slate-50 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white" placeholder="laravel (اختیاری)" />
                            @error('icon') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
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
                            {{ $editing ? 'ذخیره تغییرات' : 'ذخیره مهارت' }}
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
                            <h3 class="text-base font-bold">حذف مهارت</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">این عملیات قابل بازگشت نیست</p>
                        </div>
                    </div>
                    <button wire:click="$set('confirmingDelete', false)" class="p-2 transition rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-700 dark:hover:text-slate-300">
                        <x-icon name="close" />
                    </button>
                </div>
                <p class="text-sm text-slate-600 dark:text-slate-300">آیا از حذف این مهارت مطمئن هستید؟ ارتباط آن با پروژه‌ها نیز حذف خواهد شد.</p>
                <div class="flex gap-3 mt-5">
                    <button wire:click="deleteSkill" wire:loading.attr="disabled"
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
