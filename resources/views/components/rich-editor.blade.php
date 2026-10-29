@props([
    'target',
    'uploadProp' => 'inlineUpload',
    'event' => 'inline-media-uploaded',
    'lang' => 'fa',
    'placeholder' => null,
    'galleryAssets' => [],
    'selectedGalleryIds' => [],
])

@php
    $isEn = $lang === 'en';
    $labels = $isEn
        ? [
            'layoutTitle' => 'Layout',
            'full' => 'Full',
            'center' => 'Center',
            'left' => 'Left',
            'right' => 'Right',
            'galleryBtn' => 'Gallery',
            'insertBtn' => 'Insert',
            'replaceBtn' => 'Replace',
            'closeBtn' => 'Close',
            'hint' => 'Drag an image file onto the surface to upload • drag a figure to one of the three dashed zones to move it • click an image for its menu.',
            'placeholder' => 'Write your content… you can add headings, lists, links and images from the gallery.',
            'emptyGallery' => 'No images in the gallery yet.',
            'emptyGalleryHint' => 'Upload images from the Gallery page in the admin panel first.',
            'moveUp' => 'Move up',
            'moveDown' => 'Move down',
            'remove' => 'Remove',
            'para' => 'Paragraph',
            'h2' => 'Big heading',
            'h3' => 'Small heading',
            'linkTitle' => 'Link',
            'linkPlaceholder' => 'https://example.com',
            'applyLink' => 'Apply',
            'removeLink' => 'Remove link',
            'directionTitle' => 'Toggle text direction',
            'uploadTitle' => 'Upload image',
            'uploading' => 'Uploading…',
            'altLabel' => 'SEO',
            'altPlaceholder' => 'Alternative text for the image (Alt)…',
            'captionPlaceholder' => 'Image caption (optional)',
        ]
        : [
            'layoutTitle' => 'چیدمان',
            'full' => 'کامل',
            'center' => 'وسط',
            'left' => 'چپ',
            'right' => 'راست',
            'galleryBtn' => 'گالری',
            'insertBtn' => 'درج تصویر',
            'replaceBtn' => 'جایگزینی',
            'closeBtn' => 'بستن',
            'hint' => 'کشیدن فایل تصویر روی سطح ویرایش، آن را آپلود و درج می‌کند • تصویر را بکشید و در یکی از سه ناحیهٔ خط‌چین رها کنید • کلیک روی تصویر منوی تنظیمات را باز می‌کند.',
            'placeholder' => 'متن خود را بنویسید… می‌توانید تیتر، فهرست، لینک و تصویر از گالری اضافه کنید.',
            'emptyGallery' => 'هنوز تصویری در گالری نیست.',
            'emptyGalleryHint' => 'ابتدا از بخش «گالری» در پنل ادمین تصویر آپلود کنید.',
            'moveUp' => 'حرکت به بالا',
            'moveDown' => 'حرکت به پایین',
            'remove' => 'حذف تصویر',
            'para' => 'متن',
            'h2' => 'عنوان بزرگ',
            'h3' => 'عنوان کوچک',
            'linkTitle' => 'لینک',
            'linkPlaceholder' => 'https://example.com',
            'applyLink' => 'اعمال',
            'removeLink' => 'حذف لینک',
            'directionTitle' => 'تغییر جهت متن (فارسی/انگلیسی)',
            'uploadTitle' => 'آپلود تصویر جدید',
            'uploading' => 'در حال آپلود…',
            'altLabel' => 'سئو',
            'altPlaceholder' => 'متن جایگزین تصویر (Alt)…',
            'captionPlaceholder' => 'زیرنویس تصویر (اختیاری)',
        ];

    $layouts = [
        'full' => $labels['full'],
        'center' => $labels['center'],
        'left' => $labels['left'],
        'right' => $labels['right'],
    ];

    $placeholder = $placeholder ?: $labels['placeholder'];
    $assetBase = rtrim((string) (config('app.url') ?? ''), '/');

    $assetsForJs = collect($galleryAssets)->map(fn ($a) => [
        'id' => $a->id,
        'src' => $a->srcAttribute(),
        'thumb' => $a->thumbAttribute() ?: $a->srcAttribute(),
        'alt' => $a->alt,
        'metaTitle' => $a->meta_title,
    ])->values();

    $config = json_encode([
        'prop' => $target,
        'uploadProp' => $uploadProp,
        'event' => $event,
        'dir' => $isEn ? 'ltr' : 'rtl',
        'placeholder' => $placeholder,
        'assetBase' => $assetBase,
        'layouts' => $layouts,
        'galleryAssets' => $assetsForJs,
        'selectedGalleryIds' => $selectedGalleryIds,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
@endphp

@once
    <style>
        [x-cloak] { display: none !important; }

        .uc-rich-editor .uc-tb-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .25rem;
            padding: .38rem .55rem;
            border-radius: .55rem;
            font-size: .75rem;
            font-weight: 700;
            color: #475569;
            background: transparent;
            transition: all .15s ease;
            line-height: 1;
        }
        .uc-rich-editor .uc-tb-btn:hover { background: #f1f5f9; color: #6366f1; }
        .dark .uc-rich-editor .uc-tb-btn { color: #cbd5e1; }
        .dark .uc-rich-editor .uc-tb-btn:hover { background: rgba(99, 102, 241, .15); color: #a5b4fc; }
        .uc-rich-editor .uc-tb-btn-active {
            background: #6366f1 !important;
            color: #fff !important;
            box-shadow: 0 4px 12px -4px rgba(99, 102, 241, .5);
        }
        .uc-rich-editor .uc-tb-big { font-size: .85rem; }
        .uc-rich-editor .uc-tb-mid { font-size: .78rem; opacity: .85; }
        .uc-rich-editor .uc-tb-btn-green { background:#ecfdf5; color:#059669; }
        .uc-rich-editor .uc-tb-btn-green:hover { background:#d1fae5; color:#047857; }
        .dark .uc-rich-editor .uc-tb-btn-green { background: rgba(16, 185, 129, .15); color:#34d399; }

        .uc-rich-editor .uc-editor-surface { position: relative; min-height: 340px; }

        .uc-rich-editor .uc-editable {
            outline: none;
            min-height: 320px;
            padding: 1rem 1.25rem 2rem;
            font-size: 15px;
            line-height: 2.1;
            word-break: break-word;
        }
        .uc-rich-editor .uc-editable:empty::before {
            content: attr(data-placeholder);
            color: #94a3b8;
            pointer-events: none;
        }
        .uc-rich-editor .uc-editable p { margin: 0 0 .75em; }
        .uc-rich-editor .uc-editable h2 { font-size: 1.45em; font-weight: 800; margin: 1em 0 .5em; line-height: 1.6; }
        .uc-rich-editor .uc-editable h3 { font-size: 1.2em; font-weight: 700; margin: .9em 0 .4em; }
        .uc-rich-editor .uc-editable ul,
        .uc-rich-editor .uc-editable ol { padding-inline-start: 1.5em; margin: 0 0 .75em; }
        .uc-rich-editor .uc-editable ul { list-style: disc; }
        .uc-rich-editor .uc-editable ol { list-style: decimal; }
        .uc-rich-editor .uc-editable blockquote {
            border-inline-start: 4px solid #818cf8;
            background: rgba(99, 102, 241, .06);
            padding: .6rem 1rem;
            border-radius: .6rem;
            margin: 0 0 .75em;
            color: #475569;
        }
        .dark .uc-rich-editor .uc-editable blockquote { color: #cbd5e1; background: rgba(99, 102, 241, .12); }
        .uc-rich-editor .uc-editable a { color: #6366f1; text-decoration: underline; text-decoration-style: dashed; }
        .dark .uc-rich-editor .uc-editable a { color: #a5b4fc; }
        .uc-rich-editor .uc-editable small { font-size: .78em; color: #64748b; }
        .dark .uc-rich-editor .uc-editable small { color: #94a3b8; }

        {{-- تصاویر داخل ادیتور با همان لایوت خروجی نهایی رندر می‌شوند (WYSIWYG از app.css) --}}
        .uc-rich-editor .uc-editable figure.uc-media { cursor: pointer; position: relative; }
        .uc-rich-editor .uc-editable figure.uc-media:hover { outline: 2px dashed rgb(14 165 233 / 0.55); outline-offset: 2px; }
        .uc-rich-editor .uc-editable figure.uc-media.uc-drop-flash img { outline: 3px solid #34d399; outline-offset: 2px; }
        .uc-rich-editor .uc-editable figure.uc-media figcaption {
            text-align: center;
            font-size: .82rem;
            color: #64748b;
            padding-top: .45rem;
            outline: none;
            min-height: 1em;
        }
        .uc-rich-editor .uc-editable figure.uc-media figcaption:empty::before {
            content: attr(data-placeholder);
            color: #cbd5e1;
        }
        .uc-rich-editor .uc-editable figure.uc-media[draggable="true"]:hover { outline-style: dashed; }
        .uc-rich-editor .uc-editable::after { content: ""; display: table; clear: both; }

        {{-- سه ناحیهٔ قرارگیری هنگام درگ --}}
        .uc-rich-editor .uc-drop-overlay {
            position: absolute;
            inset: 0;
            z-index: 30;
            display: flex;
            gap: .6rem;
            padding: .8rem;
            background: rgba(255, 255, 255, .75);
            backdrop-filter: blur(2px);
        }
        .dark .uc-rich-editor .uc-drop-overlay { background: rgba(2, 6, 23, .55); }
        .uc-rich-editor .uc-drop-zone {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .4rem;
            border: 2px dashed #a5b4fc;
            border-radius: .9rem;
            font-size: .8rem;
            font-weight: 800;
            color: #6366f1;
            background: rgba(99, 102, 241, .04);
            transition: all .15s ease;
            cursor: move;
        }
        .dark .uc-rich-editor .uc-drop-zone { color: #a5b4fc; border-color: #6366f1; background: rgba(99, 102, 241, .08); }
        .uc-rich-editor .uc-drop-zone-active {
            border-style: solid;
            border-color: #6366f1;
            background: rgba(99, 102, 241, .18);
            transform: scale(.985);
        }

        {{-- منوی شناور تصویر --}}
        .uc-rich-editor .uc-figure-menu { position: absolute; z-index: 60; }
    </style>
@endonce

<div {{ $attributes->merge(['class' => 'uc-rich-editor mb-2', 'dir' => $isEn ? 'ltr' : 'rtl']) }}
     x-data="ucEditor({{ $config }})"
     x-on:{{ $event }}.window="onUpload($event)"
     @click.outside="closeAllOverlays()">

    {{-- فایل آپلود inline --}}
    <input type="file" x-ref="pick" class="hidden uc-file-input"
           wire:model="{{ $uploadProp }}" accept="image/*" multiple>

    {{-- ===================== تولبار ===================== --}}
    <div class="flex flex-wrap items-center gap-1 rounded-t-xl border border-slate-200 bg-gradient-to-t from-slate-50 to-white px-2.5 py-2 shadow-sm dark:border-slate-700 dark:from-slate-800/80 dark:to-slate-800/40">

        {{-- نوع بلوک --}}
        <button type="button" @mousedown.prevent @click="setBlock('<p>')" title="{{ $labels['para'] }}"
                class="uc-tb-btn" :class="currentBlock === 'p' && 'uc-tb-btn-active'">{{ $labels['para'] }}</button>
        <button type="button" @mousedown.prevent @click="setBlock('<h2>')" title="{{ $labels['h2'] }}"
                class="uc-tb-btn uc-tb-big" :class="currentBlock === 'h2' && 'uc-tb-btn-active'">{{ $labels['h2'] }}</button>
        <button type="button" @mousedown.prevent @click="setBlock('<h3>')" title="{{ $labels['h3'] }}"
                class="uc-tb-btn uc-tb-mid" :class="currentBlock === 'h3' && 'uc-tb-btn-active'">{{ $labels['h3'] }}</button>

        <span class="mx-1 h-5 w-px bg-slate-200 dark:bg-slate-700"></span>

        {{-- قالب inline --}}
        <button type="button" @mousedown.prevent @click="exec('bold')" class="uc-tb-btn font-black" :class="bold && 'uc-tb-btn-active'" title="Bold (Ctrl+B)">B</button>
        <button type="button" @mousedown.prevent @click="exec('italic')" class="uc-tb-btn italic" :class="italic && 'uc-tb-btn-active'" title="Italic (Ctrl+I)">I</button>
        <button type="button" @mousedown.prevent @click="exec('underline')" class="uc-tb-btn underline" :class="underline && 'uc-tb-btn-active'" title="Underline (Ctrl+U)">U</button>
        <button type="button" @mousedown.prevent @click="exec('strikeThrough')" class="uc-tb-btn line-through" :class="strike && 'uc-tb-btn-active'" title="Strikethrough">S</button>
        <button type="button" @mousedown.prevent @click="exec('formatBlock','<small>')" class="uc-tb-btn text-[9px]" :class="small && 'uc-tb-btn-active'" title="Small">A⁻</button>

        <span class="mx-1 h-5 w-px bg-slate-200 dark:bg-slate-700"></span>

        {{-- فهرست‌ها و نقل‌قول --}}
        <button type="button" @mousedown.prevent @click="exec('insertUnorderedList')" class="uc-tb-btn" :class="isUl && 'uc-tb-btn-active'" title="• {{ $isEn ? 'List' : 'فهرست نقطه‌ای' }}">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 6h2m-2 6h2m-2 6h2m4-12h10M8 12h10M8 18h10"/></svg>
        </button>
        <button type="button" @mousedown.prevent @click="exec('insertOrderedList')" class="uc-tb-btn" :class="isOl && 'uc-tb-btn-active'" title="1. {{ $isEn ? 'List' : 'فهرست شماره‌دار' }}">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M5 5h1v5m-1 0a1.5 1.5 0 100 3 1.5 1.5 0 000-3zm11-4v16m-8-8h11"/></svg>
        </button>
        <button type="button" @mousedown.prevent @click="exec('formatBlock','<blockquote>')" class="uc-tb-btn" :class="isQuote && 'uc-tb-btn-active'" title="{{ $isEn ? 'Quote' : 'نقل‌قول' }}">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 5h10a2 2 0 012 2v7a2 2 0 01-2 2H9l-4 4z"/></svg>
        </button>

        <span class="mx-1 h-5 w-px bg-slate-200 dark:bg-slate-700"></span>

        {{-- تراز --}}
        <button type="button" @mousedown.prevent @click="setAlign('left')" class="uc-tb-btn" :class="currentAlign === 'left' && 'uc-tb-btn-active'" title="{{ $isEn ? 'Align left' : 'چپ‌چین' }}">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 6h16M4 10h10M4 14h16M4 18h10"/></svg>
        </button>
        <button type="button" @mousedown.prevent @click="setAlign('center')" class="uc-tb-btn" :class="currentAlign === 'center' && 'uc-tb-btn-active'" title="{{ $isEn ? 'Align center' : 'وسط‌چین' }}">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 6h16M7 10h10M4 14h16M7 18h10"/></svg>
        </button>
        <button type="button" @mousedown.prevent @click="setAlign('right')" class="uc-tb-btn" :class="currentAlign === 'right' && 'uc-tb-btn-active'" title="{{ $isEn ? 'Align right' : 'راست‌چین' }}">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 6h16M10 10h10M4 14h16M10 18h10"/></svg>
        </button>

        <span class="mx-1 h-5 w-px bg-slate-200 dark:bg-slate-700"></span>

        {{-- لینک + جهت --}}
        <button type="button" @mousedown.prevent @click="openLinkPopover()" class="uc-tb-btn" :class="linkPopoverOpen && 'uc-tb-btn-active'" title="{{ $labels['linkTitle'] }}">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M13.8 10.2a4 4 0 000 5.6l1.4 1.4a4 4 0 005.6-5.6l-1.4-1.4m-2-2-1.4-1.4a4 4 0 10-5.6 5.6l1.4 1.4"/></svg>
        </button>
        <button type="button" @mousedown.prevent @click="toggleDirection()" class="uc-tb-btn" :class="direction === 'ltr' && 'uc-tb-btn-active'" title="{{ $labels['directionTitle'] }}">
            <span class="text-[10px] font-bold">FA/EN</span>
        </button>

        <span class="mx-1 h-5 w-px bg-slate-200 dark:bg-slate-700"></span>

        {{-- چیدمان پیش‌فرض تصویر --}}
        <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400">{{ $labels['layoutTitle'] }}</span>
        <template x-for="(label, value) in layouts" :key="'tb-' + value">
            <button type="button" @mousedown.prevent @click="setLayout(value)" x-text="label"
                    class="uc-tb-btn" :class="layout === value && 'uc-tb-btn-active'"></button>
        </template>

        <span class="mx-1 h-5 w-px bg-slate-200 dark:bg-slate-700"></span>

        {{-- تصویر: آپلود + گالری --}}
        <button type="button" @mousedown.prevent @click="openInlineUpload()" class="uc-tb-btn uc-tb-btn-green" title="{{ $labels['uploadTitle'] }}">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 16V8m0 0l-3 3m3-3l3 3M4 19h16"/></svg>
        </button>
        <button type="button" @mousedown.prevent @click="openGalleryPicker()" class="uc-tb-btn uc-tb-btn-green" title="{{ $labels['galleryBtn'] }}">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 6a2 2 0 012-2h12a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm2 10l4-4 6 6m1-6a2 2 0 100-4 2 2 0 000 4z"/></svg>
            <span class="text-[10px]">{{ $labels['galleryBtn'] }}</span>
        </button>

        <span wire:loading wire:target="{{ $uploadProp }}" class="ms-1 inline-flex items-center gap-1 text-[11px] font-bold text-primary-600">
            <svg class="h-3.5 w-3.5 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
            {{ $labels['uploading'] }}
        </span>

        <span class="ms-auto hidden text-[10px] font-normal text-slate-400 dark:text-slate-500 xl:inline">{{ $labels['hint'] }}</span>
    </div>

    {{-- پاپ‌آور لینک --}}
    <div x-cloak x-show="linkPopoverOpen" class="mb-2 flex items-center gap-2 rounded-xl border border-slate-200 bg-white p-2 shadow-lg dark:border-slate-700 dark:bg-slate-800">
        <input type="url" x-model="linkUrl" placeholder="{{ $labels['linkPlaceholder'] }}" dir="ltr"
               @keydown.enter.prevent="applyLink()"
               class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs focus:border-primary-400 focus:outline-none dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
        <button type="button" @click="applyLink()" class="rounded-lg bg-primary-500 px-3 py-1.5 text-xs font-bold text-white hover:bg-primary-600">{{ $labels['applyLink'] }}</button>
        <button type="button" @click="removeLink()" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-bold text-slate-600 dark:border-slate-600 dark:text-slate-300">{{ $labels['removeLink'] }}</button>
        <button type="button" @click="linkPopoverOpen = false" class="p-1 text-slate-400 hover:text-slate-600">×</button>
    </div>

    {{-- ===================== سطح ویرایش ===================== --}}
    <div class="uc-editor-surface rounded-b-xl border border-t-0 border-slate-200 bg-white dark:border-slate-700 dark:bg-surface-800" x-ref="surface" wire:ignore>
        <div x-ref="editor" class="uc-editable" contenteditable="true" dir="{{ $isEn ? 'ltr' : 'rtl' }}"></div>

        {{-- سه ناحیهٔ درگ (چپ / وسط / راست) --}}
        <div x-cloak x-show="showDropZones" class="uc-drop-overlay">
            <template x-for="zone in ['left', 'center', 'right']" :key="zone">
                <div class="uc-drop-zone"
                     :class="dropZoneHover === zone && 'uc-drop-zone-active'"
                     @dragover.prevent.stop="hoverDropZone(zone)"
                     @dragleave="dropZoneHover === zone && (dropZoneHover = null)"
                     @drop.prevent.stop="dropFigure(zone, $event)">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 12h16m-5-5 5 5-5 5"/></svg>
                    <span x-text="layouts[zone]"></span>
                </div>
            </template>
        </div>
    </div>
    <p class="mt-1.5 text-[11px] text-slate-400 dark:text-slate-500">{{ $labels['hint'] }}</p>

    {{-- ===================== منوی شناور تصویر ===================== --}}
    <div x-cloak x-show="floatMenuOpen && selectedFigure" class="uc-figure-menu flex items-center gap-0.5 rounded-xl border border-slate-200 bg-white p-1.5 shadow-xl dark:border-slate-600 dark:bg-slate-800"
         :style="{ top: floatMenuAnchor.top + 'px', left: floatMenuAnchor.left + 'px', transform: 'translateX(-50%) translateY(-110%)' }"
         @click.stop>
        <template x-for="(label, value) in layouts" :key="'fm-' + value">
            <button type="button" @click="setFigureLayout(value)" x-text="label"
                    class="rounded-lg px-2.5 py-1.5 text-[11px] font-bold transition"
                    :class="figureLayout() === value ? 'bg-primary-500 text-white shadow' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-700'"></button>
        </template>

        <span class="mx-1 h-5 w-px bg-slate-200 dark:bg-slate-700"></span>

        <button type="button" @click="moveFigure(-1)" title="{{ $labels['moveUp'] }}" class="rounded-lg p-1.5 text-slate-500 transition hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-700">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M5 15l7-7 7 7"/></svg>
        </button>
        <button type="button" @click="moveFigure(1)" title="{{ $labels['moveDown'] }}" class="rounded-lg p-1.5 text-slate-500 transition hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-700">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M19 9l-7 7-7-7"/></svg>
        </button>

        <span class="mx-1 h-5 w-px bg-slate-200 dark:bg-slate-700"></span>

        <button type="button" @click="openReplacePicker()" class="rounded-lg px-2.5 py-1.5 text-[11px] font-bold text-slate-600 transition hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-700">{{ $labels['replaceBtn'] }}</button>
        <button type="button" @click="deleteFigure()" title="{{ $labels['remove'] }}" class="rounded-lg p-1.5 text-rose-500 transition hover:bg-rose-50 dark:hover:bg-rose-900/30">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
        </button>
        <button type="button" @click="closeFigureMenu()" class="absolute -top-1.5 -left-1.5 rounded-full bg-slate-800 p-0.5 text-white shadow">
            <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>

    {{-- ورودی Alt تصویر انتخاب‌شده (زیر منو) — کلاس uc-figure-alt باعث می‌شود کلیک روی آن منو را نبندد --}}
    <div x-cloak x-show="floatMenuOpen && selectedFigure" class="uc-figure-alt mt-2 flex items-center gap-2 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 dark:border-amber-900/50 dark:bg-amber-900/20">
        <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-600 dark:bg-amber-900/40 dark:text-amber-300">{{ $labels['altLabel'] }}</span>
        <input type="text" x-model="figureAlt" @input.debounce.300ms="updateFigureAlt()" placeholder="{{ $labels['altPlaceholder'] }}"
               class="w-full rounded-lg border border-amber-200 bg-white px-3 py-1.5 text-xs focus:border-amber-400 focus:outline-none dark:border-amber-800 dark:bg-slate-800 dark:text-slate-200">
    </div>

    {{-- ===================== مودال گالری ===================== --}}
    <div x-cloak x-show="galleryPickerOpen" class="fixed inset-0 z-[80] flex items-center justify-center bg-black/45 backdrop-blur-sm">
        <div class="max-h-[85vh] w-full max-w-lg mx-4 flex flex-col overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-surface-800" @click.stop @dragover.prevent>
            <div class="flex items-center justify-between border-b border-slate-100 p-4 dark:border-slate-800">
                <h3 class="text-base font-bold">{{ $labels['galleryBtn'] }}</h3>
                <button type="button" @click="closeAllOverlays()" class="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 dark:hover:bg-slate-800">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto p-4">
                <div class="grid grid-cols-3 gap-3">
                    <template x-for="asset in galleryAssets" :key="asset.id">
                        <button type="button" @click="toggleGalleryAsset(asset.id)"
                                class="overflow-hidden rounded-xl border-2 text-start transition"
                                :class="selectedGalleryIds.includes(asset.id) ? 'border-primary-500 ring-2 ring-primary-500/30' : 'border-transparent hover:border-primary-300'">
                            <img :src="asset.thumb" class="h-24 w-full object-cover" :alt="asset.alt">
                            <span class="block px-2 py-1.5 text-[11px] text-slate-600 dark:text-slate-300" x-text="asset.alt || asset.metaTitle || '{{ $isEn ? 'Untitled' : 'بدون عنوان' }}'"></span>
                        </button>
                    </template>
                    <div x-show="!galleryAssets.length" class="col-span-3 py-10 text-center text-sm text-slate-500">
                        <p class="font-bold">{{ $labels['emptyGallery'] }}</p>
                        <p class="mt-1 text-xs">{{ $labels['emptyGalleryHint'] }}</p>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3 border-t border-slate-100 p-4 dark:border-slate-800">
                <button type="button" @click="insertSelectedGalleryImages()" :disabled="!selectedGalleryIds.length"
                        class="flex-1 rounded-xl bg-primary-500 px-4 py-2.5 text-sm font-bold text-white shadow transition hover:bg-primary-600 disabled:cursor-not-allowed disabled:opacity-40">
                    {{ $labels['insertBtn'] }} <span x-show="selectedGalleryIds.length" x-text="'(' + selectedGalleryIds.length + ')'"></span>
                </button>
                <button type="button" @click="closeAllOverlays()" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-bold text-slate-600 dark:border-slate-700 dark:text-slate-300">{{ $labels['closeBtn'] }}</button>
            </div>
        </div>
    </div>

    {{-- ===================== مودال جایگزینی ===================== --}}
    <div x-cloak x-show="replacePickerOpen" class="fixed inset-0 z-[80] flex items-center justify-center bg-black/45 backdrop-blur-sm">
        <div class="max-h-[85vh] w-full max-w-lg mx-4 flex flex-col overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-surface-800" @click.stop @dragover.prevent>
            <div class="flex items-center justify-between border-b border-slate-100 p-4 dark:border-slate-800">
                <h3 class="text-base font-bold">{{ $labels['replaceBtn'] }}</h3>
                <button type="button" @click="closeAllOverlays()" class="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 dark:hover:bg-slate-800">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto p-4">
                <div class="grid grid-cols-3 gap-3">
                    <template x-for="asset in galleryAssets" :key="'rp-' + asset.id">
                        <button type="button" @click="replaceAssetId = asset.id"
                                class="overflow-hidden rounded-xl border-2 transition"
                                :class="String(replaceAssetId) === String(asset.id) ? 'border-primary-500 ring-2 ring-primary-500/30' : 'border-transparent hover:border-primary-300'">
                            <img :src="asset.thumb" class="h-24 w-full object-cover" :alt="asset.alt">
                            <span class="block px-2 py-1.5 text-[11px] text-slate-600 dark:text-slate-300" x-text="asset.alt || asset.metaTitle || '{{ $isEn ? 'Untitled' : 'بدون عنوان' }}'"></span>
                        </button>
                    </template>
                    <div x-show="!galleryAssets.length" class="col-span-3 py-10 text-center text-sm text-slate-500">
                        <p class="font-bold">{{ $labels['emptyGallery'] }}</p>
                        <p class="mt-1 text-xs">{{ $labels['emptyGalleryHint'] }}</p>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3 border-t border-slate-100 p-4 dark:border-slate-800">
                <button type="button" @click="applyReplace()" :disabled="replaceAssetId == null"
                        class="flex-1 rounded-xl bg-primary-500 px-4 py-2.5 text-sm font-bold text-white shadow transition hover:bg-primary-600 disabled:cursor-not-allowed disabled:opacity-40">
                    {{ $labels['replaceBtn'] }}
                </button>
                <button type="button" @click="closeAllOverlays()" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-bold text-slate-600 dark:border-slate-700 dark:text-slate-300">{{ $labels['closeBtn'] }}</button>
            </div>
        </div>
    </div>

    @error($uploadProp)
        <p class="mt-1 text-[11px] font-bold text-rose-600">{{ $message }}</p>
    @enderror
    @error($uploadProp . '.*')
        <p class="mt-1 text-[11px] font-bold text-rose-600">{{ $message }}</p>
    @enderror
</div>
