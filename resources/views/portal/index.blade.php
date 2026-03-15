<x-portal.layout title="Consulta de rider">
    <section class="portal-hero">
        <div class="portal-brand">
            <p class="portal-kicker">REPSOL RIDERS</p>
            <h1>Consulta tus puntos con un ingreso mínimo y directo.</h1>
            <p>
                Solo necesitas tu ID de rider para ver nombre, puntos acumulados y actividad reciente.
            </p>
        </div>

        <form action="{{ route('portal.search') }}" method="POST" class="portal-card">
            @csrf
            <label class="portal-label" for="rider_id">ID del rider</label>
            <input
                id="rider_id"
                name="rider_id"
                type="text"
                class="portal-input"
                value="{{ old('rider_id') }}"
                placeholder="SC00065"
                autocomplete="off"
                required
            >

            @error('rider_id')
                <p class="portal-error">{{ $message }}</p>
            @enderror

            <button type="submit" class="portal-button">Consultar puntos</button>

            <p class="portal-note">Acceso administrativo en <a href="/admin">/admin</a>.</p>
        </form>
    </section>
</x-portal.layout>
