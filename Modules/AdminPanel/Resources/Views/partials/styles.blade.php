@vite(['resources/css/app.css'])

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

{{--livewire-navigate-preserve-dark--}}
<script>
    //Livewire SPA navigation replaces the <html> attributes of the new page,
    //which removes the dark class and causes the panel to render in light
    //mode for a moment (and the inline theme script above does not re-run).
    //Here we restore the class right after the navigation is completed.
    document.addEventListener('livewire:navigated', function () {
        var stored = localStorage.getItem('theme') || localStorage.getItem('resume-theme');
        var dark =
            stored === 'dark' ||
            (stored !== 'light' && window.matchMedia('(prefers-color-scheme: dark)').matches);
        document.documentElement.classList.toggle('dark', dark);
        document.documentElement.style.setProperty('color-scheme', dark ? 'dark' : 'light');
    });
</script>