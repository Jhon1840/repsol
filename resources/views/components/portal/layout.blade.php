<!DOCTYPE html>
<html lang="es" class="dark" data-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0a1a2f">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Riders Pedidos Ya">
    <meta name="application-name" content="Riders Pedidos Ya">
    <meta name="mobile-web-app-capable" content="yes">
    <title>{{ $title ?? 'Consulta de riders' }}</title>
    <link rel="manifest" href="{{ asset('consulta-puntos/manifest.json') }}">
    <link rel="apple-touch-icon" sizes="192x192" href="{{ asset('consulta-puntos/icon-192.png') }}">
    @filamentStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- Theme mode kept for future use.
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
    --}}
</head>
<body class="portal-shell">
    <div class="portal-brand-bar">
        <div class="portal-brand-bar-inner">

            <div class="portal-brand-partner">
                <img src="{{ asset('assets/Logo Latco2.png') }}" alt="Latco Universal S.R.L.">
            </div>
        </div>
    </div>

    {{-- Theme toggle kept for future use.
        <button type="button" class="theme-toggle" id="portal-theme-toggle" aria-label="Cambiar tema">
            <img src="{{ asset('assets/logo.jpeg') }}" alt="" aria-hidden="true" id="portal-theme-toggle-icon">
        </button>
    --}}

    <main class="portal-main">
        {{ $slot }}
    </main>

    {{-- Theme toggle behavior kept for future use.
        <script>
            (() => {
                const key = 'repsol-theme';
                const button = document.getElementById('portal-theme-toggle');
                button.addEventListener('click', () => {
                    const isDark = document.documentElement.classList.toggle('dark');
                    const theme = isDark ? 'dark' : 'light';

                    document.documentElement.dataset.theme = theme;
                    localStorage.setItem(key, theme);
                });
            })();
        </script>
    --}}

    @auth
        @include('partials.session-refresh-script')
    @endauth

    @livewire(Filament\Livewire\Notifications::class)
    @filamentScripts(withCore: true)

    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register("{{ asset('consulta-puntos/sw.js') }}", {
                    scope: '/consulta-puntos/',
                }).catch(() => {
                    // Keep silent if SW registration fails; portal works without offline support.
                });
            });
        }
    </script>
</body>
</html>
