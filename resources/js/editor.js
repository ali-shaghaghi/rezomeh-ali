// ویرایشگر متن پنل ادمین — ucEditor (Alpine x-data، بدون Quill)
//
// - خروجی: HTML معیار؛ تصاویر داخل <figure class="uc-media uc-media-{layout}"> ذخیره می‌شوند
//   تا در پابلیک با همان لایوت‌ها (کامل/وسط/چپ/راست) رندر شوند (render_inline_media / app.css).
// - توکن قدیمی [[media|لایوت|storage/...]] هنگام بارگذاری به همان figure تبدیل می‌شود.
// - آپلود inline: ورودی پنهان x-ref="pick" با wire:model؛ نتیجه از رویداد Livewire
//   (inline-media-uploaded / inline-media-uploaded-en) با payload urls یا assets می‌آید.
// - درج از گالری، منوی شناور روی تصویر (چیدمان، حرکت، جایگزینی، Alt، حذف) و درگ‌اند‌دراپ
//   با سه ناحیهٔ خط‌چین (چپ/وسط/راست).

function ucEditor(config) {
    const store = {
        // ----- config -----
        prop: (config && config.prop) || 'content',
        placeholder: (config && config.placeholder) || 'متن خود را بنویسید…',
        layouts: (config && config.layouts) || { full: 'کامل', center: 'وسط', left: 'چپ', right: 'راست' },
        galleryAssets: Array.isArray(config && config.galleryAssets) ? config.galleryAssets.slice() : [],
        assetBase: (config && config.assetBase) || '',

        // ----- state -----
        direction: (config && config.dir) || 'rtl',
        layout: 'full',
        galleryPickerOpen: false,
        selectedGalleryIds: [],
        replacePickerOpen: false,
        replaceAssetId: null,
        linkPopoverOpen: false,
        linkUrl: '',
        // بازهٔ متن انتخاب‌شده هنگام باز شدن پاپ‌آور لینک — تا «اعمال» روی همان متن انجام شود
        linkRange: null,
        uploading: false,

        // figure ها
        selectedFigure: null,
        figureAlt: '',
        floatMenuOpen: false,
        floatMenuAnchor: { top: 0, left: 0 },

        // درگ‌اند‌دراپ ۳ ناحیه
        dragFigure: null,
        showDropZones: false,
        dropZoneHover: null,

        // وضعیت قالب‌بندی (برای هایلایت دکمه‌ها)
        bold: false, italic: false, underline: false, strike: false, small: false,
        isUl: false, isOl: false, isQuote: false,
        currentBlock: 'p',
        currentAlign: 'left',

        syncTimer: null,
        _ucBooted: false,

        // ============================================================
        // راه‌اندازی
        // ============================================================
        init() {
            if (this._ucBooted) return;
            this._ucBooted = true;

            const editor = this.$refs.editor;
            if (!editor) return;

            // جهت از config یا attribute روی wrapper
            const rootDir = this.$root && this.$root.getAttribute && this.$root.getAttribute('dir');
            this.direction = this.direction || rootDir || 'rtl';

            // محتوای اولیه از پراپرتی Livewire (تبدیل توکن قدیمی + پاک‌سازی)
            let initial = '';
            try { initial = this.$wire.get(this.prop) || ''; } catch (e) { initial = ''; }
            if (typeof initial === 'string' && initial.trim() !== '') {
                editor.innerHTML = this.sanitizeHtml(this.toEditorHtml(initial));
            }
            editor.setAttribute('dir', this.direction);
            editor.dataset.placeholder = this.placeholder;

            // رویدادهای ویرایش
            editor.addEventListener('input', () => { this.markDirty(); this.syncSelectionState(); });
            document.addEventListener('selectionchange', () => this.syncSelectionState());

            // کلیک روی تصویر → منوی شناور
            editor.addEventListener('click', (e) => {
                const img = e.target.closest('img');
                if (img) {
                    e.preventDefault();
                    this.selectFigure(img.closest('figure') || img.parentElement, img);
                }
            });

            // لمس طولانی (موبایل) → منوی شناور
            let pressTimer = null;
            editor.addEventListener('pointerdown', (e) => {
                const img = e.target.closest('img');
                if (!img || window.innerWidth > 768) return;
                pressTimer = setTimeout(() => {
                    this.selectFigure(img.closest('figure') || img.parentElement, img);
                    if (navigator.vibrate) navigator.vibrate(15);
                }, 450);
            }, { passive: true });
            ['pointerup', 'pointerleave', 'pointercancel'].forEach((t) =>
                editor.addEventListener(t, () => { if (pressTimer) { clearTimeout(pressTimer); pressTimer = null; } }, { passive: true })
            );

            // انداختن فایل تصویر روی سطح ادیتور → آپلود inline
            const surface = this.$refs.surface;
            if (surface) {
                surface.addEventListener('dragover', (e) => { if (this.hasFiles(e)) e.preventDefault(); });
                surface.addEventListener('drop', (e) => {
                    if (!this.hasFiles(e)) return;
                    e.preventDefault();
                    e.stopPropagation();
                    this.saveCaretFromPoint(e.clientX, e.clientY);
                    this.sendFiles(e.dataTransfer.files);
                });
            }

            // درگ figure (تصویر) → نمایش سه ناحیهٔ قرارگیری؛ درگ متن/انتخاب عادی دست نخورده می‌ماند
            editor.addEventListener('dragstart', (e) => {
                const fig = e.target && e.target.closest ? e.target.closest('figure') : null;
                if (fig && editor.contains(fig)) this.startFigureDrag(e);
            });
            editor.addEventListener('dragend', () => { if (this.dragFigure) this.endFigureDrag(); });

            // بستن لایه‌ها با Escape
            window.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') this.closeAllOverlays();
            });

            // بستن منوی شناور با کلیک بیرون (کلیک روی خودِ منو یا ورودی Alt آن باز نگه‌اش می‌دارد)
            document.addEventListener('click', (e) => {
                if (this.floatMenuOpen && !e.target.closest('.uc-media') && !e.target.closest('.uc-figure-menu') && !e.target.closest('.uc-figure-alt')) {
                    this.closeFigureMenu();
                }
            });

            this.syncSelectionState();
        },

        // ============================================================
        // ابزارها
        // ============================================================
        editor() { return this.$refs.editor; },

        sanitizeHtml(html) {
            const tpl = document.createElement('template');
            tpl.innerHTML = html;
            tpl.content.querySelectorAll('script,iframe,object,embed,link,meta,style').forEach((n) => n.remove());
            tpl.content.querySelectorAll('*').forEach((n) => {
                [...n.attributes].forEach((attr) => {
                    const name = attr.name.toLowerCase();
                    if (name.startsWith('on')) n.removeAttribute(attr.name);
                    if (/^(href|src)$/.test(name) && /^\s*javascript:/i.test(attr.value)) n.removeAttribute(attr.name);
                });
            });
            return tpl.innerHTML;
        },

        /** توکن قدیمی [[media|لایوت|storage/...]] → figure معیار؛ متن خام → HTML */
        toEditorHtml(raw) {
            let s = String(raw);

            s = s.replace(
                /\[\[media\|(full|center|left|right)\|(storage\/[A-Za-z0-9_\-.\/]+)\]\]/g,
                (m, layout, path) =>
                    `<figure class="uc-media uc-media-${layout}"><img src="/${path}" alt="" loading="lazy"></figure>`
            );

            const hasHtml = /<[a-z][^>]*>/i.test(s);
            if (!hasHtml && s.trim() !== '') {
                const escaped = s
                    .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                    .replace(/\r\n/g, '\n').replace(/\n/g, '<br>');
                s = `<p>${escaped}</p>`;
            }

            return s;
        },

        hasFiles(e) {
            const dt = e.dataTransfer;
            if (!dt) return false;
            if (dt.types && Array.from(dt.types).includes('Files')) {
                return Array.from(dt.files).some((f) => f.type && f.type.startsWith('image/'));
            }
            return false;
        },

        // ============================================================
        // جهت متن (فارسی/انگلیسی)
        // ============================================================
        toggleDirection() {
            this.direction = this.direction === 'rtl' ? 'ltr' : 'rtl';
            this.editor().setAttribute('dir', this.direction);
            this.markDirty();
        },

        // ============================================================
        // همگام‌سازی با Livewire
        // ============================================================
        emitHtml() {            let html = this.sanitizeHtml(this.editor().innerHTML);
            html = html.replace(/&nbsp;/g, ' ').replace(/\u00a0/g, ' ');
            // لینک‌های absolute سایت → مسیر نسبی برای ذخیره در دیتابیس
            if (this.assetBase) html = html.split(this.assetBase).join('');
            if (this.isBlank(html)) html = '';
            return html;
        },

        isBlank(html) {            const text = html
                .replace(/<[^>]*>/g, '')
                .replace(/&nbsp;/g, ' ')
                .replace(/[\s\u200B\u200C\uFEFF]/g, '');
            return text === '' && !/<figure|<img/i.test(text + html);
        },

        markDirty() {
            clearTimeout(this.syncTimer);
            this.syncTimer = setTimeout(() => {
                try { this.$wire.set(this.prop, this.emitHtml(), true); } catch (e) { /* no-op */ }
            }, 350);
        },

        flushSync() {
            clearTimeout(this.syncTimer);
            try { this.$wire.set(this.prop, this.emitHtml(), true); } catch (e) { /* no-op */ }
        },

        // ============================================================
        // انتخاب و قالب‌بندی
        // ============================================================
        syncSelectionState() {
            const sel = window.getSelection();
            const inside = sel && sel.rangeCount && this.editor().contains(sel.anchorNode);
            if (inside) this.pendingCaretRange = sel.getRangeAt(0).cloneRange();

            let bold = false, italic = false, underline = false, strike = false, small = false;
            let isUl = false, isOl = false, isQuote = false;
            let block = 'p';
            let align = this.direction === 'rtl' ? 'right' : 'left';

            if (inside) {
                try {
                    bold = document.queryCommandState('bold');
                    italic = document.queryCommandState('italic');
                    underline = document.queryCommandState('underline');
                    strike = document.queryCommandState('strikeThrough');
                } catch (e) { /* no-op */ }

                const parent = sel.getRangeAt(0).startContainer;
                let node = parent.nodeType === 3 ? parent.parentElement : parent;
                const root = this.editor();
                while (node && node !== root) {
                    const tag = node.tagName;
                    if (tag === 'H2') block = 'h2';
                    if (tag === 'H3') block = 'h3';
                    if (tag === 'UL') isUl = true;
                    if (tag === 'OL') isOl = true;
                    if (tag === 'BLOCKQUOTE') isQuote = true;
                    if (tag === 'SMALL') small = true;
                    node = node.parentElement;
                }
                const el = sel.anchorNode.nodeType === 1 ? sel.anchorNode : sel.anchorNode.parentElement;
                const cs = window.getComputedStyle(el || root);
                if (cs.textAlign === 'center') align = 'center';
                else if (cs.textAlign === 'right') align = 'right';
                else if (cs.textAlign === 'justify') align = 'justify';
            }

            this.bold = bold; this.italic = italic; this.underline = underline;
            this.strike = strike; this.small = small;
            this.isUl = isUl; this.isOl = isOl; this.isQuote = isQuote;
            this.currentBlock = block;
            this.currentAlign = align;
        },

        exec(cmd, value) {
            this.restoreCaret();
            document.execCommand(cmd, false, value || null);
            this.syncSelectionState();
            this.markDirty();
        },

        restoreCaret() {
            this.editor().focus({ preventScroll: true });
            const sel = window.getSelection();
            const inside = sel && sel.rangeCount && this.editor().contains(sel.getRangeAt(0).startContainer);
            if (!inside && this.pendingCaretRange) {
                try { sel.removeAllRanges(); sel.addRange(this.pendingCaretRange); } catch (e) { /* no-op */ }
            }
        },

        saveCaretFromPoint(x, y) {
            try {
                if (document.caretRangeFromPoint) this.pendingCaretRange = document.caretRangeFromPoint(x, y);
                else {
                    const pos = document.caretPositionFromPoint(x, y);
                    if (pos) {
                        const r = document.createRange();
                        r.setStart(pos.offsetNode, pos.offset);
                        this.pendingCaretRange = r;
                    }
                }
            } catch (e) { /* no-op */ }
        },

        setBlock(tag) { this.exec('formatBlock', tag); },
        setAlign(mode) {
            const map = { left: 'justifyLeft', center: 'justifyCenter', right: 'justifyRight', justify: 'justifyFull' };
            this.exec(map[mode]);
        },

        // ============================================================
        // لینک
        // ============================================================
        openLinkPopover() {
            this.restoreCaret();

            // ذخیرهٔ دقیق بازهٔ انتخاب‌شده — تا پس از فوکوس روی ورودی URL، لینک روی همان متن اعمال شود
            this.linkRange = null;
            const sel = window.getSelection();
            if (sel && sel.rangeCount && this.editor().contains(sel.getRangeAt(0).startContainer)) {
                this.linkRange = sel.getRangeAt(0).cloneRange();
            } else if (this.pendingCaretRange && this.editor().contains(this.pendingCaretRange.startContainer)) {
                this.linkRange = this.pendingCaretRange.cloneRange();
            }

            this.linkUrl = '';
            if (this.linkRange) {
                let node = this.linkRange.startContainer;
                while (node && node !== this.editor()) {
                    if (node.nodeType === 1 && node.tagName === 'A') { this.linkUrl = node.getAttribute('href') || ''; break; }
                    node = node.parentElement;
                }
            }
            this.linkPopoverOpen = true;
        },

        applyLink() {
            const url = (this.linkUrl || '').trim();
            if (url === '') { this.linkPopoverOpen = false; this.linkRange = null; return; }

            const root = this.editor();
            root.focus({ preventScroll: true });

            // بازگرداندن دقیق بازهٔ ذخیره‌شده به انتخاب فعلی
            const sel = window.getSelection();
            if (this.linkRange && root.contains(this.linkRange.startContainer)) {
                sel.removeAllRanges();
                sel.addRange(this.linkRange);
            }

            const inEditor = sel && sel.rangeCount && root.contains(sel.getRangeAt(0).startContainer);
            const hasText = inEditor && !sel.getRangeAt(0).collapsed;

            if (hasText) {
                // متن انتخاب‌شده حفظ می‌شود و لینک روی همان متن می‌نشیند
                document.execCommand('createLink', false, url);
            } else {
                // بدون متن انتخاب‌شده: لینکی با متنِ همان آدرس درج می‌شود
                const a = document.createElement('a');
                a.href = url;
                a.textContent = url;
                const r = inEditor ? sel.getRangeAt(0) : (this.pendingCaretRange || null);
                if (r && root.contains(r.startContainer)) {
                    r.deleteContents();
                    r.insertNode(a);
                    const after = document.createRange();
                    after.setStartAfter(a);
                    after.collapse(true);
                    sel.removeAllRanges();
                    sel.addRange(after);
                } else {
                    root.appendChild(a);
                }
            }

            this.linkPopoverOpen = false;
            this.linkRange = null;
            this.markDirty();
            this.syncSelectionState();
        },

        removeLink() {
            const root = this.editor();
            root.focus({ preventScroll: true });
            const sel = window.getSelection();
            if (this.linkRange && root.contains(this.linkRange.startContainer)) {
                sel.removeAllRanges();
                sel.addRange(this.linkRange);
            }
            document.execCommand('unlink', false, null);
            this.linkPopoverOpen = false;
            this.linkRange = null;
            this.markDirty();
        },

        // ============================================================
        // گالری: انتخاب و درج
        // ============================================================
        openGalleryPicker() {
            this.restoreCaret();
            this.selectedGalleryIds = [];
            this.galleryPickerOpen = true;
        },

        toggleGalleryAsset(id) {
            const i = this.selectedGalleryIds.indexOf(id);
            if (i === -1) this.selectedGalleryIds.push(id);
            else this.selectedGalleryIds.splice(i, 1);
        },

        assetById(id) { return this.galleryAssets.find((a) => String(a.id) === String(id)); },

        insertSelectedGalleryImages() {
            const ids = [...this.selectedGalleryIds];
            ids.forEach((id) => {
                const asset = this.assetById(id);
                if (asset) this.insertFigure(asset);
            });
            this.galleryPickerOpen = false;
            this.selectedGalleryIds = [];
            this.markDirty();
        },

        // ============================================================
        // figure (uc-media — همان فرمت خروجی نهایی سایت)
        // ============================================================
        figureClass(layout) { return 'uc-media uc-media-' + layout; },

        insertFigure(asset) {
            const root = this.editor();
            this.restoreCaret();

            const fig = document.createElement('figure');
            fig.className = this.figureClass(this.layout || 'full');
            fig.setAttribute('draggable', 'true');

            const img = document.createElement('img');
            img.src = asset.thumb || asset.src;
            img.alt = asset.alt || '';
            img.setAttribute('data-media-id', asset.id);
            img.setAttribute('draggable', 'false');
            img.setAttribute('loading', 'lazy');
            fig.appendChild(img);

            const cap = document.createElement('figcaption');
            cap.contentEditable = 'true';
            cap.dataset.placeholder = 'زیرنویس تصویر (اختیاری)';
            fig.appendChild(cap);

            // درج در مکان مکان‌نما (یا انتهای ادیتور)
            const sel = window.getSelection();
            const range = (sel && sel.rangeCount && root.contains(sel.getRangeAt(0).startContainer))
                ? sel.getRangeAt(0)
                : (this.pendingCaretRange || null);

            if (range && root.contains(range.startContainer)) {
                let node = range.startContainer;
                if (node.nodeType === 3) node = node.parentElement;
                if (node && root.contains(node)) {
                    // بالاترین فرزند مستقیم ادیتور (برای درج به‌عنوان هم‌سطح)
                    let topNode = node;
                    while (topNode.parentElement && topNode.parentElement !== root) topNode = topNode.parentElement;
                    root.insertBefore(fig, topNode.nextElementSibling);
                } else {
                    root.appendChild(fig);
                }
            } else {
                root.appendChild(fig);
            }

            // figure نباید داخل p بماند
            if (fig.parentElement && fig.parentElement.tagName === 'P') fig.parentElement.after(fig);
            this.ensureTrailingParagraph(fig);

            this.selectFigure(fig);
            this.markDirty();
        },

        ensureTrailingParagraph(fig) {
            const next = fig.nextElementSibling;
            if (!next || (next.tagName !== 'P' && next.tagName !== 'H2' && next.tagName !== 'H3')) {
                const p = document.createElement('p');
                p.innerHTML = '<br>';
                fig.after(p);
            }
        },

        selectFigure(fig, imgEl) {
            if (!fig) return;
            this.selectedFigure = fig;
            this.figureAlt = (imgEl || fig.querySelector('img'))?.alt || '';
            const rect = fig.getBoundingClientRect();
            this.floatMenuAnchor = {
                top: Math.max(8, rect.top + window.scrollY),
                left: rect.left + rect.width / 2 + window.scrollX,
            };
            this.floatMenuOpen = true;
        },

        closeFigureMenu() {
            this.floatMenuOpen = false;
            this.selectedFigure = null;
            this.figureAlt = '';
        },

        figureLayout() {
            if (!this.selectedFigure) return this.layout || 'full';
            const matched = (this.selectedFigure.className || '').match(/uc-media-([a-z]+)/);
            return matched ? matched[1] : 'full';
        },

        applyLayoutToFigure(fig, layout) {
            if (!fig) return;
            const LAYOUTS = ['full', 'center', 'left', 'right'];
            LAYOUTS.forEach((l) => fig.classList.remove('uc-media-' + l));
            fig.classList.add('uc-media-' + (LAYOUTS.includes(layout) ? layout : 'full'));
        },

        /** چیدمان پیش‌فرض برای درج بعدی + اعمال روی تصویر انتخاب‌شده (در صورت وجود) */
        setLayout(value) {
            const LAYOUTS = ['full', 'center', 'left', 'right'];
            if (!LAYOUTS.includes(value)) return;
            this.layout = value;
            if (this.selectedFigure) {
                this.applyLayoutToFigure(this.selectedFigure, value);
                this.markDirty();
            }
        },

        setFigureLayout(layout) {
            if (!this.selectedFigure) return;
            this.applyLayoutToFigure(this.selectedFigure, layout);
            this.markDirty();
        },

        moveFigure(dir) {
            const fig = this.selectedFigure;
            if (!fig) return;
            if (dir < 0 && fig.previousElementSibling) fig.parentElement.insertBefore(fig, fig.previousElementSibling);
            if (dir > 0 && fig.nextElementSibling) fig.parentElement.insertBefore(fig.nextElementSibling, fig);
            const rect = fig.getBoundingClientRect();
            this.floatMenuAnchor = { top: rect.top + window.scrollY, left: rect.left + rect.width / 2 + window.scrollX };
            this.markDirty();
        },

        deleteFigure() {
            if (!this.selectedFigure) return;
            this.selectedFigure.remove();
            this.closeFigureMenu();
            this.markDirty();
        },

        updateFigureAlt() {
            if (!this.selectedFigure) return;
            const img = this.selectedFigure.querySelector('img');
            if (img) img.alt = this.figureAlt;
            this.markDirty();
        },

        openReplacePicker() {
            if (!this.selectedFigure) return;
            this.replaceAssetId = null;
            this.replacePickerOpen = true;
        },

        applyReplace() {
            const asset = this.assetById(this.replaceAssetId);
            const fig = this.selectedFigure;
            if (!asset || !fig) return;
            const img = fig.querySelector('img');
            if (img) {
                img.src = asset.thumb || asset.src;
                img.alt = asset.alt || '';
                img.setAttribute('data-media-id', asset.id);
            }
            this.replacePickerOpen = false;
            this.replaceAssetId = null;
            this.markDirty();
        },

        // ============================================================
        // درگ‌اند‌دراپ: سه ناحیهٔ قرارگیری
        // ============================================================
        startFigureDrag(e) {
            const fig = e.target.closest('figure');
            if (!fig) { e.preventDefault(); return; }
            this.dragFigure = fig;
            this.showDropZones = true;
            this.closeFigureMenu();
            try {
                e.dataTransfer.setData('text/plain', 'uc-media');
                e.dataTransfer.effectAllowed = 'move';
            } catch (err) { /* no-op */ }
        },

        hoverDropZone(zone) {
            this.dropZoneHover = zone;
            if (this.dragFigure) this.applyLayoutToFigure(this.dragFigure, zone);
        },

        dropFigure(zone, e) {
            e.preventDefault();
            e.stopPropagation();
            if (this.dragFigure) {
                this.dragFigure.classList.add('uc-drop-flash');
                const f = this.dragFigure;
                setTimeout(() => f.classList.remove('uc-drop-flash'), 700);
                this.applyLayoutToFigure(f, zone);
            }
            this.showDropZones = false;
            this.dropZoneHover = null;
            this.dragFigure = null;
            this.markDirty();
        },

        endFigureDrag() {
            this.showDropZones = false;
            this.dropZoneHover = null;
            this.dragFigure = null;
            this.markDirty();
        },

        // ============================================================
        // آپلود inline
        // ============================================================
        openInlineUpload() {
            this.restoreCaret();
            this.$refs.pick.click();
        },

        sendFiles(fileList) {
            const files = Array.from(fileList || []).filter((f) => f.type && f.type.startsWith('image/'));
            if (!files.length) return;
            this.uploading = true;
            try {
                const input = this.$refs.pick;
                const dt = new DataTransfer();
                files.forEach((f) => dt.items.add(f));
                input.files = dt.files;
                input.dispatchEvent(new Event('change', { bubbles: true }));
            } catch (err) {
                this.uploading = false;
            }
        },

        /**
         * رویداد Livewire پس از آپلود موفق — از طریق x-on:{event}.window در کامپوننت
         * فراخوانی می‌شود. payload ممکن است urls (رشته) یا assets (شیء) باشد.
         */
        onUpload(e) {
            const detail = (e && e.detail && e.detail.params) ? e.detail.params : (e && e.detail) || {};
            this.uploading = false;

            let assets = [];
            if (Array.isArray(detail.assets) && detail.assets.length) {
                assets = detail.assets;
            } else if (Array.isArray(detail.urls) && detail.urls.length) {
                assets = detail.urls.map((url) => ({ id: url, src: url, thumb: url, alt: '' }));
            }

            if (!assets.length) return;

            assets.forEach((asset) => {
                if (!this.assetById(asset.id)) this.galleryAssets.push(asset);
                this.insertFigure(asset);
            });

            // پاک کردن input فایل تا انتخاب دوبارهٔ همان فایل کار کند
            if (this.$refs.pick) this.$refs.pick.value = '';
            this.markDirty();
        },

        // ============================================================
        // بستن همه
        // ============================================================
        closeAllOverlays() {
            this.galleryPickerOpen = false;
            this.selectedGalleryIds = [];
            this.replacePickerOpen = false;
            this.replaceAssetId = null;
            this.linkPopoverOpen = false;
            this.linkRange = null;
            this.floatMenuOpen = false;
            this.showDropZones = false;
            this.dropZoneHover = null;
        },
    };

    return store;
}

function register(Alpine) {
    Alpine.data('ucEditor', ucEditor);
}

if (window.Alpine) {
    register(window.Alpine);
} else {
    document.addEventListener('alpine:init', () => register(window.Alpine));
}
