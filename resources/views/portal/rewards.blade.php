<x-portal.layout :title="'Premios ' . $rider->rider_id">
    @php
        $pointsBalance = (int) $rider->points_balance;
        $availableRewards = $articulos
            ->map(function ($articulo) use ($pointsBalance) {
                $points = (int) ($articulo->pointCosts->first()?->points ?? 0);

                return [
                    'articulo' => $articulo,
                    'missing_points' => max(0, $points - $pointsBalance),
                    'points' => $points,
                ];
            })
            ->sortBy([
                fn (array $reward): int => $reward['missing_points'] > 0 ? 1 : 0,
                fn (array $reward): int => $reward['points'],
                fn (array $reward): string => $reward['articulo']->nombre,
            ])
            ->values();

        $redeemableCount = $availableRewards->filter(fn (array $reward): bool => $reward['missing_points'] === 0)->count();
    @endphp

    <section class="rider-result rider-rewards-screen">
        <article class="rider-result-card rider-rewards-card">
            <header class="rider-result-header rider-rewards-header">
                <div class="rider-result-icon">
                    <img src="{{ asset('assets/REPSOL LUBRICANTS VERTICAL BLANCO.png') }}" alt="Repsol Lubricants">
                </div>
                <span class="rider-entry-eyebrow">Premios disponibles</span>
                <h1>{{ $rider->name }}</h1>
                <p>ID {{ $rider->rider_id }}</p>
            </header>

            <section class="rider-rewards-summary" aria-label="Resumen para canje">
                <article>
                    <span>Saldo</span>
                    <strong>{{ number_format($pointsBalance) }}</strong>
                    <small>puntos</small>
                </article>

                <article>
                    <span>Rango</span>
                    <strong>{{ $rider->rango ?? 'Sin rango' }}</strong>
                    <small>{{ $redeemableCount }} para canjear</small>
                </article>
            </section>

            @if (blank($rider->rango))
                <div class="rider-empty-state">
                    El rider todavía no tiene rango asignado para calcular premios.
                </div>
            @elseif ($availableRewards->isEmpty())
                <div class="rider-empty-state">
                    No hay premios configurados para el rango {{ $rider->rango }}.
                </div>
            @else
                <section class="rider-rewards-list" aria-label="Premios y costos por rango">
                    @foreach ($availableRewards as $reward)
                        @php
                            $articulo = $reward['articulo'];
                            $points = $reward['points'];
                            $missingPoints = $reward['missing_points'];
                            $canRedeem = $missingPoints === 0;
                            $imageUrl = $articulo->primaryImageUrl();
                        @endphp

                        <article class="rider-reward-item @if ($canRedeem) is-redeemable @endif">
                            <div class="rider-reward-main">
                                @if ($imageUrl)
                                    <img
                                        src="{{ $imageUrl }}"
                                        alt="{{ $articulo->nombre }}"
                                        class="rider-reward-image"
                                        loading="lazy"
                                    >
                                @else
                                    <div class="rider-reward-icon" aria-hidden="true">
                                        {{ str($articulo->nombre)->trim()->substr(0, 1)->upper() }}
                                    </div>
                                @endif

                                <div class="rider-reward-copy">
                                    <h2>{{ $articulo->nombre }}</h2>
                                    <p>{{ $articulo->descripcion ?: 'Premio disponible para riders ' . strtolower($rider->rango) . '.' }}</p>
                                </div>
                            </div>

                            <div class="rider-reward-meta">
                                <div class="rider-reward-cost">
                                    <strong>{{ number_format($points) }}</strong>
                                    <span>puntos</span>
                                </div>

                                @if ($canRedeem)
                                    <span class="rider-reward-status is-ready">Te alcanza</span>
                                @else
                                    <span class="rider-reward-status">Faltan {{ number_format($missingPoints) }}</span>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </section>
            @endif

            <div class="rider-rewards-actions">
                <a href="{{ route('portal.index') }}" class="rider-back-button">
                    Nueva consulta
                </a>
            </div>
        </article>
    </section>
</x-portal.layout>
