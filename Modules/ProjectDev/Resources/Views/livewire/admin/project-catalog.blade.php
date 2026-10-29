<div class="space-y-6">
    {{-- نوتیفیکیشن --}}
    @if ($successToast)
        <div class="flex items-center gap-2 p-4 text-sm font-bold rounded-2xl glass-card !border-emerald-300/60 dark:!border-emerald-700/40 !text-emerald-600 dark:!text-emerald-400 animate-slide-up"
             x-data="{ show: true }" x-init="setTimeout(() => show = false, 3500)" x-show="show" x-transition.opacity.duration.500ms>
            <x-icon name="check" size="4" />
            {{ $successMessage }}
        </div>
    @endif

    {{-- هدر --}}
    <div class="flex flex-wrap items-center justify-between gap-4 mb-2">
        <div>
            <h2 class="text-2xl font-bold">قیمت‌گذاری پروژه‌ها</h2>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">انواع پروژه، قیمت پایه و قیمت هر ویژگی — این مقادیر در فرم «ثبت درخواست پروژه» کاربر به‌صورت زنده محاسبه می‌شوند.</p>
        </div>
        <button type="button" wire:click="openTypeCreate" class="btn-primary !px-5 !py-2.5 !text-xs">
            <x-icon name="plus" size="4" />
            نوع پروژه جدید
        </button>
    </div>

    {{-- ===================== انواع پروژه ===================== --}}
    <div class="grid gap-4 md:grid-cols-2">
        @forelse ($types as $type)
            <div class="chart-card" wire:key="type-{{ $type->id }}">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h3 class="font-bold text-lg flex items-center gap-2">
                            {{ $type->name }}
                            @if (! $type->is_active)
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300">غیرفعال</span>
                            @endif
                        </h3>
                        @if ($type->description)
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400 leading-5">{{ \Illuminate\Support\Str::limit($type->description, 140) }}</p>
                        @endif
                        <p class="mt-2 text-sm font-black text-primary-600 dark:text-primary-400">قیمت پایه: {{ number_format((float) $type->base_price) }} تومان</p>
                    </div>
                    <div class="flex gap-1.5 shrink-0">
                        <button type="button" wire:click="openTypeEdit({{ $type->id }})" class="icon-btn !p-2" title="ویرایش">
                            <x-icon name="edit" size="4" />
                        </button>
                        <button type="button" wire:click="confirmTypeDelete({{ $type->id }})" class="icon-btn !p-2 !text-rose-500" title="حذف">
                            <x-icon name="trash" size="4" />
                        </button>
                    </div>
                </div>

                {{-- ویژگی‌های این نوع --}}
                <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-700/50">
                    <p class="mb-2 text-[11px] font-bold text-slate-400">ویژگی‌ها و قیمت‌ها</p>
                    @if ($type->options->count())
                        <div class="flex flex-wrap gap-2">
                            @foreach ($type->options as $option)
                                <span class="flex items-center gap-1.5 px-2.5 py-1 text-xs rounded-lg bg-slate-100 dark:bg-slate-800 {{ $option->is_active ? '' : 'opacity-50 line-through' }}" wire:key="opt-{{ $option->id }}">
                                    {{ $option->name }}
                                    <b class="text-emerald-600 dark:text-emerald-400">{{ number_format((float) $option->price) }}</b>
                                    <button type="button" wire:click="openOptionEdit({{ $option->id }})" class="text-primary-500 hover:text-primary-600" title="ویرایش">
                                        <x-icon name="edit" size="3" />
                                    </button>
                                    <button type="button" wire:click="confirmOptionDelete({{ $option->id }})" class="text-rose-500 hover:text-rose-600" title="حذف">
                                        <x-icon name="trash" size="3" />
                                    </button>
                                </span>
                            @endforeach
                        </div>
                    @else
                        <p class="text-xs text-slate-400">ویژگی‌ای برای این نوع ثبت نشده است.</p>
                    @endif
                    <button type="button" wire:click="openOptionCreate" class="mt-3 text-xs font-bold text-primary-600 dark:text-primary-400 hover:underline">
                        <x-icon name="plus" size="3.5" />
                        افزودن ویژگی
                    </button>
                </div>
            </div>
        @empty
            <div class="chart-card md:col-span-2 text-center p-10">
                <p class="text-sm text-slate-500 dark:text-slate-400">هنوز نوع پروژه‌ای ثبت نشده است — با دکمه «نوع پروژه جدید» شروع کنید.</p>
            </div>
        @endforelse
    </div>

    {{-- ویژگی‌های عمومی (مشترک بین همه انواع) --}}
    <div class="chart-card">
        <div class="flex items-center justify-between mb-3">
            <h3 class="font-bold">ویژگی‌های عمومی (مشترک بین همه انواع)</h3>
            <button type="button" wire:click="openOptionCreate" class="text-xs font-bold text-primary-600 dark:text-primary-400 hover:underline">افزودن</button>
        </div>
        @if ($globalOptions->count())
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-xs text-slate-500 dark:text-slate-400 border-b border-slate-100 dark:border-slate-700/50">
                            <th class="py-2 text-right font-medium">ویژگی</th>
                            <th class="py-2 text-right font-medium">توضیح</th>
                            <th class="py-2 text-right font-medium">قیمت (تومان)</th>
                            <th class="py-2 text-right font-medium">عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($globalOptions as $option)
                            <tr class="border-b border-slate-50 dark:border-slate-800 {{ $option->is_active ? '' : 'opacity-50' }}" wire:key="gopt-{{ $option->id }}">
                                <td class="py-3">{{ $option->name }}</td>
                                <td class="py-3 text-xs text-slate-500">{{ \Illuminate\Support\Str::limit($option->description ?? '', 60) }}</td>
                                <td class="py-3 font-bold">{{ number_format((float) $option->price) }}</td>
                                <td class="py-3">
                                    <button type="button" wire:click="openOptionEdit({{ $option->id }})" class="icon-btn !p-1.5"><x-icon name="edit" size="3.5" /></button>
                                    <button type="button" wire:click="confirmOptionDelete({{ $option->id }})" class="icon-btn !p-1.5 !text-rose-500"><x-icon name="trash" size="3.5" /></button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-xs text-slate-400">ویژگی عمومی‌ای ثبت نشده است.</p>
        @endif
    </div>

    {{-- ===================== مودال نوع پروژه ===================== --}}
    @if ($typeModalOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
            <div class="w-full max-w-lg glass-card p-6 rounded-3xl animate-slide-up">
                <h3 class="mb-5 font-bold text-lg">{{ $typeEditing ? 'ویرایش نوع پروژه' : 'نوع پروژه جدید' }}</h3>
                <form wire:submit="saveType" class="space-y-4">
                    <div>
                        <label class="block mb-1.5 text-sm font-medium">نام نوع پروژه <span class="text-rose-500">*</span></label>
                        <input type="text" wire:model="typeForm.name" placeholder="مثلاً: طراحی سایت فروشگاهی (لاراول)"
                               class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 bg-white focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white" />
                        @error('typeForm.name') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block mb-1.5 text-sm font-medium">توضیحات</label>
                        <textarea wire:model="typeForm.description" rows="2" class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 bg-white focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white resize-y"></textarea>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block mb-1.5 text-sm font-medium">قیمت پایه (تومان) <span class="text-rose-500">*</span></label>
                            <input type="number" wire:model="typeForm.base_price" min="0" step="1000"
                                   class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 bg-white focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white" />
                            @error('typeForm.base_price') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                        </div>
                        <label class="flex items-center justify-between gap-3 p-3 rounded-xl cursor-pointer bg-slate-50 dark:bg-slate-700/50 self-end">
                            <span class="text-sm font-medium">فعال</span>
                            <input type="checkbox" wire:model="typeForm.is_active" class="w-10 h-5 rounded-full appearance-none cursor-pointer transition bg-slate-300 checked:bg-primary-500 relative before:content-[''] before:absolute before:top-0.5 before:right-0.5 before:w-4 before:h-4 before:rounded-full before:bg-white before:transition checked:before:right-5 dark:bg-slate-600" />
                        </label>
                    </div>
                    <div class="flex gap-3 pt-2">
                        <button type="submit" wire:loading.attr="disabled" wire:target="saveType" class="btn-primary !px-6 !py-2.5 !text-xs">ذخیره</button>
                        <button type="button" wire:click="$set('typeModalOpen', false)" class="btn-outline !px-6 !py-2.5 !text-xs">انصراف</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- ===================== مودال ویژگی ===================== --}}
    @if ($optionModalOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
            <div class="w-full max-w-lg glass-card p-6 rounded-3xl animate-slide-up">
                <h3 class="mb-5 font-bold text-lg">{{ $optionEditing ? 'ویرایش ویژگی' : 'ویژگی جدید' }}</h3>
                <form wire:submit="saveOption" class="space-y-4">
                    <div>
                        <label class="block mb-1.5 text-sm font-medium">نام ویژگی <span class="text-rose-500">*</span></label>
                        <input type="text" wire:model="optionForm.name" placeholder="مثلاً: طراحی درگاه پرداخت"
                               class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 bg-white focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white" />
                        @error('optionForm.name') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block mb-1.5 text-sm font-medium">قیمت (تومان) <span class="text-rose-500">*</span></label>
                            <input type="number" wire:model="optionForm.price" min="0" step="1000"
                                   class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 bg-white focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white" />
                            @error('optionForm.price') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block mb-1.5 text-sm font-medium">محدود به نوع پروژه</label>
                            <select wire:model="optionForm.project_type_id"
                                    class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 bg-white focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white">
                                <option value="">عمومی (همه انواع)</option>
                                @foreach ($types as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block mb-1.5 text-sm font-medium">توضیحات</label>
                        <input type="text" wire:model="optionForm.description" class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 bg-white focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white" />
                    </div>
                    <label class="flex items-center justify-between gap-3 p-3 rounded-xl cursor-pointer bg-slate-50 dark:bg-slate-700/50">
                        <span class="text-sm font-medium">فعال</span>
                        <input type="checkbox" wire:model="optionForm.is_active" class="w-10 h-5 rounded-full appearance-none cursor-pointer transition bg-slate-300 checked:bg-primary-500 relative before:content-[''] before:absolute before:top-0.5 before:right-0.5 before:w-4 before:h-4 before:rounded-full before:bg-white before:transition checked:before:right-5 dark:bg-slate-600" />
                    </label>
                    <div class="flex gap-3 pt-2">
                        <button type="submit" wire:loading.attr="disabled" wire:target="saveOption" class="btn-primary !px-6 !py-2.5 !text-xs">ذخیره</button>
                        <button type="button" wire:click="$set('optionModalOpen', false)" class="btn-outline !px-6 !py-2.5 !text-xs">انصراف</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- ===================== مودال‌های حذف ===================== --}}
    @if ($confirmingTypeDelete)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
            <div class="w-full max-w-sm glass-card p-6 rounded-3xl text-center animate-slide-up">
                <h3 class="font-bold text-lg">حذف نوع پروژه</h3>
                <p class="mt-2 mb-5 text-xs text-slate-500 dark:text-slate-400">ویژگی‌های این نوع هم حذف می‌شوند. مطمئن هستید؟</p>
                <div class="flex gap-3 justify-center">
                    <button type="button" wire:click="deleteType" class="btn-primary !bg-rose-500 !px-6 !py-2.5 !text-xs">حذف کن</button>
                    <button type="button" wire:click="$set('confirmingTypeDelete', false)" class="btn-outline !px-6 !py-2.5 !text-xs">انصراف</button>
                </div>
            </div>
        </div>
    @endif

    @if ($confirmingOptionDelete)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
            <div class="w-full max-w-sm glass-card p-6 rounded-3xl text-center animate-slide-up">
                <h3 class="font-bold text-lg">حذف ویژگی</h3>
                <p class="mt-2 mb-5 text-xs text-slate-500 dark:text-slate-400">مطمئن هستید؟</p>
                <div class="flex gap-3 justify-center">
                    <button type="button" wire:click="deleteOption" class="btn-primary !bg-rose-500 !px-6 !py-2.5 !text-xs">حذف کن</button>
                    <button type="button" wire:click="$set('confirmingOptionDelete', false)" class="btn-outline !px-6 !py-2.5 !text-xs">انصراف</button>
                </div>
            </div>
        </div>
    @endif
</div>
