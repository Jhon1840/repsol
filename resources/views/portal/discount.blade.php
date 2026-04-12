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
                        placeholder="Ej: SC00065"
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
                <section id="rider-discount-details" class="rider-discount-summary" aria-label="Datos del rider encontrado">
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
                        <label for="points">Puntos a descontar</label>
                        <input
                            id="points"
                            name="points"
                            type="number"
                            min="1"
                            step="1"
                            value="{{ old('points') }}"
                            placeholder="Ej: 50"
                            required
                        >
                    </div>

                    @error('points')
                        <div class="rider-error-message">
                            {{ $message }}
                        </div>
                    @enderror

                    <div class="rider-form-group">
                        <label for="articulos">Qué compró o canjeó</label>
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
                                    @endphp

                                    <div class="rider-multiselect-option @if ($isSelected) is-selected @endif" data-article-option>
                                        <div class="rider-multiselect-choice">
                                            <span data-article-name>{{ $articulo->nombre }}</span>
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

                    <button type="submit" class="rider-submit-button">
                        Descontar puntos
                    </button>

                    <div class="rider-example-hint">
                        <small>Solo se permiten valores enteros y no mayores al saldo actual del rider. Usa + y - para indicar la cantidad de cada artículo.</small>
                    </div>
                </form>
            @endif

            <div class="rider-admin-link">
                <a href="{{ route('portal.index') }}">Volver a consulta de puntos</a>
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

            multiselects.forEach((multiselect) => {
                const label = multiselect.querySelector('[data-multiselect-label]');
                const options = [...multiselect.querySelectorAll('[data-article-option]')];
                const placeholder = 'Selecciona uno o más artículos';

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

                const syncOption = (option) => {
                    const field = option.querySelector('[data-quantity-field]');
                    const display = option.querySelector('[data-quantity-display]');
                    const minusButton = option.querySelector('[data-quantity-step="-1"]');
                    const quantity = Number(field.value || 0);
                    const isSelected = quantity > 0;

                    field.disabled = ! isSelected;
                    minusButton.disabled = ! isSelected;
                    display.textContent = String(quantity);
                    option.classList.toggle('is-selected', isSelected);
                };

                options.forEach((option) => {
                    const field = option.querySelector('[data-quantity-field]');
                    const buttons = option.querySelectorAll('[data-quantity-step]');

                    buttons.forEach((button) => {
                        button.addEventListener('click', () => {
                            const nextQuantity = Math.max(0, Number(field.value || 0) + Number(button.dataset.quantityStep));

                            field.value = String(nextQuantity);
                            syncOption(option);
                            syncLabel();
                        });
                    });

                    syncOption(option);
                });

                syncLabel();
            });
        })();
    </script>
</x-portal.layout>

