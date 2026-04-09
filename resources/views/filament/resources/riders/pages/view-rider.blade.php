<x-filament-panels::page>
    @php($record = $this->getRecord())
    @php($creationLabel = $record->creation_source === 'excel' ? 'Excel' : ($record->creator?->name ?? 'Sistema'))
    @php($lastEditorLabel = $record->editor?->name)

    <div class="grid gap-6">
        <section class="rounded-3xl border border-slate-200/70 bg-white/95 p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950/90">
            <div class="grid gap-4 md:grid-cols-4 xl:grid-cols-8">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">ID</p>
                    <p class="mt-2 text-lg font-semibold text-slate-950 dark:text-white">{{ $record->rider_id }}</p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Nombre</p>
                    <p class="mt-2 text-lg font-semibold text-slate-950 dark:text-white">{{ $record->name }}</p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Sucursal</p>
                    <p class="mt-2 text-lg font-semibold text-slate-950 dark:text-white">{{ $record->branch ?? 'Sin sucursal' }}</p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Rango</p>
                    <p class="mt-2 text-lg font-semibold text-slate-950 dark:text-white">{{ $record->rango ?? 'Sin rango' }}</p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Puntos acumulados</p>
                    <p class="mt-2 text-3xl font-semibold text-[#C12A3A] dark:text-[#F0D98A]">{{ number_format((int) $record->points_balance) }}</p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Creado por</p>
                    <p class="mt-2 text-lg font-semibold text-slate-950 dark:text-white">{{ $creationLabel }}</p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Fecha de creación</p>
                    <p class="mt-2 text-lg font-semibold text-slate-950 dark:text-white">{{ optional($record->created_at)->format('d/m/Y H:i') ?? '-' }}</p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Última edición por</p>
                    <p class="mt-2 text-lg font-semibold text-slate-950 dark:text-white">{{ $lastEditorLabel ?? 'Sin ediciones manuales' }}</p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Última edición</p>
                    <p class="mt-2 text-lg font-semibold text-slate-950 dark:text-white">
                        {{ $lastEditorLabel ? (optional($record->updated_at)->format('d/m/Y H:i') ?? '-') : 'Sin ediciones manuales' }}
                    </p>
                </div>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.6fr_1fr]">
            <article class="rounded-3xl border border-slate-200/70 bg-white/95 p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950/90">
                <div class="mb-4">
                    <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Historial de compras y movimientos</h2>
                    <p class="text-sm text-slate-500">Los puntos se calculan automáticamente desde estos registros.</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                        <thead>
                            <tr class="text-left text-xs uppercase tracking-[0.18em] text-slate-500">
                                <th class="pb-3 pr-4">Fecha</th>
                                <th class="pb-3 pr-4">Registrado por</th>
                                <th class="pb-3 pr-4">Sucursal</th>
                                <th class="pb-3 pr-4">Referencia</th>
                                <th class="pb-3 pr-4">Detalle</th>
                                <th class="pb-3 pr-4 text-right">Monto</th>
                                <th class="pb-3 text-right">Puntos</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-900">
                            @forelse ($record->movements as $movement)
                                @php($source = data_get($movement->metadata, 'source'))
                                @php($actorLabel = $source === 'excel_auto_import' ? 'Excel' : ($movement->actor?->name ?? 'Sistema'))
                                <tr>
                                    <td class="py-3 pr-4 text-slate-600 dark:text-slate-300">{{ optional($movement->occurred_at)->format('d/m/Y') ?? '-' }}</td>
                                    <td class="py-3 pr-4 text-slate-600 dark:text-slate-300">{{ $actorLabel }}</td>
                                    <td class="py-3 pr-4 text-slate-600 dark:text-slate-300">{{ $movement->branch ?? '-' }}</td>
                                    <td class="py-3 pr-4 font-medium text-slate-900 dark:text-white">{{ $movement->reference ?? '-' }}</td>
                                    <td class="py-3 pr-4 text-slate-600 dark:text-slate-300">{{ $movement->description ?? ucfirst($movement->movement_type) }}</td>
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

            <article class="rounded-3xl border border-slate-200/70 bg-white/95 p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950/90">
                <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Documentos relacionados</h2>
                <p class="mt-1 text-sm text-slate-500">Vista preparada para futuras asignaciones automáticas desde PDFs globales.</p>

                <div class="mt-5 space-y-3">
                    @forelse ($record->documents as $document)
                        @php($previewUrl = URL::signedRoute('documents.preview', ['document' => $document]))

                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 dark:border-slate-800 dark:bg-slate-900">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-medium text-slate-900 dark:text-white">{{ $document->original_name }}</p>
                                    <p class="mt-1 text-xs uppercase tracking-[0.18em] text-slate-500">{{ str_replace('_', ' ', $document->status) }}</p>
                                    <p class="mt-2 text-sm text-slate-500">{{ optional($document->uploaded_at)->format('d/m/Y H:i') ?? 'Sin fecha' }}</p>
                                    <p class="mt-1 text-sm text-slate-500">Subido por: {{ $document->uploader?->name ?? 'Sistema' }}</p>
                                </div>

                                <a
                                    href="{{ $previewUrl }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-flex shrink-0 items-center rounded-xl border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:border-[#C12A3A] hover:text-[#C12A3A] dark:border-slate-700 dark:text-slate-200"
                                >
                                    Ver PDF
                                </a>
                            </div>

                            <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-950">
                                <iframe
                                    src="{{ $previewUrl }}"
                                    title="Vista previa de {{ $document->original_name }}"
                                    class="h-96 w-full bg-white dark:bg-slate-950"
                                ></iframe>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-300 px-4 py-8 text-center text-sm text-slate-500 dark:border-slate-700">
                            No hay PDFs vinculados a este rider por el momento.
                        </div>
                    @endforelse
                </div>
            </article>
        </section>
    </div>
</x-filament-panels::page>
