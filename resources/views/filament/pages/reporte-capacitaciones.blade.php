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
        <div style="background: rgba(255,255,255,0.05); border-radius: 12px; padding: 20px;">
            <div style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; color: #818cf8; font-weight: 600; margin-bottom: 8px;">
                Reporte de Capacitaciones de Ciberseguridad
            </div>
            <p style="font-size: 14px; color: #d1d5db; margin: 0;">
                Genera un PDF con todas las capacitaciones realizadas en el mes seleccionado, incluyendo metricas de asistencia y cumplimiento por usuario. Formato formal para presentar ante entes auditores (PSC000001).
            </p>
        </div>
    </x-filament::section>
</x-filament-panels::page>
