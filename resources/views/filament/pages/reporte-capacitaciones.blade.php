<x-filament-panels::page>
    <x-filament::section>
        <form wire:submit="generateReport">
            <p class="text-sm text-gray-500 dark:text-gray-400" style="margin-bottom: 16px;">
                Genera un PDF con todas las capacitaciones y la asistencia de los usuarios.
            </p>
            <x-filament::button type="submit" icon="heroicon-o-document-arrow-down" size="lg">
                Generar PDF
            </x-filament::button>
        </form>
    </x-filament::section>

    <x-filament::section heading="Informacion" icon="heroicon-o-information-circle">
        <div style="background: rgba(255,255,255,0.05); border-radius: 12px; padding: 20px;">
            <div style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; color: #818cf8; font-weight: 600; margin-bottom: 8px;">
                Reporte de Capacitaciones de Ciberseguridad
            </div>
            <p style="font-size: 14px; color: #d1d5db; margin: 0;">
                Genera un PDF con todas las capacitaciones realizadas, incluyendo metricas de asistencia y cumplimiento por usuario. Formato formal para presentar ante entes auditores (PSC000001).
            </p>
        </div>
    </x-filament::section>
</x-filament-panels::page>
