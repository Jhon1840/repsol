<x-filament-panels::page>
    @php($record = $this->getRecord())
    @php($creationLabel = $record->creation_source === 'excel' ? 'Excel' : ($record->creator?->name ?? 'Sistema'))
    @php($lastEditorLabel = $record->editor?->name)
    @php($relatedDocuments = $this->getRelatedDocuments())

    <div class="grid min-w-0 gap-6">
        <section class="min-w-0 rounded-2xl border border-slate-200/70 bg-white/95 p-5 shadow-sm dark:border-slate-800 dark:bg-slate-950/90 sm:p-6">
            <div class="grid gap-4 [grid-template-columns:repeat(auto-fit,minmax(min(100%,11rem),1fr))]">
                <div class="min-w-0">
                    <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500 lg:tracking-[0.24em]">ID</p>
                    <p class="mt-2 break-words text-lg font-semibold text-slate-950 dark:text-white">{{ $record->rider_id }}</p>
                </div>

                <div class="min-w-0">
                    <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500 lg:tracking-[0.24em]">Nombre</p>
                    <p class="mt-2 break-words text-lg font-semibold text-slate-950 dark:text-white">{{ $record->name }}</p>
                </div>

                <div class="min-w-0">
                    <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500 lg:tracking-[0.24em]">Sucursal</p>
                    <p class="mt-2 break-words text-lg font-semibold text-slate-950 dark:text-white">{{ $record->branch ?? 'Sin sucursal' }}</p>
                </div>

                <div class="min-w-0">
                    <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500 lg:tracking-[0.24em]">Rango</p>
                    <p class="mt-2 break-words text-lg font-semibold text-slate-950 dark:text-white">{{ $record->rango ?? 'Sin rango' }}</p>
                </div>

                <div class="min-w-0">
                    <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500 lg:tracking-[0.24em]">Puntos acumulados</p>
                    <p class="mt-2 text-3xl font-semibold text-[#C12A3A] dark:text-[#F0D98A]">{{ number_format((int) $record->points_balance) }}</p>
                </div>

                <div class="min-w-0">
                    <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500 lg:tracking-[0.24em]">Creado por</p>
                    <p class="mt-2 break-words text-lg font-semibold text-slate-950 dark:text-white">{{ $creationLabel }}</p>
                </div>

                <div class="min-w-0">
                    <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500 lg:tracking-[0.24em]">Fecha de creación</p>
                    <p class="mt-2 break-words text-lg font-semibold text-slate-950 dark:text-white">{{ optional($record->created_at)->format('d/m/Y H:i') ?? '-' }}</p>
                </div>

                <div class="min-w-0">
                    <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500 lg:tracking-[0.24em]">Última edición por</p>
                    <p class="mt-2 break-words text-lg font-semibold text-slate-950 dark:text-white">{{ $lastEditorLabel ?? 'Sin ediciones manuales' }}</p>
                </div>

                <div class="min-w-0">
                    <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500 lg:tracking-[0.24em]">Última edición</p>
                    <p class="mt-2 break-words text-lg font-semibold text-slate-950 dark:text-white">
                        {{ $lastEditorLabel ? (optional($record->updated_at)->format('d/m/Y H:i') ?? '-') : 'Sin ediciones manuales' }}
                    </p>
                </div>
            </div>
        </section>

        <section class="grid min-w-0 gap-6 2xl:grid-cols-[minmax(0,1fr)_minmax(320px,420px)]">
            <article class="min-w-0 overflow-hidden rounded-2xl border border-slate-200/70 bg-white/95 p-5 shadow-sm dark:border-slate-800 dark:bg-slate-950/90 sm:p-6">
                <div class="mb-4">
                    <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Historial de compras y movimientos</h2>
                    <p class="text-sm text-slate-500">Los puntos se calculan automáticamente desde estos registros.</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-[760px] table-fixed divide-y divide-slate-200 text-xs dark:divide-slate-800 lg:min-w-[920px] sm:text-sm">
                        <thead>
                            <tr class="text-left text-xs uppercase tracking-[0.1em] text-slate-500 lg:tracking-[0.18em]">
                                <th class="w-28 pb-3 pr-4">Fecha</th>
                                <th class="w-36 pb-3 pr-4">Registrado por</th>
                                <th class="w-28 pb-3 pr-4">Sucursal</th>
                                <th class="w-48 pb-3 pr-4">Referencia</th>
                                <th class="pb-3 pr-4">Detalle</th>
                                <th class="w-24 pb-3 pr-4 text-right">Monto</th>
                                <th class="w-24 pb-3 text-right">Puntos</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-900">
                            @forelse ($record->movements as $movement)
                                @php($source = data_get($movement->metadata, 'source'))
                                @php($actorLabel = $source === 'excel_auto_import' ? 'Excel' : ($movement->actor?->name ?? 'Sistema'))
                                <tr>
                                    <td class="py-3 pr-4 text-slate-600 dark:text-slate-300">{{ optional($movement->occurred_at)->format('d/m/Y') ?? '-' }}</td>
                                    <td class="py-3 pr-4 text-slate-600 dark:text-slate-300">
                                        <span class="block truncate">{{ $actorLabel }}</span>
                                    </td>
                                    <td class="py-3 pr-4 text-slate-600 dark:text-slate-300">
                                        <span class="block truncate">{{ $movement->branch ?? '-' }}</span>
                                    </td>
                                    <td class="py-3 pr-4 font-medium text-slate-900 dark:text-white">
                                        <span class="block truncate" title="{{ $movement->reference ?? '-' }}">{{ $movement->reference ?? '-' }}</span>
                                    </td>
                                    <td class="py-3 pr-4 text-slate-600 dark:text-slate-300">
                                        <span class="line-clamp-2">{{ $movement->description ?? ucfirst($movement->movement_type) }}</span>
                                    </td>
                                    <td class="py-3 pr-4 text-right text-slate-600 dark:text-slate-300">{{ $movement->amount ? number_format((float) $movement->amount, 2) : '-' }}</td>
                                    <td class="py-3 text-right font-semibold text-[#C12A3A] dark:text-[#F0D98A]">{{ number_format($movement->points) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-6 text-center text-slate-500">Todavía no hay movimientos asociados a este rider.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </article>

            <article class="min-w-0 overflow-hidden rounded-2xl border border-slate-200/70 bg-white/95 p-5 shadow-sm dark:border-slate-800 dark:bg-slate-950/90 sm:p-6">
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Documentos relacionados</h2>
                        <p class="mt-1 text-sm text-slate-500">Archivos originales guardados para auditoría interna.</p>
                    </div>

                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600 dark:bg-slate-900 dark:text-slate-300">
                        {{ $relatedDocuments->count() }} archivo(s)
                    </span>
                </div>

                <div class="mt-5 space-y-3">
                    @forelse ($relatedDocuments as $document)
                        @php($downloadUrl = URL::signedRoute('documents.download', ['document' => $document]))
                        @php($processedItems = count(data_get($document->metadata, 'processed_items', [])))
                        @php($skippedItems = count(data_get($document->metadata, 'skipped_items', [])))
                        @php($parsedPoints = (int) data_get($document->metadata, 'parsed_points', 0))

                        <div class="min-w-0 rounded-xl border border-slate-200 bg-slate-50 px-4 py-4 dark:border-slate-800 dark:bg-slate-900">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                <div class="min-w-0">
                                    <p class="truncate font-semibold text-slate-900 dark:text-white">{{ $document->original_name }}</p>

                                    <div class="mt-2 flex flex-wrap gap-2 text-xs font-semibold">
                                        <span class="rounded-full bg-white px-2.5 py-1 text-slate-600 ring-1 ring-slate-200 dark:bg-slate-950 dark:text-slate-300 dark:ring-slate-800">
                                            {{ str_replace('_', ' ', $document->status) }}
                                        </span>
                                        <span class="rounded-full bg-white px-2.5 py-1 text-slate-600 ring-1 ring-slate-200 dark:bg-slate-950 dark:text-slate-300 dark:ring-slate-800">
                                            {{ number_format($parsedPoints) }} pts
                                        </span>
                                    </div>

                                    <dl class="mt-4 grid min-w-0 gap-3 text-sm text-slate-500 sm:grid-cols-2 2xl:grid-cols-1">
                                        <div>
                                            <dt class="text-xs font-semibold uppercase tracking-[0.18em]">Subido</dt>
                                            <dd class="mt-1 text-slate-700 dark:text-slate-200">{{ optional($document->uploaded_at)->format('d/m/Y H:i') ?? 'Sin fecha' }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-xs font-semibold uppercase tracking-[0.18em]">Subido por</dt>
                                            <dd class="mt-1 text-slate-700 dark:text-slate-200">{{ $document->uploader?->name ?? 'Sistema' }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-xs font-semibold uppercase tracking-[0.18em]">Movimientos</dt>
                                            <dd class="mt-1 text-slate-700 dark:text-slate-200">{{ $processedItems }} procesado(s)</dd>
                                        </div>
                                        <div>
                                            <dt class="text-xs font-semibold uppercase tracking-[0.18em]">Omitidas</dt>
                                            <dd class="mt-1 text-slate-700 dark:text-slate-200">{{ $skippedItems }} fila(s)</dd>
                                        </div>
                                    </dl>
                                </div>

                                <a
                                    href="{{ $downloadUrl }}"
                                    class="inline-flex shrink-0 items-center justify-center rounded-lg bg-[#0A1A2F] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#3F5C79] dark:bg-white dark:text-slate-950 dark:hover:bg-slate-200"
                                >
                                    Descargar
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-xl border border-dashed border-slate-300 px-4 py-8 text-center text-sm text-slate-500 dark:border-slate-700">
                            No hay documentos vinculados a este rider por el momento.
                        </div>
                    @endforelse
                </div>
            </article>
        </section>
    </div>
</x-filament-panels::page>
