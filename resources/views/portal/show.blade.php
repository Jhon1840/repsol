<x-portal.layout :title="'Rider ' . $rider->rider_id">
    <section class="portal-results">
        <div class="portal-results-header">
            <div>
                <p class="portal-kicker">RESULTADO</p>
                <h1>{{ $rider->name }}</h1>
                <p class="portal-muted">ID {{ $rider->rider_id }}</p>
            </div>

            <a href="{{ route('portal.index') }}" class="portal-secondary-button">Nueva consulta</a>
        </div>

        <div class="portal-metrics">
            <article class="portal-metric-card">
                <span>Puntos acumulados</span>
                <strong>{{ number_format((int) $rider->points_balance) }}</strong>
            </article>

            <article class="portal-metric-card">
                <span>Actividad reciente</span>
                <strong>{{ $rider->movements->count() }}</strong>
            </article>
        </div>

        <div class="portal-panels">
            <article class="portal-card">
                <h2>Historial reciente</h2>

                <div class="portal-table-wrap">
                    <table class="portal-table">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Referencia</th>
                                <th>Detalle</th>
                                <th>Puntos</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rider->movements as $movement)
                                <tr>
                                    <td>{{ optional($movement->occurred_at)->format('d/m/Y') ?? '-' }}</td>
                                    <td>{{ $movement->reference ?? '-' }}</td>
                                    <td>{{ $movement->description ?? ucfirst($movement->movement_type) }}</td>
                                    <td>{{ number_format($movement->points) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4">No hay actividad reciente para este rider.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </article>

            <article class="portal-card">
                <h2>Documentos relacionados</h2>

                <div class="portal-doc-list">
                    @forelse ($rider->documents as $document)
                        <div class="portal-doc-item">
                            <strong>{{ $document->original_name }}</strong>
                            <span>{{ str_replace('_', ' ', $document->status) }}</span>
                        </div>
                    @empty
                        <div class="portal-empty">
                            No existen PDFs relacionados para este rider por ahora.
                        </div>
                    @endforelse
                </div>
            </article>
        </div>
    </section>
</x-portal.layout>
