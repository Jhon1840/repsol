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

            {{-- Card: Promedio de puntos --}}
            <article class="group relative overflow-hidden rounded-[2rem] border border-[rgba(63,92,121,0.14)] bg-white/95 p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-[rgba(63,92,121,0.36)] dark:bg-slate-900/90">
                <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-gradient-to-br from-[#C12A3A]/12 to-[#E39B63]/16 blur-2xl transition group-hover:scale-125"></div>
                <div class="relative flex items-start justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-[#3F5C79] dark:text-[#F0D98A]">Promedio de puntos</p>
                        <p class="mt-3 text-4xl font-bold tracking-tight text-[#C12A3A] dark:text-[#F0D98A]">{{ number_format($averagePoints, 0) }}</p>
                        <p class="mt-1.5 text-sm text-slate-500 dark:text-slate-300">Promedio por rider registrado</p>
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

        <section class="rounded-[2rem] border border-[rgba(63,92,121,0.14)] bg-white/95 p-6 shadow-sm dark:border-[rgba(63,92,121,0.36)] dark:bg-slate-950/90">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-[#3F5C79] dark:text-[#F0D98A]">KPI de puntos</p>
                    <h3 class="mt-2 text-lg font-semibold text-[#0A1A2F] dark:text-white">Puntos cargados vs gastados</h3>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-300">Compara los puntos sumados contra los puntos descontados en el rango seleccionado.</p>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <label class="text-xs font-semibold uppercase tracking-[0.18em] text-[#3F5C79] dark:text-[#F0D98A]">
                        Desde
                        <input
                            type="date"
                            wire:model.live="pointsChartStartDate"
                            class="mt-2 block w-full rounded-2xl border border-[#3F5C79]/20 bg-white px-4 py-2.5 text-sm font-medium normal-case tracking-normal text-[#0A1A2F] shadow-sm outline-none transition focus:border-[#E39B63] focus:ring-2 focus:ring-[#E39B63]/20 dark:border-[#3F5C79]/50 dark:bg-slate-900 dark:text-white"
                        />
                    </label>
                    <label class="text-xs font-semibold uppercase tracking-[0.18em] text-[#3F5C79] dark:text-[#F0D98A]">
                        Hasta
                        <input
                            type="date"
                            wire:model.live="pointsChartEndDate"
                            class="mt-2 block w-full rounded-2xl border border-[#3F5C79]/20 bg-white px-4 py-2.5 text-sm font-medium normal-case tracking-normal text-[#0A1A2F] shadow-sm outline-none transition focus:border-[#E39B63] focus:ring-2 focus:ring-[#E39B63]/20 dark:border-[#3F5C79]/50 dark:bg-slate-900 dark:text-white"
                        />
                    </label>
                </div>
            </div>

            <div class="mt-6 grid gap-4 lg:grid-cols-[1fr_auto] lg:items-center">
                <div class="grid gap-3 sm:grid-cols-2 text-sm">
                    <div class="rounded-2xl border border-[#3F5C79]/20 bg-[#3F5C79]/5 px-4 py-3 dark:border-[#3F5C79]/40 dark:bg-[#3F5C79]/20">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-[#3F5C79] dark:text-[#F0D98A]">Cargados</p>
                        <p class="mt-1 text-xl font-bold tracking-tight text-[#1F3E5B] dark:text-white">{{ number_format($pointsChart['loadedTotal']) }}</p>
                    </div>
                    <div class="rounded-2xl border border-[#C12A3A]/20 bg-[#C12A3A]/5 px-4 py-3 dark:border-[#C12A3A]/35 dark:bg-[#C12A3A]/15">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-[#C12A3A] dark:text-[#F0D98A]">Gastados / reducidos</p>
                        <p class="mt-1 text-xl font-bold tracking-tight text-[#9B1F2C] dark:text-white">{{ number_format($pointsChart['spentTotal']) }}</p>
                    </div>
                </div>
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">Valor maximo: {{ number_format($pointsChart['maxValue']) }} puntos</p>
            </div>

            <div
                id="dashboard-points-chart-wrapper"
                data-points-chart='@json($pointsChart)'
                class="relative mt-5 h-[420px] overflow-hidden rounded-[1.5rem] border border-[#E7DCC2] bg-gradient-to-b from-[#FFF8EB] to-[#FFF3D8] p-4 dark:border-slate-800 dark:bg-slate-900"
                style="height: 420px;"
                x-data
                x-init="$nextTick(() => window.renderDashboardPointsChart?.())"
                x-effect="$wire.pointsChartStartDate; $wire.pointsChartEndDate; $nextTick(() => window.renderDashboardPointsChart?.())"
            >
                <div
                    id="dashboard-points-chart"
                    class="relative z-0 w-full"
                    style="height: 380px; min-height: 380px;"
                    wire:ignore
                ></div>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.6fr_1fr]">
            <article class="rounded-[2rem] border border-[rgba(63,92,121,0.14)] bg-white/95 p-6 shadow-sm dark:border-[rgba(63,92,121,0.36)] dark:bg-slate-950/90">
                <div class="mb-5">
                    <h3 class="text-lg font-semibold text-[#0A1A2F] dark:text-white">Top 5 riders</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-300">Riders con mas puntos acumulados.</p>
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

        @once
            <script src="https://cdn.canvasjs.com/canvasjs.min.js"></script>
            <script>
                window.renderDashboardPointsChart = function () {
                    const wrapper = document.getElementById('dashboard-points-chart-wrapper');
                    const container = document.getElementById('dashboard-points-chart');

                    if (!wrapper || !container || !window.CanvasJS) {
                        return;
                    }

                    const chartData = JSON.parse(wrapper.dataset.pointsChart || '{"rows":[]}');
                    const rows = chartData.rows || [];
                    const dateFormat = rows.length > 8 ? 'MMM YYYY' : 'DD MMM YYYY';
                    const toDataPoints = (key) => rows.map((row) => ({
                        x: new Date(`${row.date}T00:00:00`),
                        y: Number(row[key] || 0),
                        label: row.label,
                    }));

                    if (container.__dashboardPointsChart) {
                        container.__dashboardPointsChart.destroy();
                        container.innerHTML = '';
                    }

                    const chart = new CanvasJS.Chart(container, {
                        animationEnabled: true,
                        backgroundColor: 'transparent',
                        culture: 'es',
                        theme: 'light2',
                        axisX: {
                            labelFontColor: '#0A1A2F',
                            labelFontWeight: 'bold',
                            labelAngle: rows.length > 8 ? -35 : 0,
                            valueFormatString: rows.length > 8 ? 'MMM YYYY' : 'DD MMM',
                            lineColor: '#CBD5E1',
                            tickColor: '#CBD5E1',
                        },
                        axisY: {
                            title: 'Puntos',
                            titleFontColor: '#3F5C79',
                            labelFontColor: '#0A1A2F',
                            gridColor: 'rgba(63, 92, 121, 0.14)',
                            includeZero: true,
                            minimum: 0,
                        },
                        legend: {
                            cursor: 'pointer',
                            fontColor: '#0A1A2F',
                            itemclick: function (event) {
                                event.dataSeries.visible = !(typeof event.dataSeries.visible === 'undefined' || event.dataSeries.visible);
                                event.chart.render();
                            },
                        },
                        toolTip: {
                            shared: true,
                            contentFormatter: function (event) {
                                const dateLabel = CanvasJS.formatDate(event.entries[0].dataPoint.x, dateFormat);
                                const lines = event.entries.map((entry) => `${entry.dataSeries.name}: <strong>${CanvasJS.formatNumber(entry.dataPoint.y, '#,##0')}</strong>`);

                                return `<strong>${dateLabel}</strong><br>${lines.join('<br>')}`;
                            },
                        },
                        data: [
                            {
                                type: 'spline',
                                name: 'Cargados',
                                showInLegend: true,
                                color: '#3F5C79',
                                markerSize: 8,
                                lineThickness: 4,
                                yValueFormatString: '#,##0 puntos',
                                dataPoints: toDataPoints('loaded'),
                            },
                            {
                                type: 'spline',
                                name: 'Gastados/reducidos',
                                showInLegend: true,
                                color: '#C12A3A',
                                markerSize: 8,
                                lineThickness: 4,
                                yValueFormatString: '#,##0 puntos',
                                dataPoints: toDataPoints('spent'),
                            },
                        ],
                    });

                    chart.render();
                    container.__dashboardPointsChart = chart;
                };

                window.renderDashboardPointsChart();

                document.addEventListener('livewire:init', () => {
                    Livewire.hook('morph.updated', ({ el }) => {
                        if (el.id === 'dashboard-points-chart-wrapper' || el.querySelector?.('#dashboard-points-chart-wrapper')) {
                            window.requestAnimationFrame(() => window.renderDashboardPointsChart());
                        }
                    });
                });
            </script>
        @endonce
    </div>
</x-filament-panels::page>
