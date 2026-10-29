<div>
    @once
        {{-- تقویم شمسی سفارشی برای «تاریخ پروژه» — جایگزین date input بی‌ریخت مرورگر --}}
        <script>
            (function () {
                if (window.ucDatePicker) return;

                // الگوریتم استاندارد تبدیل جلالی ⇄ میلادی (jalaali-js)
                function div(a, b) { return ~~(a / b); }
                function mod(a, b) { return a - ~~(a / b) * b; }
                function g2d(gy, gm, gd) {
                    var d = div((gy + div(gm - 8, 6) + 100100) * 1461, 4)
                        + div(153 * mod(gm + 9, 12) + 2, 5)
                        + gd - 34840408;
                    d = d - div(div(gy + 100100 + div(gm - 8, 6), 100) * 3, 4) + 752;
                    return d;
                }
                function d2g(jdn) {
                    var j = 4 * jdn + 139361631;
                    j = j + div(div(4 * jdn + 183187720, 146097) * 3, 4) * 4 - 3908;
                    var i = div(mod(j, 1461), 4) * 5 + 308;
                    var gd = div(mod(i, 153), 5) + 1;
                    var gm = mod(div(i, 153), 12) + 1;
                    var gy = div(j, 1461) - 100100 + div(8 - gm, 6);
                    return { gy: gy, gm: gm, gd: gd };
                }
                function jalCal(jy) {
                    var breaks = [-61, 9, 38, 199, 426, 686, 756, 818, 1111, 1181, 1210, 1635, 2060, 2097, 2192, 2262, 2324, 2394, 2456, 3178];
                    var bl = breaks.length, gy = jy + 621, leapJ = -14, jp = breaks[0], jm, jump, leapG, march, n, i;
                    if (jy < jp || jy >= breaks[bl - 1]) throw new Error('Invalid Jalaali year ' + jy);
                    for (i = 1; i < bl; i += 1) {
                        jm = breaks[i]; jump = jm - jp;
                        if (jy < jm) break;
                        leapJ = leapJ + div(jump, 33) * 8 + div(mod(jump, 33), 4);
                        jp = jm;
                    }
                    n = jy - jp;
                    leapJ = leapJ + div(n, 33) * 8 + div(mod(n, 33) + 3, 4);
                    if (mod(jump, 33) === 4 && jump - n === 4) leapJ += 1;
                    leapG = div(gy, 4) - div((div(gy, 100) + 1) * 3, 4) - 150;
                    march = 20 + leapJ - leapG;
                    var leap = mod(mod(n + 1, 33) - 1, 4);
                    if (leap === -1) leap = 4;
                    return { leap: leap, gy: gy, march: march };
                }
                function g2j(gy, gm, gd) {
                    var jdn = g2d(gy, gm, gd);
                    var gy0 = d2g(jdn).gy, jy0 = gy0 - 621;
                    var r = jalCal(jy0);
                    var jdn1f = g2d(gy0, 3, r.march);
                    var k = jdn - jdn1f, jm, jd;
                    if (k >= 0) {
                        if (k <= 185) { jm = 1 + div(k, 31); jd = mod(k, 31) + 1; return { jy: jy0, jm: jm, jd: jd }; }
                        k -= 186;
                    } else { jy0 -= 1; k += 179; if (r.leap === 1) k += 1; }
                    jm = 7 + div(k, 30); jd = mod(k, 30) + 1;
                    return { jy: jy0, jm: jm, jd: jd };
                }
                function j2g(jy, jm, jd) {
                    var r = jalCal(jy);
                    var jdn = g2d(r.gy, 3, r.march) + (jm - 1) * 31 - div(jm, 7) * (jm - 7) + jd - 1;
                    return d2g(jdn);
                }
                function monthLength(jy, jm) {
                    if (jm <= 6) return 31;
                    if (jm <= 11) return 30;
                    return jalCal(jy).leap === 1 ? 30 : 29;
                }
                function faDigits(s) {
                    return String(s).replace(/[0-9]/g, function (d) { return '۰۱۲۳۴۵۶۷۸۹'[+d]; });
                }

                window.ucJal = {
                    toJalaali: function (gy, gm, gd) { return g2j(gy, gm, gd); },
                    toGregorian: function (jy, jm, jd) { return j2g(jy, jm, jd); },
                    monthLength: monthLength,
                    faDigits: faDigits,
                    months: ['فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'],
                    weekdays: ['ش', 'ی', 'د', 'س', 'چ', 'پ', 'ج'],
                };

                window.ucDatePicker = function (initial) {
                    return {
                        open: false,
                        value: initial || null, // Y-m-d میلادی | null
                        view: null,            // { y, m } شمسی — ماه نمایش‌داده‌شده

                        init() { this.ensureView(); },

                        ensureView() {
                            if (this.view) return;
                            if (this.value) {
                                var p = this.value.split('-');
                                var j = ucJal.toJalaali(+p[0], +p[1], +p[2]);
                                this.view = { y: j.jy, m: j.jm };
                            } else {
                                var t = this.todayJ();
                                this.view = { y: t.jy, m: t.jm };
                            }
                        },

                        todayJ() {
                            var n = new Date();
                            return ucJal.toJalaali(n.getFullYear(), n.getMonth() + 1, n.getDate());
                        },

                        toIso(jy, jm, jd) {
                            var g = ucJal.toGregorian(jy, jm, jd);
                            return g.gy + '-' + String(g.gm).padStart(2, '0') + '-' + String(g.gd).padStart(2, '0');
                        },

                        get text() {
                            if (!this.value) return '';
                            var p = this.value.split('-');
                            var j = ucJal.toJalaali(+p[0], +p[1], +p[2]);
                            return ucJal.faDigits(j.jy + '/' + String(j.jm).padStart(2, '0') + '/' + String(j.jd).padStart(2, '0'));
                        },

                        toggle() { this.open = !this.open; if (this.open) { this.view = null; this.ensureView(); } },
                        prev() { this.view.m--; if (this.view.m < 1) { this.view.m = 12; this.view.y--; } },
                        next() { this.view.m++; if (this.view.m > 12) { this.view.m = 1; this.view.y++; } },
                        monthLabel() { return ucJal.months[this.view.m - 1] + ' ' + ucJal.faDigits(this.view.y); },

                        cells() {
                            var len = ucJal.monthLength(this.view.y, this.view.m);
                            var g = ucJal.toGregorian(this.view.y, this.view.m, 1);
                            var offset = (new Date(g.gy, g.gm - 1, g.gd).getDay() + 1) % 7; // شنبه = 0
                            var out = [];
                            for (var i = 0; i < offset; i++) out.push(null);
                            for (var d = 1; d <= len; d++) out.push(d);
                            while (out.length % 7 !== 0) out.push(null);
                            return out;
                        },

                        selectedJ() {
                            if (!this.value) return null;
                            var p = this.value.split('-');
                            return ucJal.toJalaali(+p[0], +p[1], +p[2]);
                        },

                        isSel(d) {
                            if (!d) return false;
                            var j = this.selectedJ();
                            return !!j && j.jy === this.view.y && j.jm === this.view.m && j.jd === d;
                        },

                        isToday(d) {
                            if (!d) return false;
                            var t = this.todayJ();
                            return t.jy === this.view.y && t.jm === this.view.m && t.jd === d;
                        },

                        pick(d) {
                            if (!d) return;
                            this.value = this.toIso(this.view.y, this.view.m, d);
                            this.open = false;
                            try { this.$wire.set('project_date', this.value); } catch (e) { /* no-op */ }
                        },

                        today() {
                            var t = this.todayJ();
                            this.view = { y: t.jy, m: t.jm };
                            this.value = this.toIso(t.jy, t.jm, t.jd);
                            this.open = false;
                            try { this.$wire.set('project_date', this.value); } catch (e) { /* no-op */ }
                        },

                        clear() {
                            this.value = null;
                            this.open = false;
                            try { this.$wire.set('project_date', null); } catch (e) { /* no-op */ }
                        },

                        fa(n) { return ucJal.faDigits(String(n)); },
                    };
                };
            })();
        </script>
    @endonce

    {{-- هدر --}}
    <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
        <div>
            <h2 class="text-2xl font-bold sm:text-3xl">{{ $creating ? 'افزودن پروژه جدید' : 'ویرایش: ' . $project->title }}</h2>
            <p class="mt-1 text-slate-500 dark:text-slate-400">{{ $creating ? 'اطلاعات نمونه‌کار جدید را وارد کنید' : 'اطلاعات پروژه را به‌روزرسانی کنید' }}</p>
        </div>
        <a href="{{ route('admin.resume.portfolio.index') }}"
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
                            <input type="text" wire:model.blur="title" class="w-full px-4 py-2 border rounded-lg border-slate-300 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:border-slate-600 dark:bg-surface-800 dark:text-white" placeholder="عنوان پروژه" />
                            @error('title') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block mb-1 text-sm font-medium text-slate-700 dark:text-slate-300">Slug <span class="text-red-500">*</span></label>
                            <input type="text" wire:model.blur="slug" dir="ltr" class="w-full px-4 py-2 border rounded-lg border-slate-300 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:border-slate-600 dark:bg-surface-800 dark:text-white" placeholder="project-slug" />
                            @error('slug') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block mb-1 text-sm font-medium text-slate-700 dark:text-slate-300">توضیح کوتاه</label>
                            <input type="text" wire:model.blur="short_description" class="w-full px-4 py-2 border rounded-lg border-slate-300 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:border-slate-600 dark:bg-surface-800 dark:text-white" placeholder="خلاصه یک‌خطی پروژه (برای کارت‌ها)" />
                            @error('short_description') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block mb-1 text-sm font-medium text-slate-700 dark:text-slate-300">توضیحات کامل <span class="text-red-500">*</span></label>
                            <textarea wire:model.blur="description" rows="6" class="w-full px-4 py-2 border rounded-lg border-slate-300 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:border-slate-600 dark:bg-surface-800 dark:text-white" placeholder="توضیحات کامل پروژه..."></textarea>
                            @error('description') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                {{-- نسخه انگلیسی — عنوان، اسلاگ و توضیحات --}}
                <div class="p-6 border border-sky-200 bg-sky-50/40 rounded-2xl dark:border-sky-800 dark:bg-sky-950/20">
                    <h3 class="mb-1 text-lg font-bold flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 21l5.25-11.25L21 21m-9-3h7.5M3 5.621a48.474 48.474 0 016-.371m0 0c1.12 0 2.233.038 3.334.114M9 5.25V3m3.334 2.364C11.176 10.658 7.69 15.08 3 17.502m9.334-12.138c.896.061 1.785.147 2.666.257m-4.589 8.495a18.023 18.023 0 01-3.827-5.802"/></svg>
                        نسخه انگلیسی (EN)
                    </h3>
                    <p class="mb-4 text-xs text-slate-500 dark:text-slate-400">اختیاری — اگر خالی بماند، محتوای فارسی به کاربر انگلیسی نمایش داده می‌شود.</p>

                    <div class="space-y-4" dir="ltr">
                        <div>
                            <label class="block mb-1 text-sm font-medium text-slate-700 dark:text-slate-300">عنوان انگلیسی (EN)</label>
                            <input type="text" wire:model.blur="title_en" dir="ltr" class="w-full px-4 py-2 border rounded-lg border-slate-300 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:border-slate-600 dark:bg-surface-800 dark:text-white" placeholder="English project title" />
                            @error('title_en') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block mb-1 text-sm font-medium text-slate-700 dark:text-slate-300">اسلاگ انگلیسی (EN Slug)</label>
                            <input type="text" wire:model.blur="slug_en" dir="ltr" class="w-full px-4 py-2 border rounded-lg border-slate-300 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:border-slate-600 dark:bg-surface-800 dark:text-white" placeholder="english-project-slug" />
                            <p class="mt-1 text-[11px] text-slate-400" dir="rtl">خالی بماند یا از عنوان انگلیسی به‌صورت خودکار ساخته می‌شود.</p>
                            @error('slug_en') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block mb-1 text-sm font-medium text-slate-700 dark:text-slate-300">توضیح کوتاه انگلیسی (EN)</label>
                            <input type="text" wire:model.blur="short_description_en" dir="ltr" class="w-full px-4 py-2 border rounded-lg border-slate-300 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:border-slate-600 dark:bg-surface-800 dark:text-white" placeholder="One-line English summary" />
                            @error('short_description_en') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block mb-1 text-sm font-medium text-slate-700 dark:text-slate-300">توضیحات کامل انگلیسی (EN)</label>
                            <textarea wire:model.blur="description_en" rows="6" dir="ltr" class="w-full px-4 py-2 border rounded-lg border-slate-300 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:border-slate-600 dark:bg-surface-800 dark:text-white" placeholder="Full English description of the project..."></textarea>
                            @error('description_en') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                {{-- تکنولوژی‌ها و مهارت‌ها --}}
                <div class="p-6 chart-card">
                    <h3 class="mb-4 text-lg font-bold">تکنولوژی‌ها و مهارت‌های مرتبط</h3>
                    <div class="space-y-5">
                        <div>
                            <label class="block mb-2 text-sm font-medium text-slate-700 dark:text-slate-300">تکنولوژی‌ها</label>
                            <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                                @foreach($technologies as $tech)
                                    <label class="flex cursor-pointer items-center gap-2 rounded-lg px-2.5 py-1.5 text-xs transition hover:bg-slate-100 dark:hover:bg-slate-700/50">
                                        <input type="checkbox" wire:model="technology_ids" value="{{ $tech->id }}" class="rounded border-slate-300 text-primary-500 focus:ring-primary-500/20" />
                                        <span class="text-slate-700 dark:text-slate-300">{{ $tech->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('technology_ids') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium text-slate-700 dark:text-slate-300">مهارت‌های مرتبط</label>
                            <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                                @foreach($skills as $skill)
                                    <label class="flex cursor-pointer items-center gap-2 rounded-lg px-2.5 py-1.5 text-xs transition hover:bg-slate-100 dark:hover:bg-slate-700/50">
                                        <input type="checkbox" wire:model="skill_ids" value="{{ $skill->id }}" class="rounded border-slate-300 text-primary-500 focus:ring-primary-500/20" />
                                        <span class="text-slate-700 dark:text-slate-300">{{ $skill->name }}</span>
                                    </label>
                                @endforeach
                                @empty($skills)
                                    <p class="text-xs text-slate-400">مهارتی ثبت نشده — ابتدا از بخش مهارت‌ها اضافه کنید</p>
                                @endempty
                            </div>
                            @error('skill_ids') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
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

                        <div class="pt-3 mt-2 border-t border-slate-200 dark:border-slate-700">
                            <p class="mb-3 text-xs font-bold text-sky-600 dark:text-sky-400">سئوی انگلیسی (English SEO)</p>
                            <div class="space-y-4" dir="ltr">
                                <div>
                                    <label class="block mb-1 text-sm font-medium text-slate-700 dark:text-slate-300">عنوان SEO انگلیسی (EN)</label>
                                    <input type="text" wire:model.blur="seo_title_en" dir="ltr" class="w-full px-4 py-2 border rounded-lg border-slate-300 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:border-slate-600 dark:bg-surface-800 dark:text-white" placeholder="English SEO title" />
                                    @error('seo_title_en') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block mb-1 text-sm font-medium text-slate-700 dark:text-slate-300">توضیحات SEO انگلیسی (EN)</label>
                                    <textarea wire:model.blur="seo_description_en" rows="2" dir="ltr" class="w-full px-4 py-2 border rounded-lg border-slate-300 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:border-slate-600 dark:bg-surface-800 dark:text-white" placeholder="English SEO description"></textarea>
                                    @error('seo_description_en') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ستون کناری --}}
            <div class="space-y-6">
                {{-- وضعیت و تنظیمات --}}
                <div class="p-6 chart-card">
                    <h3 class="mb-4 text-lg font-bold">تنظیمات انتشار</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block mb-1 text-sm font-medium text-slate-700 dark:text-slate-300">وضعیت <span class="text-red-500">*</span></label>
                            <select wire:model.live="status" class="w-full px-4 py-2 border rounded-lg border-slate-300 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:border-slate-600 dark:bg-surface-800 dark:text-white">
                                <option value="draft">پیش‌نویس</option>
                                <option value="published">منتشر شده</option>
                                <option value="archived">آرشیو</option>
                            </select>
                            @error('status') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <label class="flex items-center gap-2 text-sm font-medium text-slate-700 dark:text-slate-300 cursor-pointer">
                            <input type="checkbox" wire:model="featured" class="rounded border-slate-300 text-primary-500 focus:ring-primary-500/20" />
                            پروژه ویژه (Featured)
                        </label>
                        <div>
                            <label class="block mb-1 text-sm font-medium text-slate-700 dark:text-slate-300">ترتیب نمایش</label>
                            <input type="number" wire:model.blur="order" min="0" max="9999" dir="ltr" class="w-full px-4 py-2 border rounded-lg border-slate-300 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:border-slate-600 dark:bg-surface-800 dark:text-white" />
                            @error('order') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                {{-- دسته‌بندی و مشتری --}}
                <div class="p-6 chart-card">
                    <h3 class="mb-4 text-lg font-bold">دسته‌بندی و مشتری</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block mb-1 text-sm font-medium text-slate-700 dark:text-slate-300">دسته‌بندی</label>
                            <select wire:model.live="categoryId" class="w-full px-4 py-2 border rounded-lg border-slate-300 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:border-slate-600 dark:bg-surface-800 dark:text-white">
                                <option value="">انتخاب دسته‌بندی</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            @error('categoryId') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block mb-1 text-sm font-medium text-slate-700 dark:text-slate-300">محصول مرتبط (از دسته‌بندی محصولات)</label>
                            <select wire:model.live="relatedProductId" class="w-full px-4 py-2 border rounded-lg border-slate-300 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:border-slate-600 dark:bg-surface-800 dark:text-white">
                                <option value="">بدون محصول مرتبط</option>
                                @foreach ($products->groupBy(fn ($p) => $p->category?->name ?? 'بدون دسته') as $groupName => $groupProducts)
                                    <optgroup label="{{ $groupName }}">
                                        @foreach ($groupProducts as $prod)
                                            <option value="{{ $prod->id }}">{{ $prod->name }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">دکمهٔ «جزئیات پروژه» در صفحه اصلی و نمونه‌کارها کاربر را به صفحهٔ همین محصول می‌برد.</p>
                            @error('relatedProductId') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block mb-1 text-sm font-medium text-slate-700 dark:text-slate-300">نام مشتری</label>
                            <input type="text" wire:model.blur="client_name" class="w-full px-4 py-2 border rounded-lg border-slate-300 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:border-slate-600 dark:bg-surface-800 dark:text-white" placeholder="نام مشتری یا کارفرما" />
                            @error('client_name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block mb-1 text-sm font-medium text-slate-700 dark:text-slate-300">تاریخ پروژه</label>
                            <div x-data="ucDatePicker({{ json_encode($project_date) }})" x-init="init()" class="relative" @click.outside="open = false">
                                <button type="button" @click="toggle()"
                                        class="flex w-full items-center justify-between gap-2 px-4 py-2 border rounded-lg border-slate-300 text-start hover:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:border-slate-600 dark:bg-surface-800 dark:text-white">
                                    <span class="text-sm" :class="value ? 'font-medium text-slate-700 dark:text-white' : 'text-slate-400'" x-text="text || 'انتخاب تاریخ (شمسی)…'"></span>
                                    <svg class="w-4 h-4 shrink-0 text-primary-500" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                                </button>

                                <div x-cloak x-show="open" x-transition.origin.top.scale.95.opacity.duration.150ms
                                     class="absolute z-40 mt-2 w-[19rem] rounded-2xl border border-slate-200 bg-white p-3 shadow-2xl dark:border-slate-600 dark:bg-surface-900">
                                    <div class="flex items-center justify-between mb-2">
                                        <button type="button" @click="prev()" title="ماه قبل" class="p-1.5 rounded-lg text-slate-500 transition hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-700">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
                                        </button>
                                        <span class="text-sm font-bold text-slate-800 dark:text-white" x-text="monthLabel()"></span>
                                        <button type="button" @click="next()" title="ماه بعد" class="p-1.5 rounded-lg text-slate-500 transition hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-700">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                                        </button>
                                    </div>

                                    <div class="grid grid-cols-7 gap-1 mb-1">
                                        <template x-for="w in ['ش', 'ی', 'د', 'س', 'چ', 'پ', 'ج']" :key="w">
                                            <span class="py-1 text-center text-[11px] font-bold text-slate-400" x-text="w"></span>
                                        </template>
                                    </div>

                                    <div class="grid grid-cols-7 gap-1">
                                        <template x-for="(d, i) in cells()" :key="'c' + i">
                                            <button type="button" x-show="d !== null" @click="pick(d)" x-text="fa(d)"
                                                    class="h-8 rounded-lg text-xs font-semibold transition"
                                                    :class="isSel(d)
                                                        ? 'bg-primary-500 text-white shadow-md shadow-primary-500/30'
                                                        : (isToday(d)
                                                            ? 'bg-primary-50 text-primary-600 ring-1 ring-primary-300 dark:bg-primary-900/30 dark:text-primary-300'
                                                            : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-700')"></button>
                                        </template>
                                    </div>

                                    <div class="flex items-center justify-between mt-2 pt-2 border-t border-slate-100 dark:border-slate-700">
                                        <button type="button" @click="today()" class="text-[11px] font-bold text-primary-600 hover:underline dark:text-primary-400">امروز</button>
                                        <button type="button" x-show="value" @click="clear()" class="text-[11px] font-bold text-rose-500 hover:underline">پاک کردن</button>
                                    </div>
                                </div>
                            </div>
                            @error('project_date') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                {{-- تصاویر: کاور اصلی + کاور نسخه انگلیسی (انتخاب ریل‌تایم از گالری) --}}
                <div class="p-6 chart-card">
                    <h3 class="mb-4 text-lg font-bold">تصاویر</h3>
                    @php
                        $pickerAssetsJson = json_encode(
                            collect($galleryAssets)
                                ->filter(fn ($a) => ! $a->is_video)
                                ->map(fn ($a) => [
                                    'id' => $a->id,
                                    'src' => $a->srcAttribute(),
                                    'thumb' => $a->thumbAttribute() ?: $a->srcAttribute(),
                                    'alt' => $a->alt,
                                ])->values(),
                            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                        );

                        $coverPreview = $cover_image && method_exists($cover_image, 'temporaryUrl')
                            ? $cover_image->temporaryUrl()
                            : ($coverSrc ? featured_image_url($coverSrc) : ($existingCover ? featured_image_url($existingCover) : null));

                        $coverEnPreview = $cover_image_en && method_exists($cover_image_en, 'temporaryUrl')
                            ? $cover_image_en->temporaryUrl()
                            : ($coverEnSrc ? featured_image_url($coverEnSrc) : ($existingCoverEn ? featured_image_url($existingCoverEn) : null));
                    @endphp

                    <div class="space-y-6">
                        {{-- کاور اصلی (فارسی) --}}
                        <div x-data="{
                                pickerOpen: false,
                                pick: null,
                                method: 'pickCoverImage',
                                assets: {{ $pickerAssetsJson }},
                                confirmPick() {
                                    if (! this.pick) return;
                                    this.pickerOpen = false;
                                    $wire.call(this.method, this.pick.src);
                                }
                             }">
                            <label class="block mb-1 text-sm font-medium text-slate-700 dark:text-slate-300">تصویر کاور (فارسی)</label>
                            @if($coverPreview)
                                <div class="relative group mb-2 w-fit">
                                    <img src="{{ $coverPreview }}" class="max-w-full rounded-lg max-h-32" alt="کاور پروژه" />
                                    <button type="button" wire:click="removeCover"
                                            class="absolute p-1 transition rounded-lg top-1 left-1 bg-black/50 text-white opacity-0 group-hover:opacity-100 hover:bg-rose-600">
                                        <x-icon name="close" size="3" />
                                    </button>
                                </div>
                            @endif
                            <div class="flex flex-wrap items-center gap-2">
                                <button type="button" @click="pick = null; pickerOpen = true"
                                        class="inline-flex items-center gap-1.5 rounded-xl bg-primary-500 px-3.5 py-2 text-xs font-bold text-white shadow-md shadow-primary-500/25 transition hover:bg-primary-600">
                                    🖼️ {{ $coverPreview ? 'تغییر از گالری' : 'انتخاب از گالری' }}
                                </button>
                                <label class="inline-flex cursor-pointer items-center gap-1.5 rounded-xl border border-slate-200 px-3.5 py-2 text-xs font-bold text-slate-600 transition hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-700">
                                    ⇪ آپلود فایل
                                    <input type="file" wire:model="cover_image" accept="image/*" class="hidden">
                                </label>
                                <span wire:loading wire:target="cover_image" class="inline-flex items-center gap-1 text-[11px] font-bold text-primary-600">
                                    <svg class="h-3.5 w-3.5 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                    در حال آپلود…
                                </span>
                            </div>
                            @error('cover_image') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            @error('coverSrc') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror

                            @include('resume::livewire.admin.partials.gallery-picker-modal')
                        </div>

                        {{-- کاور نسخه انگلیسی — به جای «تصویر کوچک» قدیمی --}}
                        <div x-data="{
                                pickerOpen: false,
                                pick: null,
                                method: 'pickCoverEnImage',
                                assets: {{ $pickerAssetsJson }},
                                confirmPick() {
                                    if (! this.pick) return;
                                    this.pickerOpen = false;
                                    $wire.call(this.method, this.pick.src);
                                }
                             }">
                            <label class="block mb-1 text-sm font-medium text-slate-700 dark:text-slate-300">تصویر نسخه انگلیسی (EN)</label>
                            <p class="mb-1 text-xs text-slate-400 dark:text-slate-500">در سایت نسخهٔ انگلیسی نمایش داده می‌شود؛ در صورت خالی بودن از کاور اصلی استفاده می‌شود.</p>
                            @if($coverEnPreview)
                                <div class="relative group mb-2 w-fit" dir="ltr">
                                    <img src="{{ $coverEnPreview }}" class="max-w-full rounded-lg max-h-32" alt="EN cover" />
                                    <button type="button" wire:click="removeCoverEn"
                                            class="absolute p-1 transition rounded-lg top-1 left-1 bg-black/50 text-white opacity-0 group-hover:opacity-100 hover:bg-rose-600">
                                        <x-icon name="close" size="3" />
                                    </button>
                                </div>
                            @endif
                            <div class="flex flex-wrap items-center gap-2">
                                <button type="button" @click="pick = null; pickerOpen = true"
                                        class="inline-flex items-center gap-1.5 rounded-xl bg-primary-500 px-3.5 py-2 text-xs font-bold text-white shadow-md shadow-primary-500/25 transition hover:bg-primary-600">
                                    🖼️ {{ $coverEnPreview ? 'تغییر از گالری' : 'انتخاب از گالری' }}
                                </button>
                                <label class="inline-flex cursor-pointer items-center gap-1.5 rounded-xl border border-slate-200 px-3.5 py-2 text-xs font-bold text-slate-600 transition hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-700">
                                    ⇪ آپلود فایل
                                    <input type="file" wire:model="cover_image_en" accept="image/*" class="hidden">
                                </label>
                                <span wire:loading wire:target="cover_image_en" class="inline-flex items-center gap-1 text-[11px] font-bold text-primary-600">
                                    <svg class="h-3.5 w-3.5 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                    در حال آپلود…
                                </span>
                            </div>
                            @error('cover_image_en') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            @error('coverEnSrc') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror

                            @include('resume::livewire.admin.partials.gallery-picker-modal')
                        </div>
                    </div>
                </div>

                {{-- لینک‌ها و دمو --}}
                <div class="p-6 chart-card">
                    <h3 class="mb-4 text-lg font-bold">لینک‌ها و دمو</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block mb-1 text-sm font-medium text-slate-700 dark:text-slate-300">لینک GitHub</label>
                            <input type="url" wire:model.blur="github_url" dir="ltr" class="w-full px-4 py-2 border rounded-lg border-slate-300 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:border-slate-600 dark:bg-surface-800 dark:text-white" placeholder="https://github.com/..." />
                            @error('github_url') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block mb-1 text-sm font-medium text-slate-700 dark:text-slate-300">آدرس دمو</label>
                            <input type="url" wire:model.blur="demo_url" dir="ltr" class="w-full px-4 py-2 border rounded-lg border-slate-300 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:border-slate-600 dark:bg-surface-800 dark:text-white" placeholder="https://demo.example.com" />
                            @error('demo_url') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block mb-1 text-sm font-medium text-slate-700 dark:text-slate-300">نوع دمو</label>
                            <select wire:model.live="demo_type" class="w-full px-4 py-2 border rounded-lg border-slate-300 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:border-slate-600 dark:bg-surface-800 dark:text-white">
                                <option value="link">لینک (Link)</option>
                                <option value="external_url">آدرس خارجی (External URL)</option>
                                <option value="subdomain">زیردامنه (Subdomain)</option>
                            </select>
                            @error('demo_type') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">
                                برای اتصال آینده پروژه‌های مستقل؛ فعلاً فقط ذخیره و نمایش.
                            </p>
                        </div>
                        <div>
                            <label class="block mb-1 text-sm font-medium text-slate-700 dark:text-slate-300">لینک ویدئو</label>
                            <input type="url" wire:model.blur="video_url" dir="ltr" class="w-full px-4 py-2 border rounded-lg border-slate-300 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:border-slate-600 dark:bg-surface-800 dark:text-white" placeholder="https://youtube.com/..." />
                            @error('video_url') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- دکمه‌های ذخیره --}}
        <div class="flex gap-3">
            <button type="submit" wire:loading.attr="disabled" wire:target="save"
                    class="flex items-center gap-2 flex-1 rounded-xl bg-primary-500 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-primary-500/25 transition-all hover:shadow-lg hover:shadow-primary-500/35 disabled:opacity-50">
                <span wire:loading.remove>{{ $creating ? 'ایجاد پروژه' : 'ذخیره تغییرات' }}</span>
                <span wire:loading class="flex items-center gap-2">
                    <svg class="w-4 h-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    در حال ذخیره...
                </span>
            </button>
            <a href="{{ route('admin.resume.portfolio.index') }}"
               class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-surface-700">
                لغو
            </a>
        </div>
    </form>
</div>
