import './bootstrap';

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

const installCard = document.getElementById('portal-install-card');
const installButton = document.getElementById('portal-install-button');
const installHint = document.getElementById('portal-install-hint');

let deferredInstallPrompt = null;

const showInstallHint = (message) => {
    if (!installCard || !installHint) {
        return;
    }

    installCard.hidden = false;
    installHint.hidden = false;
    installHint.textContent = message;
};

const showInstallButton = () => {
    if (!installCard || !installButton) {
        return;
    }

    installCard.hidden = false;
    installButton.hidden = false;
};

const getFallbackInstallMessage = () => {
    if (isIos()) {
        return 'Para instalar en iPhone, abre esta página en Safari y toca Compartir > Agregar a pantalla de inicio.';
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

if (installCard && installButton && installHint) {
    if (isStandalone()) {
        hideInstallUi();
    } else {
        showInstallHint(getFallbackInstallMessage());
    }

    window.addEventListener('beforeinstallprompt', (event) => {
        event.preventDefault();
        deferredInstallPrompt = event;
        showInstallButton();
        showInstallHint('Puedes instalar esta app directamente desde este botón.');
    });

    installButton.addEventListener('click', async () => {
        if (!deferredInstallPrompt) {
            return;
        }

        deferredInstallPrompt.prompt();

        const { outcome } = await deferredInstallPrompt.userChoice;
        deferredInstallPrompt = null;

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
    });
}
