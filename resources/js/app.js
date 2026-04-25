import './bootstrap';

const isStandalone = () =>
    window.matchMedia?.('(display-mode: standalone)').matches || window.navigator.standalone === true;

const isIos = () =>
    /iphone|ipad|ipod/i.test(window.navigator.userAgent) ||
    (window.navigator.platform === 'MacIntel' && window.navigator.maxTouchPoints > 1);

const isSafari = () =>
    /safari/i.test(window.navigator.userAgent) &&
    !/chrome|crios|android|fxios|edgios/i.test(window.navigator.userAgent);

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
    } else if (isIos() && isSafari()) {
        showInstallHint('En iPhone o iPad, abre Compartir y luego toca "Agregar a pantalla de inicio".');
    }

    window.addEventListener('beforeinstallprompt', (event) => {
        event.preventDefault();
        deferredInstallPrompt = event;
        showInstallButton();
        installHint.hidden = true;
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
            showInstallHint('La instalación quedó cancelada. Puedes intentarlo otra vez desde este botón o desde el menú del navegador.');
        }
    });

    window.addEventListener('appinstalled', () => {
        deferredInstallPrompt = null;
        hideInstallUi();
    });
}
