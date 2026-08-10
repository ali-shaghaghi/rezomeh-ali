@vite(['resources/css/app.css'])

<script>
    (function () {
        var theme = localStorage.getItem('theme');
        var dark =
            theme === 'dark' ||
            (theme !== 'light' && window.matchMedia('(prefers-color-scheme: dark)').matches);
        document.documentElement.classList.toggle('dark', dark);
    })();
</script>