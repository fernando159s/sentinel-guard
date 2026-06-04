<x-filament-panels::page>
    <x-filament::section>
        <form>
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; align-items: end;">
                {{ $this->form }}
            </div>

            <div style="display: flex; gap: 12px; margin-top: 16px;">
                <x-filament::button wire:click="generateInventario" icon="heroicon-o-document-arrow-down" size="lg">
                    PDF Inventario
                </x-filament::button>
                <x-filament::button wire:click="generateVencimientos" icon="heroicon-o-exclamation-triangle" color="warning" size="lg">
                    PDF Vencimientos
                </x-filament::button>
            </div>
        </form>
    </x-filament::section>

    <x-filament::section heading="Informacion" icon="heroicon-o-information-circle">
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Genera reportes PDF de los activos digitales (cuentas WhatsApp/Meta, suscripciones, licencias, dominios) con el branding de la empresa.
            <strong>Inventario</strong>: estadisticas, desglose por tipo y tabla completa de cuentas con costo, vencimiento, estado y responsables.
            <strong>Vencimientos</strong>: solo cuentas vencidas o por vencer en los proximos 30 dias.
            Por seguridad, los reportes nunca incluyen credenciales de acceso.
        </p>
    </x-filament::section>
</x-filament-panels::page>
