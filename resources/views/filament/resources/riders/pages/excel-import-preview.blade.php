<div class="grid gap-4 md:grid-cols-2">
    @if (count($preview['new_products'] ?? []))
        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900 dark:border-amber-900/50 dark:bg-amber-950/30 dark:text-amber-100">
            <p class="font-semibold">Productos que se crearán</p>
            <ul class="mt-2 space-y-1">
                @foreach (array_slice($preview['new_products'], 0, 8) as $product)
                    <li>{{ $product['code'] }} - {{ $product['name'] }}</li>
                @endforeach
            </ul>
            @if (count($preview['new_products']) > 8)
                <p class="mt-2 text-xs">Y {{ count($preview['new_products']) - 8 }} producto(s) más.</p>
            @endif
        </div>
    @endif

    @if (count($preview['new_riders'] ?? []))
        <div class="rounded-2xl border border-sky-200 bg-sky-50 p-4 text-sm text-sky-900 dark:border-sky-900/50 dark:bg-sky-950/30 dark:text-sky-100">
            <p class="font-semibold">Riders que se crearán</p>
            <ul class="mt-2 space-y-1">
                @foreach (array_slice($preview['new_riders'], 0, 8) as $rider)
                    <li>{{ $rider['rider_id'] }} - {{ $rider['rider_name'] ?: 'Sin nombre' }}</li>
                @endforeach
            </ul>
            @if (count($preview['new_riders']) > 8)
                <p class="mt-2 text-xs">Y {{ count($preview['new_riders']) - 8 }} rider(s) más.</p>
            @endif
        </div>
    @endif
</div>
