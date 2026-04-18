<!DOCTYPE html>
<html lang="es" class="dark" data-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Consulta de riders' }}</title>
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
</body>
</html>

