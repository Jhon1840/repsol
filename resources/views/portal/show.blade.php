<x-portal.layout :title="'Rider ' . $rider->rider_id">
    <section class="rider-result">
        <article class="rider-result-card">
            <header class="rider-result-header">
                <div class="rider-result-icon">
                    <img src="{{ asset('assets/REPSOL LUBRICANTS VERTICAL BLANCO.png') }}" alt="Repsol Lubricants">
                </div>
                <span class="rider-entry-eyebrow">Resumen del rider</span>
                <h1>Hola, {{ $rider->name }}</h1>
                <p>ID {{ $rider->rider_id }}</p>
            </header>

            <section class="rider-points-display">
                <div class="rider-points-label">Tus puntos acumulados</div>
                <div class="rider-points-value">{{ number_format((int) $rider->points_balance) }}</div>
                <div class="rider-points-unit">puntos</div>
            </section>

            <section class="rider-info-section">
                <h2>Resumen de actividad</h2>

                <div class="rider-info-stats">
                    <article class="rider-stat-item">
                        <span class="rider-stat-number">{{ $rider->movements->count() }}</span>
                        <span class="rider-stat-label">Movimientos</span>
                    </article>

                    <article class="rider-stat-item">
                        <span class="rider-stat-number">{{ number_format((int) $rider->points_balance) }}</span>
                        <span class="rider-stat-label">Puntos</span>
                    </article>
                </div>
            </section>

            <section class="rider-activity">
                <h2>Últimos movimientos</h2>

                <div class="rider-activity-list">
                    @forelse ($rider->movements->take(3) as $movement)
                        <article class="rider-activity-item">
                            <div class="rider-activity-icon">+</div>

                            <div class="rider-activity-info">
                                <div class="rider-activity-description">
                                    {{ $movement->description ?? ucfirst($movement->movement_type) }}
                                </div>

                                <div class="rider-activity-date">
                                    {{ optional($movement->occurred_at)->format('d/m/Y') ?? 'Sin fecha' }}
                                </div>
                            </div>

                            <div class="rider-activity-points">
                                +{{ number_format($movement->points) }}
                            </div>
                        </article>
                    @empty
                        <div class="rider-empty-state">
                            No hay movimientos recientes para este rider.
                        </div>
                    @endforelse
                </div>
            </section>

            <a href="{{ route('portal.index') }}" class="rider-back-button">
                Nueva consulta
            </a>

            <footer class="rider-info-footer">
                <div class="rider-info-row">
                    <span class="rider-info-label">ID</span>
                    <span class="rider-info-value">{{ $rider->rider_id }}</span>
                </div>

                <div class="rider-info-row">
                    <span class="rider-info-label">Nombre</span>
                    <span class="rider-info-value">{{ $rider->name }}</span>
                </div>

                <div class="rider-info-row">
                    <span class="rider-info-label">Puntos</span>
                    <span class="rider-info-value">{{ number_format((int) $rider->points_balance) }}</span>
                </div>
            </footer>
        </article>
    </section>
</x-portal.layout>
