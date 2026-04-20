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

const configuredApiBase = document.body.dataset.apiBase || window.RIDER_POINTS_API_BASE || '';
const apiBase = configuredApiBase.replace(/\/$/, '') || window.location.origin;

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
