<x-filament-panels::page>
    <div
        x-data="{
            open: false,
            previewUrl: null,
            fileName: '',
            selectFile(event) {
                const [file] = event.target.files || [];
                if (!file) return;
                if (this.previewUrl) URL.revokeObjectURL(this.previewUrl);
                this.previewUrl = URL.createObjectURL(file);
                this.fileName = file.name;
                this.open = true;
            },
            clearSelection() {
                if (this.previewUrl) URL.revokeObjectURL(this.previewUrl);
                this.previewUrl = null;
                this.fileName = '';
                this.open = false;
                const input = document.getElementById('dashboard-pdf-input');
                if (input) input.value = '';
            },
        }"
        x-on:pdf-uploaded.window="clearSelection()"
        x-on:pdf-selection-cancelled.window="clearSelection()"
        class="space-y-6"
    >
        <section class="grid gap-4 xl:grid-cols-[1.5fr_1fr_1fr]">
            <article class="overflow-hidden rounded-[2rem] border border-slate-200/70 bg-white/95 p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950/90">
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Control central</p>
                <h2 class="mt-3 max-w-xl text-2xl font-semibold tracking-tight text-slate-950 dark:text-white">
                    Gestiona riders, puntos acumulados y PDFs globales sin salir del dashboard.
                </h2>
                <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-500">
                    La carga de PDFs queda lista para futura automatización, pero hoy solo registra el archivo y conserva el flujo visual de previsualización.
                </p>

                <div class="mt-6 flex flex-wrap gap-3">
                    <button
                        type="button"
                        onclick="document.getElementById('dashboard-pdf-input').click()"
                        class="inline-flex items-center justify-center rounded-full bg-gradient-to-r from-[#E6932A] to-[#C12A3A] px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-orange-200 transition hover:-translate-y-0.5 dark:shadow-orange-950/40"
                    >
                        Subir PDF
                    </button>

                    <a
                        href="{{ \App\Filament\Resources\Riders\RiderResource::getUrl('create') }}"
                        class="inline-flex items-center justify-center rounded-full border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-400 hover:text-slate-950 dark:border-slate-700 dark:text-slate-200 dark:hover:border-slate-500 dark:hover:text-white"
                    >
                        Crear rider
                    </a>
                </div>

                <input
                    id="dashboard-pdf-input"
                    type="file"
                    accept="application/pdf"
                    wire:model="pendingPdf"
                    x-on:change="selectFile($event)"
                    class="hidden"
                />
            </article>

            <article class="rounded-[2rem] border border-slate-200/70 bg-white/95 p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950/90">
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Riders registrados</p>
                <p class="mt-4 text-4xl font-semibold text-slate-950 dark:text-white">{{ number_format($totalRiders) }}</p>
                <p class="mt-2 text-sm text-slate-500">Total activo en la base administrativa.</p>
            </article>

            <article class="rounded-[2rem] border border-slate-200/70 bg-white/95 p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950/90">
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Puntos acumulados</p>
                <p class="mt-4 text-4xl font-semibold text-[#C12A3A] dark:text-[#F0D98A]">{{ number_format($totalPoints) }}</p>
                <p class="mt-2 text-sm text-slate-500">Suma calculada desde el historial de movimientos.</p>
            </article>
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.6fr_1fr]">
            <article class="rounded-[2rem] border border-slate-200/70 bg-white/95 p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950/90">
                <div class="mb-5">
                    <h3 class="text-lg font-semibold text-slate-950 dark:text-white">Riders registrados</h3>
                    <p class="text-sm text-slate-500">Tabla responsive con acceso directo a detalle y edición completa.</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                        <thead>
                            <tr class="text-left text-xs uppercase tracking-[0.18em] text-slate-500">
                                <th class="pb-3 pr-4">ID</th>
                                <th class="pb-3 pr-4">Nombre</th>
                                <th class="pb-3 pr-4 text-right">Puntos</th>
                                <th class="pb-3 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-900">
                            @forelse ($riders as $rider)
                                <tr>
                                    <td class="py-4 pr-4 font-medium text-slate-950 dark:text-white">{{ $rider->rider_id }}</td>
                                    <td class="py-4 pr-4 text-slate-600 dark:text-slate-300">{{ $rider->name }}</td>
                                    <td class="py-4 pr-4 text-right font-semibold text-[#C12A3A] dark:text-[#F0D98A]">{{ number_format((int) $rider->points_balance) }}</td>
                                    <td class="py-4 text-right">
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ \App\Filament\Resources\Riders\RiderResource::getUrl('view', ['record' => $rider]) }}" class="inline-flex rounded-full border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:border-slate-400 dark:border-slate-700 dark:text-slate-200">
                                                Ver detalle
                                            </a>
                                            <a href="{{ \App\Filament\Resources\Riders\RiderResource::getUrl('edit', ['record' => $rider]) }}" class="inline-flex rounded-full bg-slate-950 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-slate-800 dark:bg-white dark:text-slate-950 dark:hover:bg-slate-200">
                                                Editar
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-8 text-center text-slate-500">Aún no hay riders registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $riders->links() }}
                </div>
            </article>

            <article class="rounded-[2rem] border border-slate-200/70 bg-white/95 p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950/90">
                <h3 class="text-lg font-semibold text-slate-950 dark:text-white">PDFs recientes</h3>
                <p class="mt-1 text-sm text-slate-500">Cargas registradas visualmente desde el dashboard.</p>

                <div class="mt-5 space-y-3">
                    @forelse ($recentDocuments as $document)
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 dark:border-slate-800 dark:bg-slate-900">
                            <p class="font-medium text-slate-900 dark:text-white">{{ $document->original_name }}</p>
                            <p class="mt-1 text-xs uppercase tracking-[0.18em] text-slate-500">{{ str_replace('_', ' ', $document->status) }}</p>
                            <p class="mt-2 text-sm text-slate-500">{{ optional($document->uploaded_at)->format('d/m/Y H:i') ?? 'Sin fecha' }}</p>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-300 px-4 py-8 text-center text-sm text-slate-500 dark:border-slate-700">
                            Todavía no hay PDFs cargados.
                        </div>
                    @endforelse
                </div>
            </article>
        </section>

        <div x-cloak x-show="open" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 p-4 backdrop-blur-sm">
            <div class="w-full max-w-4xl rounded-[2rem] border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-800 dark:bg-slate-950">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Vista previa PDF</p>
                        <h3 class="mt-2 text-xl font-semibold text-slate-950 dark:text-white" x-text="fileName || 'Documento seleccionado'"></h3>
                    </div>
                    <button type="button" x-on:click="clearSelection(); $wire.cancelPdfSelection()" class="rounded-full border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 dark:border-slate-700 dark:text-slate-200">
                        Cerrar
                    </button>
                </div>

                <div class="mt-5 overflow-hidden rounded-2xl border border-slate-200 bg-slate-100 dark:border-slate-800 dark:bg-slate-900">
                    <iframe x-bind:src="previewUrl" class="h-[60vh] w-full" title="Vista previa del PDF"></iframe>
                </div>

                @error('pendingPdf')
                    <p class="mt-3 text-sm font-medium text-red-600">{{ $message }}</p>
                @enderror

                <div class="mt-5 flex justify-end gap-3">
                    <button type="button" x-on:click="clearSelection(); $wire.cancelPdfSelection()" class="rounded-full border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 dark:border-slate-700 dark:text-slate-200">
                        Cancelar
                    </button>
                    <button type="button" wire:click="storePdf" class="rounded-full bg-gradient-to-r from-[#E6932A] to-[#C12A3A] px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-orange-200 dark:shadow-orange-950/40">
                        Continuar
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
