<x-filament-panels::page>
    <div
        x-data="{
            open: false,
            fileName: '',
            selectFile(event) {
                const [file] = event.target.files || [];
                if (!file) return;
                this.fileName = file.name;
                this.open = true;
            },
            clearSelection() {
                this.fileName = '';
                this.open = false;
                const input = document.getElementById('dashboard-excel-input');
                if (input) input.value = '';
            },
        }"
        x-on:excel-uploaded.window="clearSelection()"
        x-on:excel-selection-cancelled.window="clearSelection()"
        class="space-y-6"
    >
        {{-- ── Stat Cards ── --}}
        <section class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">

            {{-- Card: Riders --}}
            <article class="group relative overflow-hidden rounded-[2rem] border border-[rgba(63,92,121,0.14)] bg-white/95 p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-[rgba(63,92,121,0.36)] dark:bg-slate-900/90">
                <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-gradient-to-br from-[#E39B63]/15 to-[#C12A3A]/12 blur-2xl transition group-hover:scale-125"></div>
                <div class="relative flex items-start justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-[#3F5C79] dark:text-[#F0D98A]">Riders</p>
                        <p class="mt-3 text-4xl font-bold tracking-tight text-[#0A1A2F] dark:text-white">{{ number_format($totalRiders) }}</p>
                        <p class="mt-1.5 text-sm text-slate-500 dark:text-slate-300">Registrados en el sistema</p>
                    </div>
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-[#3F5C79] via-[#E39B63] to-[#C12A3A] text-white shadow-lg shadow-orange-200/50 dark:shadow-orange-900/30">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                    </span>
                </div>
                <div class="mt-4">
                    <a
                        href="{{ \App\Filament\Resources\Riders\RiderResource::getUrl('index') }}"
                        class="inline-flex items-center gap-1 text-xs font-semibold text-[#3F5C79] transition hover:text-[#C12A3A] dark:text-[#F0D98A] dark:hover:text-[#E39B63]"
                    >
                        Ver todos
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                    </a>
                </div>
            </article>

            {{-- Card: Puntos Totales --}}
            <article class="group relative overflow-hidden rounded-[2rem] border border-[rgba(63,92,121,0.14)] bg-white/95 p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-[rgba(63,92,121,0.36)] dark:bg-slate-900/90">
                <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-gradient-to-br from-[#C12A3A]/12 to-[#E39B63]/16 blur-2xl transition group-hover:scale-125"></div>
                <div class="relative flex items-start justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-[#3F5C79] dark:text-[#F0D98A]">Puntos Totales</p>
                        <p class="mt-3 text-4xl font-bold tracking-tight text-[#C12A3A] dark:text-[#F0D98A]">{{ number_format($totalPoints) }}</p>
                        <p class="mt-1.5 text-sm text-slate-500 dark:text-slate-300">Acumulados por todos los riders</p>
                    </div>
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-[#C12A3A] to-[#E39B63] text-white shadow-lg shadow-red-200/50 dark:shadow-red-900/30">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87L18.18 22 12 18.56 5.82 22 7 14.14l-5-4.87 6.91-1.01L12 2z"/>
                        </svg>
                    </span>
                </div>
                <div class="mt-4">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-green-50 px-2.5 py-1 text-xs font-semibold text-green-700 dark:bg-green-900/30 dark:text-green-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m18 15-6-6-6 6"/></svg>
                        Activo
                    </span>
                </div>
            </article>

            {{-- Card: Subir Excel --}}
            <article class="group relative overflow-hidden rounded-[2rem] border border-[rgba(63,92,121,0.14)] bg-white/95 p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-[rgba(63,92,121,0.36)] dark:bg-slate-900/90">
                <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-gradient-to-br from-[#F0D98A]/30 to-[#3F5C79]/15 blur-2xl transition group-hover:scale-125"></div>
                <div class="relative flex items-start justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-[#3F5C79] dark:text-[#F0D98A]">Subir Excel</p>
                        <p class="mt-2 text-sm leading-relaxed text-slate-500 dark:text-slate-300">Carga un Excel para leer la hoja REPORTE A SUBIR con código en A, nombre en B, artículo en D, descripción en E y Total Puntos en I.</p>
                    </div>
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-[#F6E8B7] text-[#0A1A2F] dark:bg-[#3F5C79]/35 dark:text-[#F0D98A]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/>
                            <path d="M14 2v6h6"/>
                            <path d="M12 18v-6"/>
                            <path d="m9 15 3-3 3 3"/>
                        </svg>
                    </span>
                </div>
                <div class="mt-5">
                    <button
                        type="button"
                        onclick="document.getElementById('dashboard-excel-input').click()"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-[#E39B63] via-[#FF7A45] to-[#C12A3A] px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-orange-200/50 transition hover:-translate-y-0.5 hover:shadow-lg dark:shadow-orange-900/30"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                        Seleccionar archivo
                    </button>
                </div>

                <input
                    id="dashboard-excel-input"
                    type="file"
                    accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                    wire:model="pendingExcel"
                    x-on:change="selectFile($event)"
                    class="hidden"
                />
            </article>

        </section>

        <section class="grid gap-6 xl:grid-cols-[1.6fr_1fr]">
            <article class="rounded-[2rem] border border-[rgba(63,92,121,0.14)] bg-white/95 p-6 shadow-sm dark:border-[rgba(63,92,121,0.36)] dark:bg-slate-950/90">
                <div class="mb-5">
                    <h3 class="text-lg font-semibold text-[#0A1A2F] dark:text-white">Riders registrados</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-300">Ultimos riders con puntos acumulados.</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                        <thead>
                            <tr class="text-left text-xs uppercase tracking-[0.18em] text-[#3F5C79] dark:text-[#F0D98A]">
                                <th class="pb-3 pr-4">ID</th>
                                <th class="pb-3 pr-4">Nombre</th>
                                <th class="pb-3 pr-4 text-right">Puntos</th>
                                <th class="pb-3 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-900">
                            @forelse ($riders as $rider)
                                <tr>
                                    <td class="py-4 pr-4 font-medium text-[#0A1A2F] dark:text-white">{{ $rider->rider_id }}</td>
                                    <td class="py-4 pr-4 text-slate-600 dark:text-slate-300">{{ $rider->name }}</td>
                                    <td class="py-4 pr-4 text-right font-semibold text-[#C12A3A] dark:text-[#F0D98A]">{{ number_format((int) $rider->points_balance) }}</td>
                                    <td class="py-4 text-right">
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ \App\Filament\Resources\Riders\RiderResource::getUrl('view', ['record' => $rider]) }}" class="inline-flex rounded-full border border-[#3F5C79]/30 px-3 py-1.5 text-xs font-semibold text-[#3F5C79] transition hover:border-[#E39B63] hover:text-[#C12A3A] dark:border-[#3F5C79]/60 dark:text-slate-200">
                                                Ver detalle
                                            </a>
                                            <a href="{{ \App\Filament\Resources\Riders\RiderResource::getUrl('edit', ['record' => $rider]) }}" class="inline-flex rounded-full bg-[#0A1A2F] px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-[#3F5C79] dark:bg-white dark:text-slate-950 dark:hover:bg-slate-200">
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

            <article class="rounded-[2rem] border border-[rgba(63,92,121,0.14)] bg-white/95 p-6 shadow-sm dark:border-[rgba(63,92,121,0.36)] dark:bg-slate-950/90">
                <h3 class="text-lg font-semibold text-[#0A1A2F] dark:text-white">Archivos recientes</h3>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-300">Importaciones registradas desde el dashboard.</p>

                <div class="mt-5 space-y-3">
                    @forelse ($recentDocuments as $document)
                        <div class="rounded-2xl border border-[#3F5C79]/12 bg-[#FFF8EB] px-4 py-3 dark:border-[#3F5C79]/30 dark:bg-slate-900">
                            <p class="font-medium text-[#0A1A2F] dark:text-white">{{ $document->original_name }}</p>
                            <p class="mt-1 text-xs uppercase tracking-[0.18em] text-[#3F5C79] dark:text-[#F0D98A]">{{ str_replace('_', ' ', $document->status) }}</p>
                            <p class="mt-2 text-sm text-slate-500 dark:text-slate-300">{{ optional($document->uploaded_at)->format('d/m/Y H:i') ?? 'Sin fecha' }}</p>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-300 px-4 py-8 text-center text-sm text-slate-500 dark:border-slate-700">
                            Todavía no hay archivos cargados.
                        </div>
                    @endforelse
                </div>
            </article>
        </section>

        <div x-cloak x-show="open" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 p-4 backdrop-blur-sm">
            <div class="w-full max-w-4xl rounded-[2rem] border border-[rgba(63,92,121,0.18)] bg-white p-6 shadow-2xl dark:border-[rgba(63,92,121,0.36)] dark:bg-slate-950">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[#3F5C79] dark:text-[#F0D98A]">Confirmar Excel</p>
                        <h3 class="mt-2 text-xl font-semibold text-[#0A1A2F] dark:text-white" x-text="fileName || 'Archivo seleccionado'"></h3>
                    </div>
                    <button type="button" x-on:click="clearSelection(); $wire.cancelExcelSelection()" class="rounded-full border border-[#3F5C79]/30 px-3 py-1.5 text-xs font-semibold text-[#3F5C79] dark:border-[#3F5C79]/60 dark:text-slate-200">
                        Cerrar
                    </button>
                </div>

                <div class="mt-5 rounded-2xl border border-[#3F5C79]/12 bg-[#FFF8EB] p-5 text-sm text-slate-600 dark:border-[#3F5C79]/30 dark:bg-slate-900 dark:text-slate-300">
                        El sistema buscará la hoja <strong>REPORTE A SUBIR</strong>, tomará el código del rider de <strong>A</strong>, el nombre de <strong>B</strong>, el artículo de <strong>D</strong>, la descripción de <strong>E</strong> y sumará el <strong>Total Puntos</strong> de <strong>I</strong> por rider.
                </div>

                @error('pendingExcel')
                    <p class="mt-3 text-sm font-medium text-red-600">{{ $message }}</p>
                @enderror

                <div class="mt-5 flex justify-end gap-3">
                    <button type="button" x-on:click="clearSelection(); $wire.cancelExcelSelection()" class="rounded-full border border-[#3F5C79]/30 px-5 py-3 text-sm font-semibold text-[#3F5C79] dark:border-[#3F5C79]/60 dark:text-slate-200">
                        Cancelar
                    </button>
                    <button type="button" wire:click="storeExcel" class="rounded-full bg-gradient-to-r from-[#E39B63] via-[#FF7A45] to-[#C12A3A] px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-orange-200 dark:shadow-orange-950/40">
                        Continuar
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
