<button
    type="button"
    id="repsol-theme-toggle"
    class="fixed bottom-5 right-5 z-[100] inline-flex h-12 w-12 items-center justify-center rounded-full border border-white/20 bg-slate-950/90 text-white shadow-xl backdrop-blur dark:border-slate-700 dark:bg-white dark:text-slate-950"
    aria-label="Cambiar tema"
>
    <span id="repsol-theme-toggle-icon">◐</span>
</button>

<script>
    (() => {
        const key = 'repsol-theme';
        const button = document.getElementById('repsol-theme-toggle');
        const icon = document.getElementById('repsol-theme-toggle-icon');

        if (!button || !icon) {
            return;
        }

        const sync = () => {
            const theme = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
            icon.textContent = theme === 'dark' ? '☀' : '☾';
        };

        button.addEventListener('click', () => {
            const isDark = document.documentElement.classList.toggle('dark');
            const theme = isDark ? 'dark' : 'light';

            document.documentElement.dataset.theme = theme;
            localStorage.setItem(key, theme);
            sync();
        });

        sync();
    })();
</script>
