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
            const input = document.getElementById('riders-header-excel-input');
            if (input) input.value = '';
        },
    }"
    x-on:excel-uploaded.window="clearSelection()"
    x-on:excel-selection-cancelled.window="clearSelection()"
    class="space-y-4"
>
    <section
        class="overflow-hidden rounded-[2rem] shadow-[0_28px_80px_-48px_rgba(3,10,24,0.95)]"
        style="background: linear-gradient(180deg, rgba(10, 26, 47, 0.98), rgba(13, 28, 50, 0.94)); border: 1px solid rgba(63, 92, 121, 0.35);"
    >
        <div
            class="flex items-center justify-between gap-4 px-8 py-6"
            style="background: rgba(63, 92, 121, 0.24); border-bottom: 1px solid rgba(63, 92, 121, 0.35);"
        >
            <h1 class="text-[2.1rem] font-semibold tracking-tight text-white">Raiders </h1>

            @if (count($this->getCachedHeaderActions()))
                <div class="[&_.fi-btn]:rounded-xl [&_.fi-btn]:border-0 [&_.fi-btn]:bg-[linear-gradient(135deg,var(--repsol-orange),var(--repsol-red))] [&_.fi-btn]:px-5 [&_.fi-btn]:py-2.5 [&_.fi-btn]:text-sm [&_.fi-btn]:font-semibold [&_.fi-btn]:text-white">
                    <x-filament::actions
                        :actions="$this->getCachedHeaderActions()"
                        :alignment="$this->getHeaderActionsAlignment()"
                    />
                </div>
            @endif
        </div>

        <div class="flex flex-col gap-6 px-8 py-7 lg:flex-row lg:items-start lg:justify-between">
            <div class="flex flex-wrap gap-4">
                <article
                    class="w-[10.25rem] rounded-[1.2rem] px-5 py-4 shadow-[0_18px_30px_-24px_rgba(0,0,0,0.85)]"
                    style="background: rgba(63, 92, 121, 0.22); border: 1px solid rgba(63, 92, 121, 0.28);"
                >
                    <p class="text-[0.72rem] font-semibold uppercase tracking-[0.16em]" style="color: rgba(240, 217, 138, 0.82);">Riders</p>
                    <p class="mt-2 text-[2.05rem] font-semibold leading-none text-white">{{ number_format($totalRiders) }}</p>
                </article>

                <article
                    class="w-[10.25rem] rounded-[1.2rem] px-5 py-4 shadow-[0_18px_30px_-24px_rgba(0,0,0,0.85)]"
                    style="background: rgba(63, 92, 121, 0.22); border: 1px solid rgba(63, 92, 121, 0.28);"
                >
                    <p class="text-[0.72rem] font-semibold uppercase tracking-[0.16em]" style="color: rgba(240, 217, 138, 0.82);">Puntos totales</p>
                    <p class="mt-2 text-[2.05rem] font-semibold leading-none" style="color: var(--repsol-soft);">{{ number_format($totalPoints) }}</p>
                </article>
            </div>

            <article
                class="w-full max-w-[17rem] rounded-[1.2rem] px-5 py-4 shadow-[0_18px_30px_-24px_rgba(0,0,0,0.85)]"
                style="background: rgba(63, 92, 121, 0.22); border: 1px solid rgba(63, 92, 121, 0.28);"
            >
                <button
                    type="button"
                    onclick="document.getElementById('riders-header-excel-input').click()"
                    class="inline-flex items-center justify-center rounded-[0.9rem] bg-gradient-to-r from-[#E6932A] to-[#C12A3A] px-4 py-2.5 text-sm font-semibold text-white"
                >
                    Subir Excel
                </button>

                <input
                    id="riders-header-excel-input"
                    type="file"
                    accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                    wire:model="pendingExcel"
                    x-on:change="selectFile($event)"
                    class="hidden"
                />

                @error('pendingExcel')
                    <p class="mt-3 text-sm font-medium text-red-400">{{ $message }}</p>
                @enderror

                <p class="mt-3 text-sm" style="color: rgba(255, 255, 255, 0.68);" x-text="fileName || 'Sin archivo seleccionado'"></p>
            </article>
        </div>
    </section>

    <div x-cloak x-show="open" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 p-4 backdrop-blur-sm">
        <div class="w-full max-w-4xl rounded-[2rem] border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-800 dark:bg-slate-950">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Confirmar Excel</p>
                    <h3 class="mt-2 text-xl font-semibold text-slate-950 dark:text-white" x-text="fileName || 'Archivo seleccionado'"></h3>
                </div>
                <button type="button" x-on:click="clearSelection(); $wire.cancelExcelSelection()" class="rounded-full border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 dark:border-slate-700 dark:text-slate-200">
                    Cerrar
                </button>
            </div>

            <div class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 p-5 text-sm text-slate-600 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300">
                El sistema leerá la sucursal en <strong>A</strong>, el código del rider en <strong>B</strong>, el nombre en <strong>C</strong>, el artículo en <strong>E</strong>, la descripción en <strong>F</strong> y sumará el <strong>Total Puntos</strong> de <strong>J</strong> por rider y sucursal.
            </div>

            <div class="mt-5 flex justify-end gap-3">
                <button type="button" x-on:click="clearSelection(); $wire.cancelExcelSelection()" class="rounded-full border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 dark:border-slate-700 dark:text-slate-200">
                    Cancelar
                </button>
                <button type="button" wire:click="storeExcel" class="rounded-full bg-gradient-to-r from-[#E6932A] to-[#C12A3A] px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-orange-200 dark:shadow-orange-950/40">
                    Continuar
                </button>
            </div>
        </div>
    </div>
</div>
