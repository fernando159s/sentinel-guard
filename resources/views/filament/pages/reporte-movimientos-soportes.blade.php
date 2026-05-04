<x-filament-panels::page>
    <x-filament::section>
        <form wire:submit="generateReport">
            <div style="display: grid; grid-template-columns: 1fr auto; gap: 16px; align-items: end;">
                {{ $this->form }}

                <div style="padding-bottom: 2px;">
                    <x-filament::button type="submit" icon="heroicon-o-document-arrow-down" size="lg">
                        Generar PDF
                    </x-filament::button>
                </div>
            </div>
        </form>
    </x-filament::section>

    <x-filament::section heading="Informacion" icon="heroicon-o-information-circle">
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Genera un reporte PDF de todos los movimientos de soportes (Formato 8) segun la politica PSC000003.
            Incluye ingresos, asignaciones, transferencias, devoluciones, salidas a mantenimiento, home office y terceros.
            Filtro opcional por tipo de movimiento.
        </p>
    </x-filament::section>
</x-filament-panels::page>
