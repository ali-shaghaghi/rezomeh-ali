{{-- اعمال تم قبل از اولین رندر — جلوگیری از فلش لایت‌مود (FOUC) در صفحات دارک --}}
<script>
    (function () {
        var theme = localStorage.getItem('theme') || localStorage.getItem('resume-theme');
        var dark =
            theme === 'dark' ||
            (theme !== 'light' && window.matchMedia('(prefers-color-scheme: dark)').matches);
        document.documentElement.classList.toggle('dark', dark);
        document.documentElement.style.setProperty('color-scheme', dark ? 'dark' : 'light');
    })();
</script>
