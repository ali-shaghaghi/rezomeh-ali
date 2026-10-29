// ویرایشگر متن حرفه‌ای پنل ادمین (ucEditor) — محصولات و بلاگ
import './editor.js';

// Persian number conversion
const faDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
function toPersianNum(n) {
    return String(n).replace(/\d/g, (d) => faDigits[+d]);
}

// Theme management
const html = document.documentElement;

function isDark() {
    return html.classList.contains('dark');
}

function setTheme(dark) {
    html.classList.toggle('dark', !!dark);
    localStorage.setItem('theme', dark ? 'dark' : 'light');
}

function toggleTheme() {
    setTheme(!isDark());
}

// Sidebar management
function openSidebar() {
    document.getElementById('sidebar')?.classList.remove('translate-x-full');
    document.getElementById('sidebar-overlay')?.classList.remove('opacity-0', 'pointer-events-none');
    document.body.classList.add('overflow-hidden');
}

function closeSidebar() {
    document.getElementById('sidebar')?.classList.add('translate-x-full');
    document.getElementById('sidebar-overlay')?.classList.add('opacity-0', 'pointer-events-none');
    document.body.classList.remove('overflow-hidden');
}

// Counter animation
function animateValue(el, end, duration, suffix = '') {
    const startTime = performance.now();
    function tick(now) {
        const progress = Math.min((now - startTime) / duration, 1);
        const eased = 1 - Math.pow(1 - progress, 3);
        const current = Math.floor(end * eased);
        el.textContent = toPersianNum(current.toLocaleString('fa-IR')) + suffix;
        if (progress < 1) requestAnimationFrame(tick);
    }
    requestAnimationFrame(tick);
}

// Dropdown management
function closeAllDropdowns() {
    document.querySelectorAll('.dropdown-panel.open').forEach((p) => p.classList.remove('open'));
    document.querySelectorAll('[data-dropdown-toggle][aria-expanded="true"]').forEach((b) => b.setAttribute('aria-expanded', 'false'));
}

// ponytail: delegate-once handlers — initAdminAndShared() روی DOMContentLoaded و
// livewire:navigated هر دو اجرا می‌شود؛ addEventListener مستقیم روی همان دکمه‌ها
// listenerهای تکراری ثبت می‌کرد و toggle دو بار اجرا می‌شد (کشوها اصلاً باز نمی‌شدند، #۱۶).
// با delegation روی document فقط یک شنونده ثبت می‌شود و دکمه‌های جدید (پس از ناوبری
// Livewire) به‌طور خودکار پوشش داده می‌شوند.
let dropdownDelegated = false;
// شنونده‌های document-level (تم) فقط یک بار ثبت می‌شوند — در غیر این صورت پس از هر
// ناوبری SPA یک شنونده اضافه می‌شد و کلیک تم دو بار toggle می‌کرد (تم ادمین «غیرفعال»
// به نظر می‌رسید چون دو بار پشت‌هم روشن/خاموش می‌شد و به حالت اول برمی‌گشت).
let sharedClickDelegated = false;

function initDropdowns() {
    if (dropdownDelegated) return;
    dropdownDelegated = true;

    // کشوهای هدر (data-dropdown-toggle) — فقط یکی همزمان باز می‌ماند
    document.addEventListener('click', (e) => {
        const toggleBtn = e.target.closest('[data-dropdown-toggle]');
        if (toggleBtn) {
            const panelId = toggleBtn.getAttribute('data-dropdown-toggle');
            const panel = document.getElementById(panelId);
            if (!panel) return;
            const isOpen = panel.classList.contains('open');
            closeAllDropdowns();
            if (!isOpen) {
                panel.classList.add('open');
                toggleBtn.setAttribute('aria-expanded', 'true');
            }
            return;
        }

        // زیرمنوهای سایدبار (data-sidebar-dropdown) — مستقل از هم باز/بسته می‌شوند
        const sidebarBtn = e.target.closest('[data-sidebar-dropdown]');
        if (sidebarBtn) {
            const panelId = sidebarBtn.getAttribute('data-sidebar-dropdown');
            const panel = document.getElementById(panelId);
            if (!panel) return;
            const open = panel.classList.toggle('open');
            sidebarBtn.classList.toggle('open', open);
            sidebarBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
            const chevron = sidebarBtn.querySelector('[data-icon="chevronDown"]');
            if (chevron) chevron.classList.toggle('rotate-180', open);
            return;
        }

        if (!e.target.closest('.dropdown')) closeAllDropdowns();
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeAllDropdowns();
    });
}

// Scroll reveal animation
const observer = new IntersectionObserver(
    (entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.remove('opacity-0-start');
                entry.target.classList.add('animate-slide-up');
                observer.unobserve(entry.target);
            }
        });
    },
    { threshold: 0.1, rootMargin: '0px 0px -40px 0px' }
);

// Initialize when DOM is ready
// ponytail: پس از ناوبری SPA لیewire (مثلاً ورود از صفحه لاگین به پنل ادمین)
// رویداد DOMContentLoaded دوباره شلیک نمی‌شود، بنابراین init باید روی هر دو
// رویداد اجرا شود تا المان‌های پنل ادمین (دراپ‌داون، سایدبار، شمارنده‌ها)
// به‌درستی مقداردهی شوند (#۴).
function initAdminAndShared() {
    // Theme toggle (هر دو کلید عمومی و ادمین از طریق data-theme-toggle کار می‌کنند)
    // فقط یک بار — شنونده document-level بین ناوبری‌ها باقی می‌ماند و
    // ثبت مجدد آن باعث دو بار toggle شدن تم در هر کلیک می‌شد.
    if (!sharedClickDelegated) {
        sharedClickDelegated = true;
        document.addEventListener('click', (e) => {
            if (e.target.closest('#theme-toggle, [data-theme-toggle]')) {
                e.preventDefault();
                toggleTheme();
            }
        });
    }

    // Sidebar
    document.getElementById('open-sidebar')?.addEventListener('click', openSidebar);
    document.getElementById('close-sidebar')?.addEventListener('click', closeSidebar);
    document.getElementById('sidebar-overlay')?.addEventListener('click', closeSidebar);
    window.addEventListener('resize', () => {
        if (window.innerWidth >= 1024) closeSidebar();
    });

    // Dropdowns
    initDropdowns();

    // Counter animations
    document.querySelectorAll('[data-count]').forEach((el) => {
        const target = parseInt(el.getAttribute('data-count'), 10);
        el.dataset.lastCount = target;
        const suffix = el.getAttribute('data-suffix') || '';
        const delay = parseInt(el.getAttribute('data-delay') || '0', 10);
        setTimeout(() => animateValue(el, target, 1800, suffix), delay);
    });

    // Scroll reveal
    document.querySelectorAll('.observe-in').forEach((el) => observer.observe(el));
}

document.addEventListener('DOMContentLoaded', initAdminAndShared);
document.addEventListener('livewire:navigated', initAdminAndShared);

// Re-animate stat counters + بازگرداندن بخش‌های reveal پس از هر morph.
// علت (باگ /admin/resume و کارت‌های آمار ادمین): المان‌های .observe-in با opacity:0
// شروع می‌شوند و IntersectionObserver بعد از نمایان‌شدن، animate-slide-up اضافه و
// خودش را unobserve می‌کند. هوک commit در Livewire «قبل از» morph اجرا می‌شود و
// morph بعداً کلاس‌ها/متن/داده‌های اضافه‌شده توسط JS را با HTML سرور جایگزین می‌کند:
//   → animate-slide-up حذف → المان به opacity:0 برمی‌گردد و نامرئی می‌ماند،
//   → متن شمارنده خالی و data-last-count حذف می‌شود.
// پس کار ترمیم باید «بعد از پایان morph» اجرا شود؛ با double-rAF + setTimeout
// تضمین می‌کنیم بعد از morph هستیم.
function repairAfterMorph() {
    // ۱) المان‌های reveal که کلاس انیمیشن‌شان توسط morph حذف شده
    document.querySelectorAll('.observe-in:not(.animate-slide-up)').forEach((el) => observer.observe(el));

    // ۲) شمارنده‌هایی که morph متن‌شان را خالی کرده یا مقدارشان عوض شده
    document.querySelectorAll('[data-count]').forEach((el) => {
        const target = parseInt(el.getAttribute('data-count'), 10);
        const valueChanged = el.dataset.lastCount === undefined || Number(el.dataset.lastCount) !== target;
        const textWiped = el.textContent.trim() === '';
        if (!valueChanged && !textWiped) return;
        el.dataset.lastCount = target;
        const suffix = el.getAttribute('data-suffix') || '';
        const delay = parseInt(el.getAttribute('data-delay') || '0', 10);
        setTimeout(() => animateValue(el, target, 1800, suffix), delay);
    });
}

if (window.Livewire) {
    window.Livewire.hook('commit', ({ succeed }) => {
        succeed(() => {
            // double-rAF: پس از پایان قطعی morph؛ setTimeout پشتیبان (morph دیر/تاخیری)
            requestAnimationFrame(() => requestAnimationFrame(repairAfterMorph));
            setTimeout(repairAfterMorph, 60);
        });
    });
}

/* ============================================================
   فرانت‌اند عمومی (ماژول Public — فاز ۱۱)
   تبدیل‌شده از main.js قالب res به app.js پروژه
   فقط وقتی اجرا می‌شود که #header فرانت وجود داشته باشد
   ============================================================ */

// heroicons markup — same SVGs as <x-heroicon name="..."> so JS and Blade stay in sync.
const publicIcons = {
    sun: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><path d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z"/></svg>`,
    moon: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><path d="M21.752 15.002A9.72 9.72 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z"/></svg>`,
};

function initPublicFrontend() {
    const header = document.getElementById('header');
    if (!header) return;

    /* ─── Theme Toggle (کلید جدا از ادمین) ─── */
    const PUBLIC_THEME_KEY = 'resume-theme';
    const themeToggle = document.getElementById('theme-toggle');
    // نگه‌دارنده آیکون داخل زیرمنوی کاربر (در صورت وجود)
    const themeToggleIcon = document.getElementById('theme-toggle-icon');

    function getPublicPreferredTheme() {
        const stored = localStorage.getItem(PUBLIC_THEME_KEY);
        if (stored === 'dark' || stored === 'light') return stored;
        return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }

    function applyPublicTheme(theme) {
        html.classList.toggle('dark', theme === 'dark');
        const iconHtml = theme === 'dark' ? publicIcons.sun : publicIcons.moon;
        // آیکون داخل زیرمنوی کاربر (هدر جدید)
        if (themeToggleIcon) themeToggleIcon.innerHTML = iconHtml;
        // هر دو کلید همگام نگه داشته می‌شوند تا تم در پنل ادمین/ورود و فرانت عمومی یکسان بماند
        localStorage.setItem('resume-theme', theme);
        localStorage.setItem('theme', theme);
    }

    applyPublicTheme(getPublicPreferredTheme());

    if (themeToggle) {
        if (themeToggleIcon) themeToggleIcon.innerHTML = html.classList.contains('dark') ? publicIcons.sun : publicIcons.moon;
        themeToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            const next = html.classList.contains('dark') ? 'light' : 'dark';
            html.style.setProperty('color-scheme', next);
            applyPublicTheme(next);
        });
    }

    /* ─── Mobile Menu ─── */
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const mobileMenu = document.getElementById('mobile-menu');

    if (mobileMenuBtn && mobileMenu) {
        let menuOpen = false;

        function setMenuOpen(open) {
            menuOpen = open;
            mobileMenu.classList.toggle('menu-open', open);
            mobileMenuBtn.classList.toggle('menu-open', open);
            // آیکن‌ها در markup هستند (<x-heroicon>) و کلاس .menu-open آن‌ها را سوییچ می‌کند
            mobileMenuBtn.setAttribute('aria-expanded', String(open));
            mobileMenuBtn.setAttribute(
                'aria-label',
                open ? window.__localeText?.closeMenu ?? 'بستن منو'
                     : window.__localeText?.openMenu ?? 'باز کردن منو',
            );
        }

        mobileMenuBtn.addEventListener('click', () => setMenuOpen(!menuOpen));

        // کلیک یا لمس هر جای صفحه بیرون از منو/دکمه → منو بسته می‌شود (#۲۴)
        document.addEventListener('click', (e) => {
            if (!menuOpen) return;
            if (e.target.closest('#mobile-menu-btn') || e.target.closest('#mobile-menu')) return;
            setMenuOpen(false);
        });

        // لمس روی موبایل (touchstart) — همان رفتار کلیک
        document.addEventListener('touchstart', (e) => {
            if (!menuOpen) return;
            if (e.target.closest('#mobile-menu-btn') || e.target.closest('#mobile-menu')) return;
            setMenuOpen(false);
        }, { passive: true });

        // بستن منو با کلید Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && menuOpen) setMenuOpen(false);
        });

        const mobileNavLinks = document.getElementById('mobile-nav-links');
        if (mobileNavLinks) {
            mobileNavLinks.addEventListener('click', (e) => {
                if (e.target.closest('a')) setMenuOpen(false);
            });
        }
    }

    /* ─── Sticky Header ─── */
    window.addEventListener(
        'scroll',
        () => {
            header.classList.toggle('header-scrolled', window.scrollY > 20);
        },
        { passive: true }
    );

    /* ─── Active Nav Link ─── */
    const sections = document.querySelectorAll('section[id]');
    const navLinks = document.querySelectorAll('#header .nav-link');

    function setActiveNav(id) {
        navLinks.forEach((link) => {
            link.classList.toggle('nav-link-active', link.dataset.section === id);
        });
    }

    const navObserver = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) setActiveNav(entry.target.id);
            });
        },
        { rootMargin: '-35% 0px -45% 0px', threshold: 0 }
    );

    sections.forEach((section) => navObserver.observe(section));

    /* ─── Smooth Scroll (فقط لینک‌های داخلی #) ─── */
    document.addEventListener('click', (e) => {
        const anchor = e.target.closest('a[href^="#"], a[href*="#"]');
        if (!anchor) return;

        const url = new URL(anchor.href, window.location.href);
        if (url.pathname !== window.location.pathname) return;

        const targetId = url.hash.slice(1);
        if (!targetId) return;

        const target = document.getElementById(targetId);
        if (!target) return;

        e.preventDefault();
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        history.pushState(null, '', `#${targetId}`);
    });

    /* ─── Scroll Reveal ─── */
    const revealObserver = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('visible');

                const progressFill = entry.target.querySelector('.progress-fill');
                if (progressFill) {
                    requestAnimationFrame(() => progressFill.classList.add('animate'));
                }

                revealObserver.unobserve(entry.target);
            });
        },
        { threshold: 0.15, rootMargin: '0px 0px -40px 0px' }
    );

    document.querySelectorAll('.reveal').forEach((el) => revealObserver.observe(el));

    /* ─── نوارهای پیشرفت مستقل (والدش visible است) ─── */
    const progressObserver = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('animate');
                progressObserver.unobserve(entry.target);
            });
        },
        { threshold: 0.5 }
    );

    document.querySelectorAll('.progress-fill').forEach((bar) => progressObserver.observe(bar));

    /* ─── Typewriter Effect ─── */
    const typewriterEl = document.getElementById('typewriter');
    const source = document.querySelector('script[data-typewriter]');

    if (typewriterEl && source) {
        let texts = [];
        try {
            texts = JSON.parse(source.textContent);
        } catch {
            texts = [];
        }

        if (Array.isArray(texts) && texts.length > 0) {
            let textIndex = 0;
            let charIndex = 0;
            let isDeleting = false;
            let typeSpeed = 80;

            function typeWriter() {
                const current = texts[textIndex];
                if (!current) return;

                if (!isDeleting) {
                    typewriterEl.textContent = current.substring(0, charIndex + 1);
                    charIndex++;

                    if (charIndex === current.length) {
                        isDeleting = true;
                        typeSpeed = 2000;
                    } else {
                        typeSpeed = 80;
                    }
                } else {
                    typewriterEl.textContent = current.substring(0, charIndex - 1);
                    charIndex--;

                    if (charIndex === 0) {
                        isDeleting = false;
                        textIndex = (textIndex + 1) % texts.length;
                        typeSpeed = 500;
                    } else {
                        typeSpeed = 40;
                    }
                }

                setTimeout(typeWriter, typeSpeed);
            }

            setTimeout(typeWriter, 1000);
        }
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPublicFrontend);
} else {
    initPublicFrontend();
}

/* ============================================================
   حفظ تم شب/روز در ناوبری Livewire SPA (#باگ تم)
   ------------------------------------------------------------
   Livewire هنگام ناوبری SPA اتریبیوت‌های <html> صفحه جدید را
   جایگزین می‌کند و کلاس dark حذف می‌شود؛ اسکریپت theme-init هم
   فقط در بارگذاری کامل صفحه اجرا می‌شود. این شنونده بعد از هر
   ناوبری، تم ذخیره‌شده را دوباره اعمال می‌کند — بدون پیش‌فرض
   سیستم‌عامل؛ فقط مقدار صریح ذخیره‌شده (یا عدم تغییر).
   ============================================================ */
document.addEventListener('livewire:navigated', () => {
    const stored = localStorage.getItem('theme') || localStorage.getItem('resume-theme');
    if (stored === 'dark' || stored === 'light') {
        document.documentElement.classList.toggle('dark', stored === 'dark');
        document.documentElement.style.setProperty('color-scheme', stored);
    }
});
