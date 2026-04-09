<x-portal.layout title="Descuento de puntos">
    <section class="rider-entry">
        <article class="rider-entry-card">
            @php
                $selectedArticulos = collect(old('articulos', []))->map(fn ($id) => (string) $id);
                $selectedNombres = $articulos
                    ->filter(fn ($articulo) => $selectedArticulos->contains((string) $articulo->id))
                    ->pluck('nombre')
                    ->values();
            @endphp

            <header class="rider-entry-header">
                <div class="rider-entry-logo">
                    <img src="{{ asset('assets/REPSOL LUBRICANTS VERTICAL BLANCO.png') }}" alt="Repsol Lubricants">
                </div>
                <span class="rider-entry-eyebrow">Gestión interna</span>
                <h1>Descuento de Puntos</h1>
                <p>Ingresa el ID del rider y los puntos a descontar desde el saldo actual.</p>
            </header>

            <form action="{{ route('portal.discount') }}" method="POST" class="rider-entry-form">
                @csrf

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
                        value="{{ old('rider_id') }}"
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
                                <label class="rider-multiselect-option">
                                    <input
                                        type="checkbox"
                                        name="articulos[]"
                                        value="{{ $articulo->id }}"
                                        @checked($selectedArticulos->contains((string) $articulo->id))
                                    >
                                    <span>{{ $articulo->nombre }}</span>
                                </label>
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
                    Siguiente
                </button>

                <div class="rider-example-hint">
                    <small>Solo se permiten valores enteros y no mayores al saldo actual del rider. Puedes seleccionar uno o más artículos.</small>
                </div>
            </form>

            <div class="rider-admin-link">
                <a href="{{ route('portal.index') }}">Volver a consulta de puntos</a>
            </div>
        </article>
    </section>

    <script>
        (() => {
            const multiselects = document.querySelectorAll('[data-multiselect]');

            multiselects.forEach((multiselect) => {
                const label = multiselect.querySelector('[data-multiselect-label]');
                const checkboxes = [...multiselect.querySelectorAll('input[type="checkbox"]')];
                const placeholder = 'Selecciona uno o más artículos';

                const syncLabel = () => {
                    const selected = checkboxes
                        .filter((checkbox) => checkbox.checked)
                        .map((checkbox) => checkbox.nextElementSibling?.textContent?.trim())
                        .filter(Boolean);

                    label.textContent = selected.length ? selected.join(', ') : placeholder;
                };

                checkboxes.forEach((checkbox) => {
                    checkbox.addEventListener('change', syncLabel);
                });

                syncLabel();
            });
        })();
    </script>
</x-portal.layout>

