const form = document.getElementById('rider-points-form');
const input = document.getElementById('rider_id');
const button = document.getElementById('rider-submit-button');
const message = document.getElementById('rider-message');
const result = document.getElementById('rider-result');
const points = document.getElementById('rider-points');
const riderName = document.getElementById('rider-name');
const riderCode = document.getElementById('rider-code');
const riderRango = document.getElementById('rider-rango');
const riderRewardsLink = document.getElementById('rider-rewards-link');
const riderMovements = document.getElementById('rider-movements');
const riderMovementList = document.getElementById('rider-movement-list');
const installCard = document.getElementById('portal-install-card');
const installButton = document.getElementById('portal-install-button');
const installHint = document.getElementById('portal-install-hint');
const debugBody = document.getElementById('portal-debug-body');

const configuredApiBase = document.body.dataset.apiBase || window.RIDER_POINTS_API_BASE || '';
const apiBase = configuredApiBase.replace(/\/$/, '') || window.location.origin;
let deferredInstallPrompt = null;
const pwaDebugLines = [];

const isStandalone = () =>
    window.matchMedia?.('(display-mode: standalone)').matches || window.navigator.standalone === true;

const isIos = () =>
    /iphone|ipad|ipod/i.test(window.navigator.userAgent) ||
    (window.navigator.platform === 'MacIntel' && window.navigator.maxTouchPoints > 1);

const isAndroid = () => /android/i.test(window.navigator.userAgent);

const isChromium = () =>
    /chrome|crios|edg|edgios|brave/i.test(window.navigator.userAgent) && !/opr\//i.test(window.navigator.userAgent);

const isSafari = () =>
    /safari/i.test(window.navigator.userAgent) &&
    !/chrome|crios|android|fxios|edgios|brave/i.test(window.navigator.userAgent);

const logPwaDebug = (label, value = '') => {
    const line = value === '' ? label : `${label}: ${value}`;
    pwaDebugLines.push(line);
    console.info('[PWA DEBUG]', line);

    try {
        window.localStorage.setItem('pwa-debug-log', JSON.stringify(pwaDebugLines.slice(-50)));
    } catch (error) {
        console.warn('[PWA DEBUG] No se pudo guardar log local.', error);
    }

    if (debugBody) {
        debugBody.textContent = pwaDebugLines.join('\n');
    }
};

const showInstallHint = (text) => {
    if (!installCard || !installHint) {
        return;
    }

    installCard.hidden = false;
    installHint.hidden = false;
    installHint.textContent = text;
};

const showInstallButton = () => {
    if (!installCard || !installButton) {
        return;
    }

    installCard.hidden = false;
    installButton.hidden = false;
};

const hideInstallUi = () => {
    if (installCard) {
        installCard.hidden = true;
    }

    if (installButton) {
        installButton.hidden = true;
    }

    if (installHint) {
        installHint.hidden = true;
        installHint.textContent = '';
    }
};

const getFallbackInstallMessage = () => {
    if (isIos()) {
        return 'Para instalar en iPhone o iPad, abre Compartir y toca "Agregar a pantalla de inicio".';
    }

    if (isAndroid()) {
        return 'Si no aparece el botón, abre el menú del navegador y busca "Instalar app" o "Agregar a pantalla principal".';
    }

    if (isChromium()) {
        return 'En computadora, si no aparece el botón, revisa el menú del navegador y busca "Instalar app".';
    }

    if (isSafari()) {
        return 'En Safari de escritorio no siempre aparece instalación como app. Prueba desde un navegador compatible o usa el menú del sistema.';
    }

    return 'Si tu navegador no muestra el botón, prueba desde Chrome, Edge o Safari y revisa el menú del navegador para instalar la app.';
};

if (installCard && installButton && installHint) {
    logPwaDebug('URL', window.location.href);
    logPwaDebug('User agent', window.navigator.userAgent);
    logPwaDebug('Standalone', String(isStandalone()));
    logPwaDebug('iOS', String(isIos()));
    logPwaDebug('Android', String(isAndroid()));
    logPwaDebug('Chromium', String(isChromium()));
    logPwaDebug('Safari', String(isSafari()));
    logPwaDebug('Manifest', document.querySelector('link[rel="manifest"]')?.getAttribute('href') ?? 'No encontrado');

    if (isStandalone()) {
        hideInstallUi();
        logPwaDebug('Estado', 'La app ya esta instalada o abierta como standalone.');
    } else {
        showInstallHint(getFallbackInstallMessage());
        logPwaDebug('Estado', 'Mostrando mensaje manual porque beforeinstallprompt aun no disparo.');
    }

    window.addEventListener('beforeinstallprompt', (event) => {
        event.preventDefault();
        deferredInstallPrompt = event;
        showInstallButton();
        showInstallHint('Puedes instalar esta app directamente desde este botón.');
        logPwaDebug('beforeinstallprompt', 'SI');
    });

    installButton.addEventListener('click', async () => {
        if (!deferredInstallPrompt) {
            logPwaDebug('Click instalar', 'Sin deferredPrompt disponible.');
            return;
        }

        logPwaDebug('Click instalar', 'Solicitando prompt nativo.');
        deferredInstallPrompt.prompt();

        const { outcome } = await deferredInstallPrompt.userChoice;
        deferredInstallPrompt = null;
        logPwaDebug('Resultado prompt', outcome);

        if (outcome === 'accepted') {
            hideInstallUi();
        } else {
            showInstallButton();
            showInstallHint('La instalación quedó cancelada. Puedes intentarlo otra vez desde este botón o desde el menú del navegador.');
        }
    });

    window.addEventListener('appinstalled', () => {
        deferredInstallPrompt = null;
        hideInstallUi();
        logPwaDebug('appinstalled', 'SI');
    });

    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.getRegistration('/consulta-puntos/').then((registration) => {
            logPwaDebug('Service worker scope', registration?.scope ?? 'Sin registro activo');
            logPwaDebug('Service worker active', registration?.active ? 'SI' : 'NO');
        }).catch((error) => {
            logPwaDebug('Service worker error', error?.message ?? 'Error desconocido');
        });
    } else {
        logPwaDebug('Service worker', 'No soportado por este navegador');
    }

    window.setTimeout(() => {
        if (!deferredInstallPrompt && !isStandalone()) {
            logPwaDebug('Diagnostico final', 'El navegador no emitio beforeinstallprompt. Debes instalar desde el menu del navegador o usar Chrome/Samsung Internet compatibles.');
        }
    }, 3000);
}

const setMessage = (text, isError = false) => {
    message.textContent = text;
    message.hidden = false;
    message.classList.toggle('is-error', isError);
};

const clearMessage = () => {
    message.textContent = '';
    message.hidden = true;
    message.classList.remove('is-error');
};

const formatPoints = (value) => new Intl.NumberFormat('es-BO').format(Number(value) || 0);

const formatSignedPoints = (value) => {
    const number = Number(value) || 0;
    const sign = number > 0 ? '+' : '';

    return `${sign}${formatPoints(number)}`;
};

const renderMovements = (movements = []) => {
    riderMovementList.innerHTML = '';
    riderMovements.hidden = movements.length === 0;

    movements.forEach((movement) => {
        const item = document.createElement('article');
        item.className = 'rider-movement-item';

        const detail = document.createElement('div');
        const description = document.createElement('div');
        const date = document.createElement('div');
        const movementPoints = document.createElement('div');

        description.className = 'rider-movement-description';
        date.className = 'rider-movement-date';
        movementPoints.className = 'rider-movement-points';

        description.textContent = movement.description;
        date.textContent = movement.occurred_at || 'Sin fecha';
        movementPoints.textContent = formatSignedPoints(movement.points);

        detail.append(description, date);
        item.append(detail, movementPoints);
        riderMovementList.append(item);
    });
};

form.addEventListener('submit', async (event) => {
    event.preventDefault();

    const riderId = input.value.trim().toUpperCase();

    result.hidden = true;
    riderRewardsLink.hidden = true;
    clearMessage();

    if (! riderId) {
        setMessage('Ingresa tu ID de rider para consultar tus puntos.', true);
        input.focus();
        return;
    }

    button.disabled = true;
    button.textContent = 'Consultando...';

    try {
        const response = await fetch(`${apiBase}/api/riders/${encodeURIComponent(riderId)}/points`, {
            headers: {
                Accept: 'application/json',
            },
        });

        const data = await response.json().catch(() => ({}));

        if (! response.ok) {
            throw new Error(data.message || 'No se pudo consultar el rider en este momento.');
        }

        points.textContent = formatPoints(data.rider.points_balance);
        riderName.textContent = data.rider.name;
        riderCode.textContent = `ID ${data.rider.rider_id}`;
        riderRango.textContent = data.rider.rango ? `Rango ${data.rider.rango}` : 'Sin rango asignado';
        riderRewardsLink.href = `/consulta-puntos/${encodeURIComponent(data.rider.rider_id)}/premios`;
        riderRewardsLink.hidden = false;
        renderMovements(data.recent_movements || []);
        result.hidden = false;
    } catch (error) {
        setMessage(error.message, true);
    } finally {
        button.disabled = false;
        button.textContent = 'Consultar Puntos';
    }
});
