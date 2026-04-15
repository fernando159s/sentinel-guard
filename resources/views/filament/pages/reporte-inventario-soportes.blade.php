<x-filament-panels::page>
    <x-filament::section>
        <form wire:submit="generateReport">
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr 1fr auto; gap: 16px; align-items: end;">
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
            Genera un reporte PDF completo del inventario de soportes de informacion (Formato 7) segun las politicas PSC000003 y PSC000004.
            Incluye pagina de estadisticas (totales, desglose por estado, tipo, sensibilidad, clasificacion de soporte y garantia),
            tabla general con todos los activos (codigo, tipo, categoria, marca/modelo, serie, ubicacion, sensibilidad, estado,
            usuario asignado, adquisicion y garantia), y fichas detalladas individuales por equipo con especificaciones tecnicas
            (SO, procesador, RAM, disco), asignacion vigente e historial completo de movimientos.
        </p>
    </x-filament::section>
</x-filament-panels::page>
