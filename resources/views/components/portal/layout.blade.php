<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Consulta de riders' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        (() => {
            const key = 'repsol-theme';
            const saved = localStorage.getItem(key);
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            const theme = saved || (prefersDark ? 'dark' : 'light');

            document.documentElement.classList.toggle('dark', theme === 'dark');
            document.documentElement.dataset.theme = theme;
        })();
    </script>
</head>
<body class="portal-shell">
    <button type="button" class="theme-toggle" id="portal-theme-toggle" aria-label="Cambiar tema">
        <span id="portal-theme-toggle-icon">◐</span>
    </button>

    <main class="portal-main">
        {{ $slot }}
    </main>

    <script>
        (() => {
            const key = 'repsol-theme';
            const button = document.getElementById('portal-theme-toggle');
            const icon = document.getElementById('portal-theme-toggle-icon');

            const sync = () => {
                const isDark = document.documentElement.classList.contains('dark');
                icon.textContent = isDark ? '☀' : '☾';
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
</body>
</html>
