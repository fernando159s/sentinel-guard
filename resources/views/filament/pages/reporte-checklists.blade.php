<x-filament-panels::page>
    <x-filament::section>
        <form wire:submit="generateReport">
            <p class="text-sm text-gray-500 dark:text-gray-400" style="margin-bottom: 16px;">
                Genera un PDF con todas las plantillas de checklist y todas las ejecuciones realizadas sobre los equipos de la empresa.
            </p>
            <x-filament::button type="submit" icon="heroicon-o-document-arrow-down" size="lg">
                Generar PDF
            </x-filament::button>
        </form>
    </x-filament::section>

    <x-filament::section heading="Informacion" icon="heroicon-o-information-circle">
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Reporte completo del estado de checklists de cumplimiento por equipo. Incluye listado de plantillas activas,
            historial de ejecuciones con score (% cumplido), y ficha detallada de cada ejecucion con sus items y observaciones.
        </p>
    </x-filament::section>
</x-filament-panels::page>
