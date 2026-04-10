<x-filament-panels::page>
    <div class="overflow-hidden rounded-[2rem] border border-[rgba(63,92,121,0.14)] bg-white/95 shadow-sm dark:border-[rgba(63,92,121,0.36)] dark:bg-slate-900/90">
        <iframe
            src="{{ route('portal.discount.form') }}"
            title="Descuento de puntos"
            class="block h-[calc(100vh-6rem)] min-h-[1400px] w-full border-0 bg-transparent"
        ></iframe>
    </div>
</x-filament-panels::page>
