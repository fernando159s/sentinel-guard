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

    <x-filament::section heading="Información" icon="heroicon-o-information-circle">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div style="background: rgba(255,255,255,0.05); border-radius: 12px; padding: 20px;">
                <div style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; color: #818cf8; font-weight: 600; margin-bottom: 8px;">
                    F09 — Incidencias
                </div>
                <p style="font-size: 14px; color: #d1d5db; margin: 0;">
                    Reporte de notificaciones de incidencias de seguridad registradas en el mes seleccionado, según política PSC000001 / PSC000-25.
                </p>
            </div>
            <div style="background: rgba(255,255,255,0.05); border-radius: 12px; padding: 20px;">
                <div style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; color: #34d399; font-weight: 600; margin-bottom: 8px;">
                    F10 — Resoluciones
                </div>
                <p style="font-size: 14px; color: #d1d5db; margin: 0;">
                    Reporte de resoluciones de incidencias registradas en el mes seleccionado, según política PSC000-25.
                </p>
            </div>
        </div>
    </x-filament::section>
</x-filament-panels::page>
