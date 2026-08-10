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

function initDropdowns() {
    document.querySelectorAll('[data-dropdown-toggle]').forEach((btn) => {
        const panelId = btn.getAttribute('data-dropdown-toggle');
        const panel = document.getElementById(panelId);
        if (!panel) return;

        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            const isOpen = panel.classList.contains('open');
            closeAllDropdowns();
            if (!isOpen) {
                panel.classList.add('open');
                btn.setAttribute('aria-expanded', 'true');
            }
        });
    });

    document.querySelectorAll('[data-sidebar-dropdown]').forEach((btn) => {
        const panelId = btn.getAttribute('data-sidebar-dropdown');
        const panel = document.getElementById(panelId);
        if (!panel) return;

        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const open = panel.classList.toggle('open');
            btn.classList.toggle('open', open);
            btn.setAttribute('aria-expanded', open ? 'true' : 'false');
            const chevron = btn.querySelector('[data-icon="chevronDown"]');
            if (chevron) chevron.classList.toggle('rotate-180', open);
        });
    });

    document.addEventListener('click', (e) => {
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
document.addEventListener('DOMContentLoaded', () => {
    // Theme toggle
    document.addEventListener('click', (e) => {
        if (e.target.closest('#theme-toggle, [data-theme-toggle]')) {
            e.preventDefault();
            toggleTheme();
        }
    });

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
        const suffix = el.getAttribute('data-suffix') || '';
        const delay = parseInt(el.getAttribute('data-delay') || '0', 10);
        setTimeout(() => animateValue(el, target, 1800, suffix), delay);
    });

    // Scroll reveal
    document.querySelectorAll('.observe-in').forEach((el) => observer.observe(el));
});