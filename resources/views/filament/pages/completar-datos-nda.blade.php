<x-filament-panels::page>
    @php
        $nda = $this->getNdaPendiente();
    @endphp

    <div style="max-width:540px; margin:0 auto;">
        <div style="border-radius:12px; overflow:hidden;" class="bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">

            {{-- Header --}}
            <div style="padding:20px 24px; border-bottom:1px solid rgba(128,128,128,0.15); text-align:center;">
                <div style="width:48px; height:48px; border-radius:12px; display:inline-flex; align-items:center; justify-content:center; margin-bottom:12px;" class="bg-warning-50 dark:bg-warning-500/10">
                    <x-heroicon-o-identification style="width:24px; height:24px;" class="text-warning-600 dark:text-warning-400" />
                </div>
                <div class="text-base font-semibold text-gray-900 dark:text-white">Datos personales requeridos</div>
                <div class="text-sm text-gray-500 dark:text-gray-400" style="margin-top:4px;">
                    @if ($nda)
                        Para firmar <strong>{{ $nda->titulo }}</strong> necesitamos tus datos personales.
                    @else
                        Completa tus datos personales para continuar.
                    @endif
                </div>
            </div>

            {{-- Form --}}
            <div style="padding:24px; display:flex; flex-direction:column; gap:16px;">

                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300" style="display:block; margin-bottom:4px;">DNI / Documento de identidad *</label>
                    <input type="text" wire:model="dni" placeholder="Ej: 12345678"
                           class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white">
                    @error('dni') <span class="text-xs text-danger-600 dark:text-danger-400">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300" style="display:block; margin-bottom:4px;">Direccion *</label>
                    <input type="text" wire:model="direccion" placeholder="Ej: Av. Javier Prado 1234, Lima"
                           class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white">
                    @error('direccion') <span class="text-xs text-danger-600 dark:text-danger-400">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300" style="display:block; margin-bottom:4px;">Telefono *</label>
                    <input type="text" wire:model="telefono" placeholder="Ej: 987654321"
                           class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white">
                    @error('telefono') <span class="text-xs text-danger-600 dark:text-danger-400">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300" style="display:block; margin-bottom:4px;">Puesto / Cargo *</label>
                    <input type="text" wire:model="puesto" placeholder="Ej: Asistente Legal"
                           class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white">
                    @error('puesto') <span class="text-xs text-danger-600 dark:text-danger-400">{{ $message }}</span> @enderror
                </div>
            </div>

            {{-- Footer --}}
            <div style="padding:16px 24px; border-top:1px solid rgba(128,128,128,0.15);">
                <x-filament::button wire:click="guardar" wire:loading.attr="disabled" icon="heroicon-m-check" class="w-full">
                    Guardar y continuar
                </x-filament::button>
            </div>
        </div>

        <div style="text-align:center; margin-top:12px;">
            <p class="text-xs text-gray-400 dark:text-gray-500">Estos datos se usaran para completar tu acuerdo de confidencialidad.</p>
        </div>
    </div>
</x-filament-panels::page>
