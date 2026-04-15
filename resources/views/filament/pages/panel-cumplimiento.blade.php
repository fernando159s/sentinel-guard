<x-filament-panels::page>
    {{-- Stats --}}
    @php $stats = $this->getStats(); @endphp
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <x-filament::section>
            <div class="text-center">
                <div class="text-3xl font-bold text-primary-600">{{ $stats['total_politicas'] }}</div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Politicas obligatorias activas</div>
            </div>
        </x-filament::section>
        <x-filament::section>
            <div class="text-center">
                <div class="text-3xl font-bold {{ $stats['cumplimiento_general'] >= 80 ? 'text-success-600' : ($stats['cumplimiento_general'] >= 50 ? 'text-warning-600' : 'text-danger-600') }}">
                    {{ $stats['cumplimiento_general'] }}%
                </div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Cumplimiento general</div>
            </div>
        </x-filament::section>
        <x-filament::section>
            <div class="text-center">
                <div class="text-3xl font-bold {{ $stats['usuarios_pendientes'] > 0 ? 'text-warning-600' : 'text-success-600' }}">
                    {{ $stats['usuarios_pendientes'] }}
                </div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Usuarios con pendientes</div>
            </div>
        </x-filament::section>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Politicas list --}}
        <x-filament::section heading="Politicas obligatorias">
            @php $politicas = $this->getPoliticas(); @endphp

            @if($politicas->isEmpty())
                <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                    No hay politicas obligatorias activas
                </div>
            @else
                <div class="space-y-2">
                    @foreach($politicas as $politica)
                        <button
                            wire:click="selectPolitica({{ $politica->id }})"
                            class="w-full text-left p-3 rounded-lg border transition-colors
                                {{ $this->selectedPoliticaId === $politica->id
                                    ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20'
                                    : 'border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800' }}"
                        >
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="font-medium text-gray-900 dark:text-gray-100">{{ $politica->titulo }}</span>
                                    <span class="text-xs text-gray-500 ml-2">v{{ $politica->version }}</span>
                                    @if($politica->es_nda)
                                        <x-filament::badge size="sm" color="info" class="ml-1">NDA</x-filament::badge>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-sm text-gray-500">{{ $politica->aceptados }}/{{ $politica->total_usuarios }}</span>
                                    <x-filament::badge
                                        size="sm"
                                        :color="$politica->porcentaje >= 80 ? 'success' : ($politica->porcentaje >= 50 ? 'warning' : 'danger')"
                                    >
                                        {{ $politica->porcentaje }}%
                                    </x-filament::badge>
                                </div>
                            </div>
                        </button>
                    @endforeach
                </div>
            @endif
        </x-filament::section>

        {{-- User detail --}}
        <x-filament::section>
            @php $detalle = $this->getDetallePolitica(); @endphp

            @if(!$detalle)
                <div class="text-center py-12 text-gray-500 dark:text-gray-400">
                    <x-filament::icon icon="heroicon-o-cursor-arrow-rays" class="w-12 h-12 mx-auto mb-3 opacity-50" />
                    <p>Selecciona una politica para ver el detalle de cumplimiento</p>
                </div>
            @else
                @php
                    $politicaSeleccionada = $this->getPoliticas()->firstWhere('id', $this->selectedPoliticaId);
                    $pendientes = $detalle->filter(fn ($u) => $u->estado !== 'aceptada');
                @endphp

                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        {{ $politicaSeleccionada?->titulo ?? 'Detalle' }}
                    </h3>
                    @if($pendientes->isNotEmpty())
                        <x-filament::button
                            size="sm"
                            color="warning"
                            icon="heroicon-m-envelope"
                            wire:click="enviarRecordatorioMasivo"
                            wire:loading.attr="disabled"
                        >
                            Recordar a {{ $pendientes->count() }} pendientes
                        </x-filament::button>
                    @endif
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b dark:border-gray-700">
                                <th class="text-left py-2 px-3 text-gray-500 dark:text-gray-400 font-medium">Usuario</th>
                                <th class="text-left py-2 px-3 text-gray-500 dark:text-gray-400 font-medium">Estado</th>
                                <th class="text-left py-2 px-3 text-gray-500 dark:text-gray-400 font-medium">Fecha</th>
                                <th class="text-right py-2 px-3 text-gray-500 dark:text-gray-400 font-medium">Accion</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($detalle as $usuario)
                                <tr class="border-b dark:border-gray-700/50">
                                    <td class="py-2 px-3">
                                        <div class="font-medium text-gray-900 dark:text-gray-100">{{ $usuario->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $usuario->email }}</div>
                                    </td>
                                    <td class="py-2 px-3">
                                        @if($usuario->estado === 'aceptada')
                                            <x-filament::badge size="sm" color="success">Aceptada</x-filament::badge>
                                        @elseif($usuario->estado === 'vencida')
                                            <x-filament::badge size="sm" color="danger">Vencida</x-filament::badge>
                                        @else
                                            <x-filament::badge size="sm" color="warning">Pendiente</x-filament::badge>
                                        @endif
                                    </td>
                                    <td class="py-2 px-3 text-gray-500">
                                        @if($usuario->fecha_aceptacion)
                                            {{ $usuario->fecha_aceptacion }}
                                            @if($usuario->fecha_expiracion)
                                                <div class="text-xs">Vence: {{ $usuario->fecha_expiracion }}</div>
                                            @endif
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="py-2 px-3 text-right">
                                        @if($usuario->estado !== 'aceptada')
                                            <x-filament::button
                                                size="xs"
                                                color="gray"
                                                icon="heroicon-m-envelope"
                                                wire:click="enviarRecordatorio({{ $usuario->user_id }})"
                                                wire:loading.attr="disabled"
                                            >
                                                Recordar
                                            </x-filament::button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-filament::section>
    </div>
</x-filament-panels::page>
