{{-- ============================================================
     اسپلش اسکرین لودینگ — لوگوی سایت در مرکز + حلقه‌های متحرک جذاب اما ساده
     • درست بعد از شروع <body> قرار می‌گیرد و محتوا را تا آماده‌شدن صفحه می‌پوشاند.
     • UI هیچ‌وقت قفل نمی‌شود — دو مسیر مستقل دیس‌میس:
       ۱) JS: حداکثر 450ms پس از DOMContentLoaded، لور اضافه می‌رود (کد پایین)
       ۲) Fallback: اگر JS اجرا نشد، CSS بعد از ۷ ثانیه خودش محو می‌کند
     • پس از اولین لود، در ناوبری‌های داخلی Livewire (wire:navigate) دیگر ظاهر
       نمی‌شود (برچسب window.__splashShown) — فقط بار اول هر باز شدن صفحه.
     • prefers-reduced-motion → بی‌حرکت، فقط محو ساده.
     • تم شب/روز پیش از این پارشال با theme-init اعمال شده و اسپلش هم تبعیت می‌کند.
     • CSS/JS مستقل از Tailwind — قبل از لود شدن بیلد هم درست نمایش داده می‌شود.
     • دمو/پیش‌نمایش: با ?splash_demo=1 در URL اسپلش ~۳.۵ ثانیه می‌ماند
       (کد پایین) تا بشود ظاهرش را راحت دید/اسکرین‌شات گرفت.
     ============================================================ --}}
<div id="splash-screen"
     aria-hidden="true">
    <div class="splash-panels"></div>
    <div class="splash-content">
        <div class="splash-badge">
            <img src="{{ site_favicon_url() }}"
                 alt=""
                 width="64" height="64"
                 class="splash-logo"
                 onerror="this.onerror=null;this.src='{{ asset('img/logo.png') }}';">
            <span class="splash-title">{{ \Modules\Settings\Models\Setting::get('site_title', 'علی شقاقی') }}</span>
        </div>
    </div>
</div>

<style>
    /* ---------- ظاهر اسپلش (مستقل از Tailwind تا پیش از بیلد هم کار کند) ---------- */
    #splash-screen {
        position: fixed;
        inset: 0;
        z-index: 2147483000; /* بالاتر از هدر، کشوها و مودال‌ها */
        display: flex;
        align-items: center;
        justify-content: center;
        /* پس‌زمینه متمایز ولی هماهنگ: شب طوسی‌تیره / روز سریتی روشن */
        background: linear-gradient(180deg, #0f172a 0%, #020617 55%, #0f172a 100%);
    }
    html:not(.dark) #splash-screen {
        background: linear-gradient(180deg, #f8fafc 0%, #eef2f7 55%, #eef2f7 100%);
    }

    /* پنل‌های بلندشونده پس‌زمینه (۶ ستون، تأخیر پلکانی — با JS پر می‌شوند) */
    .splash-panels {
        position: absolute;
        inset: 0;
        display: flex;
        pointer-events: none;
    }
    .splash-panel {
        flex: 1;
        background: rgba(99, 102, 241, 0.06);
        transform: scaleY(0);
        transform-origin: bottom;
        animation: splash-panel-rise 2.2s ease-in-out infinite;
    }
    @keyframes splash-panel-rise {
        0%   { transform: scaleY(0);    opacity: 0; }
        35%  { transform: scaleY(1);    opacity: 1; }
        100% { transform: scaleY(0);    opacity: 0; }
    }
    html:not(.dark) .splash-panel {
        background: rgba(99, 102, 241, 0.05);
    }

    .splash-content {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* بج لوگو: لوگو + هاله نور + حلقه ثابت + بازوی چرخان */
    .splash-badge {
        position: relative;
        width: 116px;
        height: 116px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
    .splash-logo {
        width: 64px;
        height: 64px;
        border-radius: 1rem;
        display: block;
        object-fit: contain;
        animation: splash-breathe 2.4s ease-in-out infinite;
    }
    @keyframes splash-breathe {
        0%, 100% { transform: scale(1); opacity: .92; }
        50%      { transform: scale(1.07); opacity: 1; }
    }

    .splash-ring {
        position: absolute;
        inset: 0;
        border-radius: 9999px;
        pointer-events: none;
    }
    .splash-ring-base {
        border: 2px solid rgba(99, 102, 241, 0.18);
        box-shadow: 0 0 22px rgba(99, 102, 241, 0.15) inset;
    }
    .splash-ring-arm {
        border-top: 3px solid #6366f1;
        border-inline-start: 3px solid transparent;
        animation: splash-spin 1.05s linear infinite;
    }
    @keyframes splash-spin {
        to { transform: rotate(360deg); }
    }

    .splash-glow {
        position: absolute;
        inset: -20px;
        border-radius: 9999px;
        background: radial-gradient(circle, rgba(99, 102, 241, 0.28) 0%, transparent 70%);
        filter: blur(14px);
        animation: splash-glow-pulse 2.2s ease-in-out infinite;
    }
    @keyframes splash-glow-pulse {
        0%, 100% { opacity: .5;  transform: scale(1); }
        50%      { opacity: .95; transform: scale(1.1); }
    }

    .splash-title {
        position: absolute;
        bottom: -34px;
        white-space: nowrap;
        font-family: Vazirmatn, Tahoma, sans-serif;
        font-size: 0.95rem;
        font-weight: 700;
        letter-spacing: 0.02em;
        color: #6366f1;
        opacity: .85;
    }
    html:not(.dark) .splash-title { color: #4338ca; }

    /* ---------- دیس‌میس ---------- */
    /* Fallback بدون JS: بعد از ۷ ثانیه با انیمیشن CSS خودش پاک‌سازی می‌شود */
    #splash-screen.splash-armed { animation: splash-timeout 700ms 7s ease forwards; }
    @keyframes splash-timeout {
        from { opacity: 1; }
        to   { opacity: 0; visibility: hidden; pointer-events: none; }
    }
    /* مسیر اصلی JS: محو نرم و کمی بزرگ‌شدن */
    #splash-screen.splash-done {
        animation: splash-out 480ms cubic-bezier(.22,1,.36,1) forwards;
        pointer-events: none;
    }
    @keyframes splash-out {
        from { opacity: 1; transform: scale(1); }
        to   { opacity: 0; transform: scale(1.06); }
    }
    #splash-screen.splash-done .splash-badge {
        animation: splash-badge-exit 480ms cubic-bezier(.22,1,.36,1) forwards;
    }
    @keyframes splash-badge-exit {
        from { transform: translateY(0) scale(1); }
        to   { transform: translateY(-10px) scale(.94); }
    }

    /* ---------- کاهش حرکت ---------- */
    @media (prefers-reduced-motion: reduce) {
        .splash-panel,
        .splash-logo,
        .splash-glow,
        .splash-ring-arm {
            animation: none !important;
        }
        .splash-panel { transform: scaleY(0.4); opacity: .6; }
        .splash-ring-arm { display: none; }
        #splash-screen.splash-armed,
        #splash-screen.splash-done {
            animation-duration: 1ms !important;
            animation-delay: 0ms !important;
        }
    }

    /* ---------- موبایل ---------- */
    @media (max-width: 480px) {
        .splash-badge  { width: 100px; height: 100px; }
        .splash-logo   { width: 56px;  height: 56px; }
        .splash-title  { font-size: .85rem; bottom: -30px; }
    }
</style>

<script>
(function () {
    var splash = document.getElementById('splash-screen');
    if (!splash) return;

    // ناوبری‌های داخلی Livewire: فقط بار اول هر باز کردن صفحه اسپلش بماند
    if (window.__splashShown) { splash.parentNode && splash.parentNode.removeChild(splash); return; }
    window.__splashShown = true;

    function build() {
        var badge = splash.querySelector('.splash-badge');
        if (badge) {
            var glow = document.createElement('div');
            glow.className = 'splash-glow';
            var ringBase = document.createElement('div');
            ringBase.className = 'splash-ring splash-ring-base';
            var ringArm = document.createElement('div');
            ringArm.className = 'splash-ring splash-ring-arm';
            badge.appendChild(glow);
            badge.appendChild(ringBase);
            badge.appendChild(ringArm);
        }

        var panels = splash.querySelector('.splash-panels');
        if (panels && !panels.children.length) {
            for (var i = 0; i < 6; i++) {
                var panel = document.createElement('div');
                panel.className = 'splash-panel';
                panel.style.animationDelay = (i * 0.18).toFixed(2) + 's';
                panels.appendChild(panel);
            }
        }
    }

    var dismissed = false;
    function dismiss() {
        if (dismissed || !splash.parentNode) return;
        dismissed = true;
        splash.classList.remove('splash-armed');
        splash.classList.add('splash-done');
        setTimeout(function () {
            if (splash.parentNode) splash.parentNode.removeChild(splash);
        }, 520);
    }

    // دمو: ?splash_demo=1 → سقف دیس‌میس ~۳.۵ ثانیه است تا ظاهر اسپلش دیده شود
    var demoMode = /[?&]splash_demo=1/.test(window.location.search);

    // سقف سخت 450ms پس از آماده‌شدن DOM — صفحه سریع هم حداقل یک نفس دیده شود
    function schedule() {
        var delay = demoMode || window.location.hash === '#splash' ? 3500 : 450;
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function () {
                setTimeout(dismiss, delay);
            });
        } else {
            setTimeout(dismiss, delay);
        }
    }

    // fallback: اگر این اسکریپت وسط کار خطا خورد، CSS بعد از ۷ ثانیه پاک کند
    splash.classList.add('splash-armed');

    try {
        build();
        schedule();
    } catch (e) {
        /* splash-armed چرخه پاک‌سازی را انجام می‌دهد */
    }
})();
</script>
