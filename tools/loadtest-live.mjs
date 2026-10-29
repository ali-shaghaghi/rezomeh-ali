// تست فشار سبک روی سایت زنده — با احتیاط روی هاست اشتراکی (بار ملایم، فقط GET)
// اجرا: node tools/loadtest-live.mjs [base]
const BASE = process.argv[2] || 'https://alishaghaghi.ir';

const PHASES = [
    { path: '/', concurrency: 10, total: 150, label: 'صفحه اصلی' },
    { path: '/blog', concurrency: 10, total: 100, label: 'وبلاگ' },
    { path: '/login', concurrency: 5, total: 50, label: 'ورود' },
];

const pct = (arr, p) => {
    if (!arr.length) return 0;
    const s = [...arr].sort((a, b) => a - b);
    return Math.round(s[Math.min(s.length - 1, Math.floor((p / 100) * s.length))]);
};

async function phase({ path, concurrency, total, label }) {
    const lat = [];
    const codes = {};
    let errors = 0;
    const start = performance.now();
    let done = 0;

    const worker = async () => {
        while (done < total) {
            done++;
            const t0 = performance.now();
            try {
                const r = await fetch(BASE + path, { redirect: 'manual', headers: { 'user-agent': 'loadtest/1.0' } });
                codes[r.status] = (codes[r.status] || 0) + 1;
                await r.arrayBuffer();
            } catch {
                errors++;
            }
            lat.push(performance.now() - t0);
        }
    };

    await Promise.all(Array.from({ length: concurrency }, worker));
    const dur = (performance.now() - start) / 1000;

    console.log(`\n=== ${label} (${path}) — ${total} درخواست، هم‌زمانی ${concurrency} ===`);
    console.log(`زمان کل: ${dur.toFixed(1)}s | RPS: ${(total / dur).toFixed(1)}`);
    console.log(`تاخیر ms — p50: ${pct(lat, 50)} | p95: ${pct(lat, 95)} | p99: ${pct(lat, 99)} | max: ${Math.round(Math.max(...lat))}`);
    console.log(`کدها: ${JSON.stringify(codes)} | خطای شبکه: ${errors}`);
    return { path, dur, rps: total / dur, p50: pct(lat, 50), p95: pct(lat, 95), codes, errors };
}

console.log(`هدف: ${BASE} — ${new Date().toISOString()}`);
const results = [];
for (const ph of PHASES) results.push(await phase(ph));

const ok = results.every((r) => r.errors === 0 && Object.keys(r.codes).every((c) => c.startsWith('2') || c === '302'));
console.log(`\nنتیجه کلی: ${ok ? '✅ پایدار — هیچ خطای شبکه/پاسخ خراب' : '⚠️ خطا دیده شد — بررسی کنید'}`);
