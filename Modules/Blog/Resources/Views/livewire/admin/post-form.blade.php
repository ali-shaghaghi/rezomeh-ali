<div>
    {{-- هدر --}}
    <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
        <div>
            <h2 class="text-2xl font-bold sm:text-3xl">{{ $creating ? 'افزودن مقاله جدید' : 'ویرایش: ' . $post->title }}</h2>
            <p class="mt-1 text-slate-500 dark:text-slate-400">{{ $creating ? 'مقاله جدید خود را بنویسید' : 'مقاله را به‌روزرسانی کنید' }}</p>
        </div>
        <a href="{{ route('admin.blog.index') }}"
           class="px-4 py-2 text-sm font-medium transition border rounded-xl border-slate-200 text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-400 dark:hover:bg-surface-800">
            بازگشت به لیست
        </a>
    </div>

    <form wire:submit.prevent="save" enctype="multipart/form-data" class="space-y-6">
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            {{-- ستون اصلی --}}
            <div class="space-y-6 lg:col-span-2">
                {{-- عنوان و اسلاگ --}}
                <div class="p-6 chart-card">
                    <div class="space-y-4">
                        <div>
                            <label class="block mb-1 text-sm font-medium text-slate-700 dark:text-slate-300">عنوان <span class="text-red-500">*</span></label>
                            <input type="text" wire:model.blur="title" class="w-full px-4 py-2 border rounded-lg border-slate-300 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:border-slate-600 dark:bg-surface-800 dark:text-white" placeholder="عنوان مقاله" />
                            @error('title') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block mb-1 text-sm font-medium text-slate-700 dark:text-slate-300">Slug <span class="text-red-500">*</span></label>
                            <input type="text" wire:model.blur="slug" dir="ltr" class="w-full px-4 py-2 border rounded-lg border-slate-300 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:border-slate-600 dark:bg-surface-800 dark:text-white" placeholder="post-slug" />
                            @error('slug') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block mb-1 text-sm font-medium text-slate-700 dark:text-slate-300">خلاصه</label>
                            <input type="text" wire:model.blur="excerpt" class="w-full px-4 py-2 border rounded-lg border-slate-300 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:border-slate-600 dark:bg-surface-800 dark:text-white" placeholder="خلاصه یک‌خطی مقاله (برای کارت‌ها و SEO)" />
                            @error('excerpt') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                            <div>
                                <label class="block mb-1 text-sm font-medium text-slate-700 dark:text-slate-300">محتوا <span class="text-red-500">*</span></label>
                                <x-rich-editor target="content" upload-prop="inlineUpload" event="inline-media-uploaded" lang="fa" :gallery-assets="$galleryAssets" placeholder="متن مقاله… می‌توانید تیتر، فهرست، لینک و تصویر اضافه کنید." />
                                @error('content') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                    </div>
                </div>

                {{-- نسخه انگلیسی (اختیاری) — برای سئو و کاربران خارجی --}}
            
    @include('blog::livewire.admin.partials.english-fields', ['richEditorTarget' => 'content'])

                {{-- دسته‌بندی و برچسب --}}
                <div class="p-6 chart-card">
                    <h3 class="mb-4 text-lg font-bold">دسته‌بندی و برچسب‌ها</h3>
                    <div class="space-y-5">
                        <div>
                            <label class="block mb-2 text-sm font-medium text-slate-700 dark:text-slate-300">دسته‌بندی‌ها</label>
                            <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                                @foreach($categories as $cat)
                                    <label class="flex cursor-pointer items-center gap-2 rounded-lg px-2.5 py-1.5 text-xs transition hover:bg-slate-100 dark:hover:bg-slate-700/50">
                                        <input type="checkbox" wire:model="category_ids" value="{{ $cat->id }}" class="rounded border-slate-300 text-primary-500 focus:ring-primary-500/20" />
                                        <span class="text-slate-700 dark:text-slate-300">{{ $cat->name }}</span>
                                    </label>
                                @endforeach
                                @empty($categories)
                                    <p class="text-xs text-slate-400">دسته‌بندی‌ای ثبت نشده — از بخش دسته‌بندی‌ها اضافه کنید</p>
                                @endempty
                            </div>
                            @error('category_ids') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium text-slate-700 dark:text-slate-300">برچسب‌ها</label>
                            <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                                @foreach($tags as $tag)
                                    <label class="flex cursor-pointer items-center gap-2 rounded-lg px-2.5 py-1.5 text-xs transition hover:bg-slate-100 dark:hover:bg-slate-700/50">
                                        <input type="checkbox" wire:model="tag_ids" value="{{ $tag->id }}" class="rounded border-slate-300 text-primary-500 focus:ring-primary-500/20" />
                                        <span class="text-slate-700 dark:text-slate-300">{{ $tag->name }}</span>
                                    </label>
                                @endforeach
                                @empty($tags)
                                    <p class="text-xs text-slate-400">برچسبی ثبت نشده — از بخش برچسب‌ها اضافه کنید</p>
                                @endempty
                            </div>
                            @error('tag_ids') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                {{-- SEO --}}
                <div class="p-6 chart-card">
                    <h3 class="mb-4 text-lg font-bold">SEO</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block mb-1 text-sm font-medium text-slate-700 dark:text-slate-300">عنوان SEO</label>
                            <input type="text" wire:model.blur="seo_title" class="w-full px-4 py-2 border rounded-lg border-slate-300 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:border-slate-600 dark:bg-surface-800 dark:text-white" placeholder="عنوان SEO" />
                            @error('seo_title') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block mb-1 text-sm font-medium text-slate-700 dark:text-slate-300">توضیحات SEO</label>
                            <textarea wire:model.blur="seo_description" rows="2" class="w-full px-4 py-2 border rounded-lg border-slate-300 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:border-slate-600 dark:bg-surface-800 dark:text-white" placeholder="توضیحات SEO"></textarea>
                            @error('seo_description') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ستون کناری --}}
            <div class="space-y-6">
                {{-- وضعیت انتشار --}}
                <div class="p-6 chart-card">
                    <h3 class="mb-4 text-lg font-bold">تنظیمات انتشار</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block mb-1 text-sm font-medium text-slate-700 dark:text-slate-300">وضعیت <span class="text-red-500">*</span></label>
                            <select wire:model.live="status" class="w-full px-4 py-2 border rounded-lg border-slate-300 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:border-slate-600 dark:bg-surface-800 dark:text-white">
                                <option value="draft">پیش‌نویس</option>
                                <option value="published">منتشر شده</option>
                            </select>
                            @error('status') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <p class="text-xs text-slate-400 dark:text-slate-500">
                            هنگام انتشار، تاریخ انتشار به‌صورت خودکار ثبت می‌شود.
                        </p>
                    </div>
                </div>

                {{-- تصویر شاخص — انتخاب ریل‌تایم از گالری + آپلود مستقیم + سئو (Alt) --}}
                <div class="p-6 chart-card">
                    <h3 class="mb-4 text-lg font-bold">تصویر شاخص</h3>

                    @php
                        $featuredPreview = null;
                        $featuredSource = null; // gallery | upload | stored
                        if ($featured_image && method_exists($featured_image, 'temporaryUrl')) {
                            $featuredPreview = $featured_image->temporaryUrl();
                            $featuredSource = 'upload';
                        } elseif ($gallerySrc) {
                            $featuredPreview = featured_image_url($gallerySrc);
                            $featuredSource = 'gallery';
                        } elseif ($existingImage) {
                            $featuredPreview = asset('storage/' . $existingImage);
                            $featuredSource = 'stored';
                        }

                        $featuredPickerAssets = collect($galleryAssets)
                            ->filter(fn ($a) => ! $a->is_video)
                            ->map(fn ($a) => [
                                'id' => $a->id,
                                'src' => $a->srcAttribute(),
                                'thumb' => $a->thumbAttribute() ?: $a->srcAttribute(),
                                'alt' => $a->alt,
                                'metaTitle' => $a->meta_title,
                            ])->values();

                        $featuredPickerAssetsJson = json_encode(
                            $featuredPickerAssets,
                            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                        );
                    @endphp

                    <div x-data="{
                            pickerOpen: false,
                            pick: null,
                            assets: {{ $featuredPickerAssetsJson }},
                            confirmPick() {
                                if (! this.pick) return;
                                this.pickerOpen = false;
                                $wire.call('pickGalleryImage', this.pick.src, this.pick.alt || '');
                            }
                         }" class="space-y-3">

                        {{-- پیش‌نمایش تصویر فعلی --}}
                        @if($featuredPreview)
                            <div class="relative overflow-hidden rounded-xl border border-slate-200 dark:border-slate-700">
                                <img src="{{ $featuredPreview }}" alt="{{ $featured_image_alt ?: 'تصویر شاخص' }}" class="max-h-44 w-full object-cover" />
                                <span class="absolute top-2 right-2 rounded-full bg-black/55 px-2 py-0.5 text-[10px] font-bold text-white backdrop-blur">
                                    {{ ['gallery' => 'از گالری', 'upload' => 'آپلود جدید', 'stored' => 'ذخیره‌شده'][$featuredSource] ?? '' }}
                                </span>
                            </div>
                        @else
                            <div class="flex flex-col items-center gap-2 rounded-xl border-2 border-dashed border-slate-300 py-7 text-slate-400 dark:border-slate-600">
                                <svg class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 19.5h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5z"/></svg>
                                <span class="text-xs font-bold">تصویری انتخاب نشده — از گالری انتخاب کنید یا فایل آپلود کنید</span>
                            </div>
                        @endif

                        {{-- اکشن‌ها --}}
                        <div class="flex flex-wrap items-center gap-2">
                            <button type="button" @click="pick = null; pickerOpen = true"
                                    class="inline-flex items-center gap-1.5 rounded-xl bg-primary-500 px-3.5 py-2 text-xs font-bold text-white shadow-md shadow-primary-500/25 transition hover:bg-primary-600">
                                🖼️ {{ $featuredPreview ? 'تغییر از گالری' : 'انتخاب از گالری' }}
                            </button>

                            <label class="inline-flex cursor-pointer items-center gap-1.5 rounded-xl border border-slate-200 px-3.5 py-2 text-xs font-bold text-slate-600 transition hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-700">
                                ⇪ آپلود فایل
                                <input type="file" wire:model="featured_image" accept="image/*" class="hidden">
                            </label>
                            <span wire:loading wire:target="featured_image" class="inline-flex items-center gap-1 text-[11px] font-bold text-primary-600">
                                <svg class="h-3.5 w-3.5 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                در حال آپلود…
                            </span>

                            @if($featuredPreview)
                                <button type="button" wire:click="removeImage"
                                        class="ms-auto inline-flex items-center gap-1.5 rounded-xl border border-rose-200 px-3 py-2 text-xs font-bold text-rose-600 transition hover:bg-rose-50 dark:border-rose-900/50 dark:text-rose-400 dark:hover:bg-rose-900/20">
                                    حذف تصویر
                                </button>
                            @endif
                        </div>

                        {{-- سئو: متن جایگزین (Alt) — ریل‌تایم روی همان تصویر --}}
                        @if($featuredPreview)
                            <div class="rounded-xl border border-amber-200 bg-amber-50 p-3 dark:border-amber-900/40 dark:bg-amber-900/20">
                                <div class="mb-2 flex items-center justify-between">
                                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">
                                        سئوی تصویر
                                    </span>
                                    @if($featured_image_alt !== '')
                                        <span class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400">✓ Alt اعمال شد</span>
                                    @else
                                        <span class="text-[10px] font-bold text-amber-600 dark:text-amber-400">⚠ Alt خالی است — برای سئو توصیه می‌شود</span>
                                    @endif
                                </div>
                                <input type="text" maxlength="255" wire:model.live.debounce.300ms="featured_image_alt"
                                       placeholder="متن جایگزین تصویر (Alt)… مثال: آموزش لاراول در تهران"
                                       class="w-full rounded-lg border border-amber-300/70 bg-white px-3 py-2 text-xs focus:border-amber-400 focus:outline-none dark:border-amber-800 dark:bg-slate-900 dark:text-slate-200" />
                                <p class="mt-1.5 text-[10px] leading-5 text-amber-700 dark:text-amber-300/80">
                                    این متن در تگ alt تصویر سایت، نتایج جستجو و صفحه‌خوان‌ها استفاده می‌شود؛ اگر خالی بماند، عنوان مقاله جایگزین می‌شود.
                                </p>
                                @error('featured_image_alt') <p class="mt-1 text-[11px] font-bold text-rose-600">{{ $message }}</p> @enderror
                            </div>
                        @endif

                        @error('featured_image') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        @error('gallerySrc') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror

                        {{-- مودال انتخاب از گالری — باز شدن ریل‌تایم (Alpine) بدون رفرش صفحه --}}
                        <div x-cloak x-show="pickerOpen" x-transition.opacity
                             class="fixed inset-0 z-[90] flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm"
                             @keydown.escape.window="pickerOpen = false">
                            <div class="flex max-h-[85vh] w-full max-w-2xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-surface-800" @click.stop>
                                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 dark:border-slate-700">
                                    <div>
                                        <h4 class="text-base font-bold">انتخاب تصویر از گالری</h4>
                                        <p class="mt-0.5 text-[11px] text-slate-400">پس از انتخاب، می‌توانید Alt (سئو) تصویر را زیر پیش‌نمایش ویرایش کنید</p>
                                    </div>
                                    <button type="button" @click="pickerOpen = false" class="rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 dark:hover:bg-slate-700" aria-label="بستن">
                                        <x-icon name="close" size="5" />
                                    </button>
                                </div>

                                <div class="flex-1 overflow-y-auto p-4">
                                    <div class="grid grid-cols-3 gap-3">
                                        <template x-for="asset in assets" :key="asset.id">
                                            <button type="button" @click="pick = asset"
                                                    class="group relative overflow-hidden rounded-xl border-2 text-start transition"
                                                    :class="pick && pick.id === asset.id ? 'border-primary-500 ring-2 ring-primary-500/30' : 'border-transparent hover:border-primary-300'">
                                                <img :src="asset.thumb" loading="lazy" class="h-24 w-full object-cover" :alt="asset.alt || ''">
                                                <span class="block truncate px-2 py-1 text-[10px] text-slate-600 dark:text-slate-300" x-text="asset.alt || asset.metaTitle || 'بدون عنوان'"></span>
                                                <span x-show="pick && pick.id === asset.id" x-cloak
                                                      class="absolute top-1.5 left-1.5 rounded-full bg-primary-500 px-1.5 py-0.5 text-[9px] font-bold text-white shadow">✓</span>
                                            </button>
                                        </template>
                                        <div x-show="!assets.length" class="col-span-3 py-12 text-center text-sm text-slate-400">
                                            هنوز تصویری در گالری نیست — ابتدا از بخش «گالری تصاویر» در پنل آپلود کنید.
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3 border-t border-slate-100 px-5 py-3 dark:border-slate-700">
                                    <span class="hidden text-[11px] text-slate-400 sm:inline" x-show="pick" x-text="pick ? (pick.alt ? 'Alt پیش‌فرض: ' + pick.alt : 'این تصویر Alt ندارد') : ''"></span>
                                    <div class="ms-auto flex gap-2">
                                        <button type="button" @click="pickerOpen = false"
                                                class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-bold text-slate-600 transition hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-700">
                                            بستن
                                        </button>
                                        <button type="button" @click="confirmPick()" :disabled="!pick"
                                                class="rounded-xl bg-primary-500 px-4 py-2 text-xs font-bold text-white shadow transition hover:bg-primary-600 disabled:cursor-not-allowed disabled:opacity-40">
                                            انتخاب تصویر
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- دکمه‌های ذخیره --}}
        <div class="flex gap-3">
            <button type="submit" wire:loading.attr="disabled" wire:target="save"
                    class="flex items-center gap-2 flex-1 rounded-xl bg-primary-500 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-primary-500/25 transition-all hover:shadow-lg hover:shadow-primary-500/35 disabled:opacity-50">
                <span wire:loading.remove>{{ $creating ? 'ایجاد مقاله' : 'ذخیره تغییرات' }}</span>
                <span wire:loading class="flex items-center gap-2">
                    <svg class="w-4 h-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    در حال ذخیره...
                </span>
            </button>
            <a href="{{ route('admin.blog.index') }}"
               class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-surface-700">
                لغو
            </a>
        </div>
    </form>
</div>
