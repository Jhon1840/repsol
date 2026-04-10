<script>
    (() => {
        const refreshUrl = @json(route('session.refresh'));
        const refreshIntervalMs = 10 * 60 * 1000;
        let refreshTimerId = null;
        let refreshInFlight = false;

        const applyCsrfToken = (token) => {
            if (!token) {
                return;
            }

            let meta = document.querySelector('meta[name="csrf-token"]');

            if (!meta) {
                meta = document.createElement('meta');
                meta.name = 'csrf-token';
                document.head.appendChild(meta);
            }

            meta.content = token;

            document
                .querySelectorAll('input[name="_token"]')
                .forEach((input) => {
                    input.value = token;
                });

            if (window.axios) {
                window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
            }
        };

        const refreshSession = async () => {
            if (refreshInFlight || document.visibilityState === 'hidden') {
                return;
            }

            refreshInFlight = true;

            try {
                const response = await fetch(refreshUrl, {
                    method: 'GET',
                    credentials: 'same-origin',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        Accept: 'application/json',
                    },
                    cache: 'no-store',
                });

                if (!response.ok) {
                    return;
                }

                const payload = await response.json();
                applyCsrfToken(payload.csrf_token);
            } catch (error) {
                console.debug('No se pudo refrescar la sesión automáticamente.', error);
            } finally {
                refreshInFlight = false;
            }
        };

        const startRefreshLoop = () => {
            if (refreshTimerId) {
                window.clearInterval(refreshTimerId);
            }

            refreshTimerId = window.setInterval(refreshSession, refreshIntervalMs);
        };

        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible') {
                refreshSession();
            }
        });

        applyCsrfToken(document.querySelector('meta[name="csrf-token"]')?.content);
        startRefreshLoop();
    })();
</script>
