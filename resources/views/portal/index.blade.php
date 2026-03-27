<x-portal.layout title="Consulta de rider">
    <section class="rider-entry">
        <article class="rider-entry-card">
            <header class="rider-entry-header">
                <div class="rider-entry-logo">R</div>
                <h1>Consulta de Puntos</h1>
                <p>Ingresa tu ID para consultar tus puntos acumulados.</p>
            </header>

            <form action="{{ route('portal.search') }}" method="POST" class="rider-entry-form">
                @csrf

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

                <button type="submit" class="rider-submit-button">
                    Consultar Puntos
                </button>

                <div class="rider-example-hint">
                    <small>Ejemplos de IDs válidos: SC00065, SC00081, SC00102</small>
                </div>
            </form>

        </article>
    </section>
</x-portal.layout>
