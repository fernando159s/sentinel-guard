<x-filament-panels::page>
    <x-filament::section>
        <form wire:submit="generateReport">
            <div style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 16px; align-items: end;">
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
            Genera un reporte PDF completo de destruccion de activos (Formato 13) segun las politicas PSC000003 y PSC000004.
            Incluye estadisticas por metodo de destruccion y motivo de baja, tabla resumen de todos los registros F13,
            y fichas detalladas individuales con la informacion del equipo vinculado. Filtros opcionales por metodo o motivo.
        </p>
    </x-filament::section>
</x-filament-panels::page>
