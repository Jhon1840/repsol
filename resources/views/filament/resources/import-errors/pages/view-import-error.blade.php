<x-filament-panels::page>
    @php($record = $this->getRecord())
    @php($processedItems = data_get($record->metadata, 'processed_items', []))
    @php($skippedItems = $record->getSkippedItems())
    @php($fatalErrors = $record->getFatalErrors())
    @php($parsedPoints = (int) data_get($record->metadata, 'parsed_points', 0))

    <div class="grid min-w-0 gap-6">
        <section class="min-w-0 rounded-2xl border border-slate-200/70 bg-white/95 p-5 shadow-sm dark:border-slate-800 dark:bg-slate-950/90 sm:p-6">
            <div class="grid gap-4 [grid-template-columns:repeat(auto-fit,minmax(min(100%,12rem),1fr))]">
                <div class="min-w-0">
                    <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500 lg:tracking-[0.24em]">Archivo</p>
                    <p class="mt-2 break-words text-lg font-semibold text-slate-950 dark:text-white">{{ $record->original_name }}</p>
                </div>

                <div class="min-w-0">
                    <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500 lg:tracking-[0.24em]">Estado</p>
                    <p class="mt-2 break-words text-lg font-semibold text-slate-950 dark:text-white">{{ $record->statusLabel() }}</p>
                </div>

                <div class="min-w-0">
                    <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500 lg:tracking-[0.24em]">Subido por</p>
                    <p class="mt-2 break-words text-lg font-semibold text-slate-950 dark:text-white">{{ $record->uploader?->name ?? 'Sistema' }}</p>
                </div>

                <div class="min-w-0">
                    <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500 lg:tracking-[0.24em]">Fecha de carga</p>
                    <p class="mt-2 break-words text-lg font-semibold text-slate-950 dark:text-white">{{ optional($record->uploaded_at)->format('d/m/Y H:i') ?? 'Sin fecha' }}</p>
                </div>

                <div class="min-w-0">
                    <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500 lg:tracking-[0.24em]">Sucursal</p>
                    <p class="mt-2 break-words text-lg font-semibold text-slate-950 dark:text-white">{{ $record->branchLabel() }}</p>
                </div>

                <div class="min-w-0">
                    <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500 lg:tracking-[0.24em]">Rider</p>
                    <p class="mt-2 break-words text-lg font-semibold text-slate-950 dark:text-white">
                        @if ($record->rider)
                            {{ $record->rider->rider_id }} - {{ $record->rider->name }}
                        @else
                            {{ data_get($record->metadata, 'parsed_riders.0.rider_id') ?? data_get($record->metadata, 'skipped_items.0.rider_id') ?? 'Sin rider vinculado' }}
                        @endif
                    </p>
                </div>

                <div class="min-w-0">
                    <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500 lg:tracking-[0.24em]">Movimientos procesados</p>
                    <p class="mt-2 text-3xl font-semibold text-[#C12A3A] dark:text-[#F0D98A]">{{ count($processedItems) }}</p>
                </div>

                <div class="min-w-0">
                    <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500 lg:tracking-[0.24em]">Puntos detectados</p>
                    <p class="mt-2 text-3xl font-semibold text-[#C12A3A] dark:text-[#F0D98A]">{{ number_format($parsedPoints) }}</p>
                </div>

                <div class="min-w-0">
                    <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500 lg:tracking-[0.24em]">Total de errores</p>
                    <p class="mt-2 text-3xl font-semibold text-[#C12A3A] dark:text-[#F0D98A]">{{ $record->importErrorCount() }}</p>
                </div>
            </div>

            <div class="mt-5 rounded-xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm text-slate-600 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300">
                {{ data_get($record->metadata, 'notes', 'Sin observaciones adicionales.') }}
            </div>
        </section>

        @if ($fatalErrors !== [])
            <section class="min-w-0 overflow-hidden rounded-2xl border border-red-200/70 bg-white/95 p-5 shadow-sm dark:border-red-900/60 dark:bg-slate-950/90 sm:p-6">
                <div class="mb-4">
                    <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Errores fatales</h2>
                    <p class="text-sm text-slate-500">Problemas que impidieron procesar el archivo completo.</p>
                </div>

                <div class="space-y-3">
                    @foreach ($fatalErrors as $error)
                        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-4 dark:border-red-900/60 dark:bg-red-950/20">
                            @if (filled(data_get($error, 'field')))
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-red-700 dark:text-red-300">{{ data_get($error, 'field') }}</p>
                            @endif
                            <p class="mt-1 text-sm text-slate-700 dark:text-slate-200">{{ data_get($error, 'reason', 'Error no especificado.') }}</p>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($skippedItems !== [])
            <section class="min-w-0 overflow-hidden rounded-2xl border border-amber-200/70 bg-white/95 p-5 shadow-sm dark:border-amber-900/60 dark:bg-slate-950/90 sm:p-6">
                <div class="mb-4">
                    <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Filas omitidas</h2>
                    <p class="text-sm text-slate-500">Detalle de registros parciales que no pudieron importarse.</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-[920px] table-fixed divide-y divide-slate-200 text-xs dark:divide-slate-800 sm:text-sm">
                        <thead>
                            <tr class="text-left text-xs uppercase tracking-[0.1em] text-slate-500 lg:tracking-[0.18em]">
                                <th class="w-24 pb-3 pr-4">Fila</th>
                                <th class="w-32 pb-3 pr-4">Rider</th>
                                <th class="w-32 pb-3 pr-4">Sucursal</th>
                                <th class="w-32 pb-3 pr-4">Articulo</th>
                                <th class="w-24 pb-3 pr-4">Litros</th>
                                <th class="pb-3">Motivo</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-900">
                            @foreach ($skippedItems as $item)
                                <tr>
                                    <td class="py-3 pr-4 text-slate-600 dark:text-slate-300">{{ data_get($item, 'row_number') ?? collect(data_get($item, 'row_numbers', []))->join(', ') ?: '-' }}</td>
                                    <td class="py-3 pr-4 text-slate-600 dark:text-slate-300">{{ data_get($item, 'rider_id', '-') }}</td>
                                    <td class="py-3 pr-4 text-slate-600 dark:text-slate-300">{{ data_get($item, 'branch', '-') ?? '-' }}</td>
                                    <td class="py-3 pr-4 text-slate-600 dark:text-slate-300">
                                        {{ data_get($item, 'article_code') ?? data_get($item, 'article_description') ?? '-' }}
                                    </td>
                                    <td class="py-3 pr-4 text-slate-600 dark:text-slate-300">{{ data_get($item, 'liters_raw') ?? data_get($item, 'points_total') ?? '-' }}</td>
                                    <td class="py-3 text-slate-700 dark:text-slate-200">{{ data_get($item, 'reason', 'Sin detalle.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @endif
    </div>
</x-filament-panels::page>
