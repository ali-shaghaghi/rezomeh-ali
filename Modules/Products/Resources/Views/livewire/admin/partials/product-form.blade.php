<div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm" wire:key="product-form-modal"
     x-data="{ }"
     @keydown.escape.window="if (! $wire.pickerOpen) $wire.set('showForm', false)"
     @click.self="$wire.set('showForm', false)">
    <div class="max-h-[90vh] w-full max-w-3xl animate-slide-up overflow-y-auto rounded-2xl bg-white p-6 shadow-2xl dark:bg-surface-800">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-lg font-bold">{{ $editing ? 'ویرایش محصول' : 'افزودن محصول جدید' }}</h3>
            <button wire:click="$set('showForm', false)" class="p-2 transition rounded-xl text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700">
                <x-icon name="close" />
            </button>
        </div>

        <div class="space-y-4">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-bold text-slate-700 dark:text-slate-200">نام محصول *</label>
                    <input type="text" wire:model.live="name" placeholder="مثال: قالب فروشگاهی"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm transition focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-surface-800">
                    @error('name') <p class="mt-1 text-xs font-bold text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-bold text-slate-700 dark:text-slate-200">slug (اختیاری)</label>
                    <input type="text" wire:model="slug" placeholder="auto-generated" dir="ltr"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm transition focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-surface-800">
                    @error('slug') <p class="mt-1 text-xs font-bold text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-bold text-slate-700 dark:text-slate-200">توضیح کوتاه</label>
                <input type="text" wire:model="short_description" placeholder="یک خط توضیح محصول"
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm transition focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-surface-800">
                @error('short_description') <p class="mt-1 text-xs font-bold text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-bold text-slate-700 dark:text-slate-200">توضیحات کامل</label>
                <x-rich-editor target="description" upload-prop="inlineUpload" event="inline-media-uploaded" lang="fa" :gallery-assets="$galleryAssets" placeholder="توضیحات محصول… می‌توانید تیتر، فهرست، لینک و تصویر اضافه کنید." />
                @error('description') <p class="mt-1 text-xs font-bold text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label class="mb-1.5 block text-sm font-bold text-slate-700 dark:text-slate-200">قیمت (تومان) *</label>
                    <input type="number" wire:model="price" placeholder="1000000" dir="ltr"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm transition focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-surface-800">
                    @error('price') <p class="mt-1 text-xs font-bold text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-bold text-slate-700 dark:text-slate-200">قیمت تخفیف</label>
                    <input type="number" wire:model="sale_price" placeholder="اختیاری" dir="ltr"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm transition focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-surface-800">
                    @error('sale_price') <p class="mt-1 text-xs font-bold text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-bold text-slate-700 dark:text-slate-200">نسخه</label>
                    <input type="text" wire:model="version" placeholder="1.0.0" dir="ltr"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm transition focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-surface-800">
                    @error('version') <p class="mt-1 text-xs font-bold text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-bold text-slate-700 dark:text-slate-200">نوع محصول</label>
                    <select wire:model="type_field" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm transition focus:border-primary-400 focus:outline-none dark:border-slate-700 dark:bg-surface-800">
                        @foreach ($types as $type)
                            <option value="{{ $type->value }}">{{ $type->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-bold text-slate-700 dark:text-slate-200">دسته‌بندی</label>
                    <select wire:model="category_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm transition focus:border-primary-400 focus:outline-none dark:border-slate-700 dark:bg-surface-800">
                        <option value="">بدون دسته</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id') <p class="mt-1 text-xs font-bold text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                </div>
            </div> 

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-bold text-slate-700 dark:text-slate-200">مسیر فایل دانلود</label>
                    <input type="text" wire:model="file_path" placeholder="products/file.zip" dir="ltr"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm transition focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-surface-800">
                    @error('file_path') <p class="mt-1 text-xs font-bold text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-bold text-slate-700 dark:text-slate-200">آدرس دمو</label>
                    <input type="text" wire:model="demo_url" placeholder="https://..." dir="ltr"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm transition focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-surface-800">
                    @error('demo_url') <p class="mt-1 text-xs font-bold text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex flex-wrap gap-6 pt-2">
                <label class="flex cursor-pointer items-center gap-2.5">
                    <input type="checkbox" wire:model="is_published" class="w-5 h-5 rounded-lg border-slate-300 text-primary-600 focus:ring-primary-500/20">
                    <span class="text-sm font-bold text-slate-700 dark:text-slate-200">منتشر شود</span>
                </label>
                <label class="flex cursor-pointer items-center gap-2.5">
                    <input type="checkbox" wire:model="show_in_resume" class="w-5 h-5 rounded-lg border-slate-300 text-primary-600 focus:ring-primary-500/20">
                    <span class="text-sm font-bold text-slate-700 dark:text-slate-200">نمایش در صفحه رزومه</span>
                </label>
            </div>

            {{-- ===================== تصاویر محصول ===================== --}}
            <div class="p-4 border rounded-2xl border-slate-200 dark:border-slate-700">
                <h4 class="flex items-center gap-2 mb-4 text-sm font-bold text-slate-700 dark:text-slate-200">
                    <x-icon name="eye" size="4" />
                    تصاویر محصول
                </h4>

                <div class="grid gap-4 sm:grid-cols-2">
                    {{-- عکس شاخص — فقط از گالری --}}
                    <div>
                        <label class="mb-1.5 block text-xs font-bold text-slate-600 dark:text-slate-300">عکس شاخص (کاور اصلی)</label>
                        <button type="button" wire:click="openPicker('cover')"
                                class="w-full rounded-xl border border-dashed border-primary-300 bg-primary-50/50 px-3 py-2.5 text-xs font-bold text-primary-600 transition hover:bg-primary-50 dark:border-primary-700 dark:bg-primary-950/20 dark:text-primary-400">
                            {{ $coverSelected !== '' ? 'تغییر انتخاب از گالری' : 'انتخاب از گالری' }}
                        </button>

                        @if ($coverSelected !== '')
                            <div class="flex items-center gap-2 mt-2">
                                <img src="{{ asset('storage/'.$coverSelected) }}" alt="پیش‌نمایش" class="object-cover w-20 rounded-lg h-14">
                                <span class="text-[10px] text-slate-400">انتخاب‌شده از گالری</span>
                                <button type="button" wire:click="clearCoverSelected" class="text-[10px] font-bold text-rose-500 hover:text-rose-600">حذف انتخاب</button>
                            </div>
                        @elseif ($editing && $editingProduct?->cover_image)
                            <div class="flex items-center gap-2 mt-2">
                                <img src="{{ $editingProduct->cover_url }}" alt="کاور فعلی" class="object-cover w-20 rounded-lg h-14">
                                <span class="text-[10px] text-slate-400">کاور فعلی</span>
                            </div>
                        @endif
                        @error('coverSelected') <p class="mt-1 text-xs font-bold text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                    </div>

                    {{-- گالری — انتخاب چندتایی از گالری --}}
                    <div>
                        <label class="mb-1.5 block text-xs font-bold text-slate-600 dark:text-slate-300">عکس‌های گالری (چندتایی)</label>
                        <button type="button" wire:click="openPicker('gallery')"
                                class="w-full rounded-xl border border-dashed border-primary-300 bg-primary-50/50 px-3 py-2.5 text-xs font-bold text-primary-600 transition hover:bg-primary-50 dark:border-primary-700 dark:bg-primary-950/20 dark:text-primary-400">
                            انتخاب از گالری ({{ count($gallerySelected) }} انتخاب)
                        </button>

                        @if ($gallerySelected !== [])
                            <div class="flex flex-wrap gap-2 mt-2">
                                @foreach ($gallerySelected as $path)
                                    <div class="relative" wire:key="gallery-sel-{{ md5($path) }}">
                                        <img src="{{ asset('storage/'.$path) }}" alt="" class="object-cover w-20 rounded-lg h-14">
                                        <button type="button" wire:click="removeSelectedGalleryImage({{ \Illuminate\Support\Js::from($path) }})"
                                                class="absolute -left-1.5 -top-1.5 flex h-5 w-5 items-center justify-center rounded-full bg-rose-500 text-white shadow-md transition hover:bg-rose-600"
                                                aria-label="حذف انتخاب">
                                            <x-icon name="close" size="2.5" />
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                        @error('gallerySelected.*') <p class="mt-1 text-xs font-bold text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- گالری ذخیره‌شده (در حالت ویرایش) --}}
                @if ($editing && $existingImages !== [])
                    <div class="mt-4">
                        <p class="mb-2 text-xs font-bold text-slate-600 dark:text-slate-300">عکس‌های ذخیره‌شده (به‌ترتیب نمایش در صفحه محصول):</p>
                        <div class="flex flex-wrap gap-3">
                            @foreach ($existingImages as $imageId => $path)
                                <div class="relative" wire:key="gallery-existing-{{ $imageId }}">
                                    <img src="{{ asset('storage/'.$path) }}" alt="" class="object-cover w-24 h-16 border rounded-lg border-slate-200 dark:border-slate-700">
                                    <button type="button" wire:click="removeExistingImage({{ $imageId }})" wire:loading.attr="disabled"
                                            wire:target="removeExistingImage({{ $imageId }})"
                                            class="absolute -left-1.5 -top-1.5 flex h-5 w-5 items-center justify-center rounded-full bg-rose-500 text-white shadow-md transition hover:bg-rose-600"
                                            aria-label="حذف عکس">
                                        <x-icon name="close" size="2.5" />
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <p class="mt-3 text-[10px] text-slate-400">انتخاب تصاویر فقط از گالری انجام می‌شود — برای افزودن تصویر جدید از بخش گالری اقدام کنید. عکس‌های گالری در صفحه محصول به‌ترتیب نمایش داده می‌شوند (حداکثر ۱۰ عکس).</p>
            </div>

            {{-- ===================== نسخه انگلیسی (دو‌زبانه) ===================== --}}
            <div class="p-4 border rounded-2xl border-sky-200 bg-sky-50/50 dark:border-sky-800 dark:bg-sky-950/20">
                <h4 class="flex items-center gap-2 mb-4 text-sm font-bold text-sky-700 dark:text-sky-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 21l5.25-11.25L21 21m-9-3h7.5M3 5.621a48.474 48.474 0 016-.371m0 0c1.12 0 2.233.038 3.334.114M9 5.25V3m3.334 2.364C11.176 10.658 7.69 15.08 3 17.502m9.334-12.138c.896.061 1.785.147 2.666.257m-4.589 8.495a18.023 18.023 0 01-3.827-5.802"/></svg>
                    نسخه انگلیسی (اختیاری — برای کاربران انگلیسی‌زبان)
                </h4>

                <div class="space-y-3" dir="ltr">
                    <div>
                        <label class="mb-1.5 block text-xs font-bold text-slate-600 dark:text-slate-300">Product name (English)</label>
                        <input type="text" wire:model="name_en" placeholder="e.g. Store Template" dir="ltr"
                               class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm transition focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-surface-800">
                        @error('name_en') <p class="mt-1 text-xs font-bold text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-bold text-slate-600 dark:text-slate-300">Short description (English)</label>
                        <input type="text" wire:model="short_description_en" placeholder="One-line product summary" dir="ltr"
                               class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm transition focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-surface-800">
                        @error('short_description_en') <p class="mt-1 text-xs font-bold text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-bold text-slate-600 dark:text-slate-300">Full description (English)</label>
                        <x-rich-editor target="description_en" upload-prop="inlineUploadEn" event="inline-media-uploaded-en" lang="en" :gallery-assets="$galleryAssets" placeholder="Write your content… you can add headings, lists, links and images." />
                        @error('description_en') <p class="mt-1 text-xs font-bold text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                    </div>
                    <p class="text-[10px] text-slate-400" dir="rtl">اگر خالی بماند، محتوای فارسی با همان تصاویر به کاربر انگلیسی نمایش داده می‌شود.</p>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-5 mt-6 border-t border-slate-100 dark:border-slate-800">
            <button wire:click="$set('showForm', false)" class="rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-700">
                انصراف
            </button>
            <button wire:click="saveProduct" wire:loading.attr="disabled" wire:target="saveProduct"
                    class="rounded-xl bg-primary-500 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary-500/25 transition hover:bg-primary-600">
                <span wire:loading.remove wire:target="saveProduct">{{ $editing ? 'ذخیره تغییرات' : 'ایجاد محصول' }}</span>
                <span wire:loading wire:target="saveProduct">در حال ذخیره...</span>
            </button>
        </div>
    </div>

    {{-- ===================== انتخاب‌گر گالری ===================== --}}
    @if ($pickerOpen)
        <div class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm"
             @keydown.escape.window="$wire.set('pickerOpen', false)">
            <div class="w-full max-w-3xl animate-slide-up rounded-2xl bg-white p-5 shadow-2xl dark:bg-surface-800">
                <div class="mb-4 flex items-center justify-between">
                    <h4 class="text-sm font-bold">
                        انتخاب تصویر از گالری
                        <span class="text-xs font-normal text-slate-400">({{ $pickerMode === 'cover' ? 'عکس شاخص — یک انتخاب' : 'چند انتخابی — حداکثر ۱۰ عکس' }})</span>
                    </h4>
                    <button wire:click="closePicker" class="p-2 transition rounded-xl text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700">
                        <x-icon name="close" />
                    </button>
                </div>

                @if ($galleryAssets->isEmpty())
                    <p class="py-10 text-center text-sm text-slate-400">هنوز تصویری در گالری منتشر نشده است — ابتدا از بخش گالری تصویر آپلود کنید.</p>
                @else
                    <div class="grid grid-cols-3 gap-3 overflow-y-auto sm:grid-cols-4 max-h-[55vh]">
                        @foreach ($galleryAssets as $asset)
                            @php($assetPath = $asset->uploader_path)
                            @php($isPicked = $pickerMode === 'cover' ? $coverSelected === $assetPath : in_array($assetPath, $gallerySelected, true))
                            <button type="button" wire:click="pickAsset({{ \Illuminate\Support\Js::from($assetPath) }})"
                                    wire:key="pick-asset-{{ $asset->id }}"
                                    class="relative overflow-hidden transition rounded-xl border-2 {{ $isPicked ? 'border-primary-500 ring-2 ring-primary-500/30' : 'border-transparent hover:border-primary-300' }}">
                                <img src="{{ $asset->srcAttribute() }}" alt="{{ $asset->original_name }}" class="object-cover w-full h-20" loading="lazy">
                                @if ($isPicked)
                                    <span class="absolute inset-0 flex items-center justify-center bg-primary-500/30">
                                        <span class="flex items-center justify-center w-6 h-6 rounded-full bg-primary-500 text-white">
                                            <x-icon name="check" size="3.5" />
                                        </span>
                                    </span>
                                @endif
                            </button>
                        @endforeach
                    </div>
                @endif

                <div class="flex justify-end gap-3 mt-4">
                    @if ($pickerMode === 'gallery')
                        <button wire:click="closePicker" class="rounded-xl bg-primary-500 px-4 py-2 text-xs font-semibold text-white">
                            تمام شد ({{ count($gallerySelected) }} انتخاب)
                        </button>
                    @else
                        <button wire:click="closePicker" class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-medium text-slate-600 dark:border-slate-700 dark:text-slate-300">
                            بستن
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
