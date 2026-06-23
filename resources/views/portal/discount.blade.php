<x-portal.layout title="Descuento de puntos">
    <section class="rider-entry">
        <article id="discount-view-start" class="rider-entry-card">
            @php
                $selectedArticulos = collect(old('articulos', []))
                    ->mapWithKeys(fn ($quantity, $id) => [(string) $id => max(1, (int) $quantity)]);

                $selectedNombres = $articulos
                    ->filter(fn ($articulo) => $selectedArticulos->has((string) $articulo->id))
                    ->map(fn ($articulo) => $selectedArticulos->get((string) $articulo->id, 1) . ' x ' . $articulo->nombre)
                    ->values();
            @endphp

            <header class="rider-entry-header">
                <div class="rider-entry-logo">
                    <img src="{{ asset('assets/REPSOL LUBRICANTS VERTICAL BLANCO.png') }}" alt="Repsol Lubricants">
                </div>
                <span class="rider-entry-eyebrow">Gestión interna</span>
                <h1>Descuento de Puntos</h1>
                <p>Primero busca al rider. Luego confirma su rango y registra el canje.</p>
            </header>

            <form action="{{ route('portal.discount.form') }}" method="GET" class="rider-entry-form">
                @if (session('success'))
                    <div class="rider-success-message">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="rider-form-group">
                    <label for="rider_id">ID de Rider</label>
                    <input
                        id="rider_id"
                        name="rider_id"
                        type="text"
                        value="{{ old('rider_id', request('rider_id')) }}"
                        placeholder="Ej: PYA00065"
                        autocomplete="off"
                        required
                    >
                </div>

                @error('rider_id')
                    <div class="rider-error-message">
                        {{ $message }}
                    </div>
                @enderror

                <button type="submit" class="rider-submit-button">
                    Buscar rider
                </button>
            </form>

            @if ($rider)
                <section
                    id="rider-discount-details"
                    class="rider-discount-summary"
                    aria-label="Datos del rider encontrado"
                    data-rider-points-balance="{{ (int) $rider->points_balance }}"
                >
                    <div class="rider-discount-summary-rider">
                        <span class="rider-discount-summary-label">Rider</span>
                        <strong>{{ $rider->name }}</strong>
                        <small>ID {{ $rider->rider_id }}</small>
                    </div>

                    <div>
                        <span class="rider-discount-summary-label">Rango</span>
                        <strong>{{ $rider->rango ?? 'Sin rango' }}</strong>
                    </div>

                    <div>
                        <span class="rider-discount-summary-label">Saldo</span>
                        <strong>{{ number_format((int) $rider->points_balance) }}</strong>
                        <small>puntos</small>
                    </div>
                </section>

                <form action="{{ route('portal.discount') }}" method="POST" class="rider-entry-form">
                    @csrf
                    <input type="hidden" name="rider_id" value="{{ old('rider_id', $rider->rider_id) }}">

                    <div class="rider-form-group">
                        <label for="articulos">Qué compró o canjeó</label>
                        @if (blank($rider->rango))
                            <div class="rider-error-message">
                                El rider no tiene rango asignado para calcular el coste de los artículos.
                            </div>
                        @elseif ($articulos->isEmpty())
                            <div class="rider-error-message">
                                No hay artículos con coste configurado para el rango {{ $rider->rango }}.
                            </div>
                        @else
                            <details class="rider-multiselect" data-multiselect>
                                <summary class="rider-multiselect-summary">
                                    <span class="rider-multiselect-label" data-multiselect-label>
                                        {{ $selectedNombres->isNotEmpty() ? $selectedNombres->implode(', ') : 'Selecciona uno o más artículos' }}
                                    </span>
                                    <span class="rider-multiselect-arrow" aria-hidden="true">▾</span>
                                </summary>

                                <div class="rider-multiselect-menu">
                                    @foreach ($articulos as $articulo)
                                        @php
                                            $articleId = (string) $articulo->id;
                                            $isSelected = $selectedArticulos->has($articleId);
                                            $quantity = $isSelected ? $selectedArticulos->get($articleId, 1) : 0;
                                            $pointsPerUnit = (int) ($articulo->pointCosts->first()?->points ?? 0);
                                        @endphp

                                        <div
                                            class="rider-multiselect-option @if ($isSelected) is-selected @endif"
                                            data-article-option
                                            data-point-cost="{{ $pointsPerUnit }}"
                                        >
                                            <div class="rider-multiselect-choice">
                                                <span data-article-name>{{ $articulo->nombre }}</span>
                                                <small>{{ number_format($pointsPerUnit) }} puntos c/u</small>
                                            </div>

                                            <div class="rider-quantity-control" aria-label="Cantidad para {{ $articulo->nombre }}">
                                                <button
                                                    type="button"
                                                    class="rider-quantity-button"
                                                    data-quantity-step="-1"
                                                    @disabled($quantity === 0)
                                                >
                                                    -
                                                </button>
                                                <span
                                                    class="rider-quantity-display"
                                                    data-quantity-display
                                                >
                                                    {{ $quantity }}
                                                </span>
                                                <button
                                                    type="button"
                                                    class="rider-quantity-button"
                                                    data-quantity-step="1"
                                                >
                                                    +
                                                </button>
                                            </div>

                                            <span
                                                class="rider-article-subtotal"
                                                data-article-subtotal
                                            >
                                                {{ number_format($quantity * $pointsPerUnit) }} pts
                                            </span>

                                            <input
                                                type="hidden"
                                                name="articulos[{{ $articulo->id }}]"
                                                value="{{ $quantity }}"
                                                data-quantity-field
                                                @disabled($quantity === 0)
                                            >
                                        </div>
                                    @endforeach
                                </div>
                            </details>
                        @endif
                    </div>

                    @error('articulos')
                        <div class="rider-error-message">
                            {{ $message }}
                        </div>
                    @enderror

                    @error('articulos.*')
                        <div class="rider-error-message">
                            {{ $message }}
                        </div>
                    @enderror

                    <div class="rider-discount-total" data-discount-total-wrapper>
                        <span>Total a descontar</span>
                        <strong><span data-discount-total>0</span> puntos</strong>
                    </div>

                    <button
                        type="submit"
                        class="rider-submit-button"
                        data-discount-submit
                        data-default-label="Descontar puntos"
                        data-insufficient-label="No tiene puntos suficientes"
                        @disabled(blank($rider->rango) || $articulos->isEmpty())
                    >
                        Descontar puntos
                    </button>

                    <div class="rider-example-hint">
                        <small>El total se calcula según el rango actual del rider. Usa + y - para indicar la cantidad de cada artículo.</small>
                    </div>
                </form>
            @endif

            <div class="rider-admin-link">
                <a href="{{ url('/consulta-puntos/') }}">Volver a saldo rider</a>
            </div>
        </article>
    </section>

    <script>
        (() => {
            const details = document.getElementById('rider-discount-details');
            const viewStart = document.getElementById('discount-view-start');

            if (details && viewStart) {
                requestAnimationFrame(() => {
                    const topMargin = Math.min(Math.max(window.innerHeight * 0.035, 12), 32);
                    const top = viewStart.getBoundingClientRect().top + window.scrollY - topMargin;

                    window.scrollTo({
                        top,
                        behavior: 'smooth',
                    });
                });
            }

            const multiselects = document.querySelectorAll('[data-multiselect]');
            const riderPointsBalance = Number(details?.dataset.riderPointsBalance || 0);

            multiselects.forEach((multiselect) => {
                const label = multiselect.querySelector('[data-multiselect-label]');
                const options = [...multiselect.querySelectorAll('[data-article-option]')];
                const totalLabel = document.querySelector('[data-discount-total]');
                const submitButton = document.querySelector('[data-discount-submit]');
                const placeholder = 'Selecciona uno o más artículos';
                const formatter = new Intl.NumberFormat('es-BO');
                const insufficientPointsText = 'El rider no cuenta con puntos suficientes para este descuento.';
                const submitDefaultLabel = submitButton?.dataset.defaultLabel || 'Descontar puntos';
                const submitInsufficientLabel = submitButton?.dataset.insufficientLabel || 'No tiene puntos suficientes';
                const isSubmitInitiallyDisabled = submitButton?.disabled ?? false;
                let wasInsufficient = false;

                const sendInsufficientPointsNotification = () => {
                    if (window.FilamentNotification) {
                        new window.FilamentNotification()
                            .title(insufficientPointsText)
                            .danger()
                            .send();

                        return;
                    }

                    document.querySelector('[data-insufficient-points-alert]')?.remove();

                    const container = document.querySelector('[data-portal-notifications]')
                        || document.body.appendChild(document.createElement('div'));

                    container.dataset.portalNotifications = 'true';
                    container.className = 'fi-no fi-align-end fi-vertical-align-start';
                    container.setAttribute('aria-live', 'assertive');
                    container.setAttribute('aria-atomic', 'true');

                    const notification = document.createElement('div');
                    notification.dataset.insufficientPointsAlert = 'true';
                    notification.className = 'fi-no-notification';
                    notification.role = 'alert';
                    notification.style.visibility = 'visible';
                    notification.innerHTML = `
                        <div class="fi-no-notification-icon" aria-hidden="true">⚠</div>
                        <div class="fi-no-notification-main">
                            <div class="fi-no-notification-text">
                                <div class="fi-no-notification-title">${insufficientPointsText}</div>
                                <div class="fi-no-notification-body">Reduce la cantidad o selecciona un artículo de menor valor.</div>
                            </div>
                        </div>
                        <button type="button" class="fi-no-notification-close-btn" aria-label="Cerrar alerta">×</button>
                    `;

                    notification.querySelector('button')?.addEventListener('click', () => notification.remove());
                    container.appendChild(notification);
                    window.setTimeout(() => notification.remove(), 5000);
                };

                const syncLabel = () => {
                    const selected = options
                        .map((option) => {
                            const name = option.querySelector('[data-article-name]')?.textContent?.trim();
                            const quantity = Number(option.querySelector('[data-quantity-field]')?.value || 0);

                            return quantity > 0 && name ? `${quantity} x ${name}` : null;
                        })
                        .filter(Boolean);

                    label.textContent = selected.length ? selected.join(', ') : placeholder;
                };

                const calculateTotal = () => {
                    return options.reduce((sum, option) => {
                        const quantity = Number(option.querySelector('[data-quantity-field]')?.value || 0);
                        const pointCost = Number(option.dataset.pointCost || 0);

                        return sum + (quantity * pointCost);
                    }, 0);
                };

                const syncSubmitButton = (total) => {
                    if (! submitButton) {
                        return;
                    }

                    const isInsufficient = total > riderPointsBalance;

                    submitButton.disabled = isSubmitInitiallyDisabled || isInsufficient;
                    submitButton.textContent = isInsufficient ? submitInsufficientLabel : submitDefaultLabel;
                };

                const syncTotal = () => {
                    const total = calculateTotal();

                    if (totalLabel) {
                        totalLabel.textContent = formatter.format(total);
                    }

                    const isInsufficient = total > riderPointsBalance;

                    if (isInsufficient && ! wasInsufficient) {
                        sendInsufficientPointsNotification();
                    }

                    wasInsufficient = isInsufficient;
                    syncSubmitButton(total);
                };

                const syncOption = (option) => {
                    const field = option.querySelector('[data-quantity-field]');
                    const display = option.querySelector('[data-quantity-display]');
                    const minusButton = option.querySelector('[data-quantity-step="-1"]');
                    const subtotal = option.querySelector('[data-article-subtotal]');
                    const quantity = Number(field.value || 0);
                    const pointCost = Number(option.dataset.pointCost || 0);
                    const isSelected = quantity > 0;

                    field.disabled = ! isSelected;
                    minusButton.disabled = ! isSelected;
                    display.textContent = String(quantity);
                    subtotal.textContent = `${formatter.format(quantity * pointCost)} pts`;
                    option.classList.toggle('is-selected', isSelected);
                };

                options.forEach((option) => {
                    const field = option.querySelector('[data-quantity-field]');
                    const buttons = option.querySelectorAll('[data-quantity-step]');

                    buttons.forEach((button) => {
                        button.addEventListener('click', () => {
                            const currentQuantity = Number(field.value || 0);
                            const quantityStep = Number(button.dataset.quantityStep);
                            const nextQuantity = Math.max(0, currentQuantity + quantityStep);
                            const pointCost = Number(option.dataset.pointCost || 0);
                            const nextTotal = calculateTotal() + ((nextQuantity - currentQuantity) * pointCost);

                            if (quantityStep > 0 && nextTotal > riderPointsBalance) {
                                sendInsufficientPointsNotification();
                                syncSubmitButton(nextTotal);

                                return;
                            }

                            field.value = String(nextQuantity);
                            syncOption(option);
                            syncLabel();
                            syncTotal();
                        });
                    });

                    syncOption(option);
                });

                syncLabel();
                syncTotal();
            });
        })();
    </script>
</x-portal.layout>
