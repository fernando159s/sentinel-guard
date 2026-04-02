<x-filament-panels::page>
    <div class="mx-auto max-w-3xl space-y-6">

        {{-- Selectors --}}
        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Seleccionar equipo y checklist</h3>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Equipo</label>
                    <select wire:model.live="equipo_id"
                            class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm dark:border-white/10 dark:bg-white/5 dark:text-white">
                        <option value="">Seleccionar equipo...</option>
                        @foreach ($this->equipos as $id => $nombre)
                            <option value="{{ $id }}">{{ $nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Plantilla de checklist</label>
                    <select wire:model.live="plantilla_id"
                            class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm dark:border-white/10 dark:bg-white/5 dark:text-white">
                        <option value="">Seleccionar checklist...</option>
                        @foreach ($this->plantillas as $id => $nombre)
                            <option value="{{ $id }}">{{ $nombre }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            @if ($equipo)
                <div class="mt-3 rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-800">
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        <strong>{{ $equipo->codigo_interno }}</strong> — {{ $equipo->marca }} {{ $equipo->modelo }}
                        @if ($equipo->numero_serie) (S/N: {{ $equipo->numero_serie }}) @endif
                        | {{ ucfirst($equipo->tipo) }} | {{ $equipo->ubicacion ?? 'Sin ubicacion' }}
                    </p>
                </div>
            @endif
        </div>

        {{-- Checklist items --}}
        @if (!empty($items))
            <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="mb-4">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ $plantilla?->nombre }}</h3>
                    @if ($plantilla?->descripcion)
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $plantilla->descripcion }}</p>
                    @endif
                </div>

                <div class="space-y-3">
                    @foreach ($items as $index => $item)
                        <div class="rounded-lg border p-4 transition {{ $item['cumple'] ? 'border-success-200 bg-success-50/50 dark:border-success-800 dark:bg-success-950/30' : 'border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800' }}">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $item['nombre'] }}</span>
                                        @if ($item['obligatorio'])
                                            <span class="inline-flex items-center rounded-full bg-danger-100 px-1.5 py-0.5 text-[10px] font-semibold text-danger-700 dark:bg-danger-500/20 dark:text-danger-400">Obligatorio</span>
                                        @endif
                                    </div>
                                    @if ($item['descripcion'])
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $item['descripcion'] }}</p>
                                    @endif
                                </div>
                                <label class="relative inline-flex cursor-pointer items-center">
                                    <input type="checkbox" wire:model.live="items.{{ $index }}.cumple" class="peer sr-only">
                                    <div class="h-6 w-11 rounded-full bg-gray-300 transition after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:bg-white after:transition-all peer-checked:bg-success-500 peer-checked:after:translate-x-full dark:bg-gray-600 dark:peer-checked:bg-success-500"></div>
                                </label>
                            </div>
                            @if (!$item['cumple'])
                                <div class="mt-3">
                                    <textarea wire:model.blur="items.{{ $index }}.observacion"
                                              rows="2"
                                              placeholder="Observacion (por que no cumple)..."
                                              class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs text-gray-900 shadow-sm placeholder:text-gray-400 dark:border-white/10 dark:bg-white/5 dark:text-white"></textarea>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                {{-- Observaciones generales --}}
                <div class="mt-4">
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Observaciones generales</label>
                    <textarea wire:model="observaciones_generales"
                              rows="2"
                              placeholder="Notas adicionales sobre esta verificacion..."
                              class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm dark:border-white/10 dark:bg-white/5 dark:text-white"></textarea>
                </div>

                {{-- Summary + save --}}
                <div class="mt-4 flex items-center justify-between border-t border-gray-200 pt-4 dark:border-gray-700">
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        @php
                            $cumple = collect($items)->where('cumple', true)->count();
                            $total = count($items);
                            $obligatoriosFallidos = collect($items)->where('obligatorio', true)->where('cumple', false)->count();
                        @endphp
                        <span class="font-semibold {{ $cumple === $total ? 'text-success-600' : 'text-warning-600' }}">{{ $cumple }}/{{ $total }}</span> items cumplen
                        @if ($obligatoriosFallidos > 0)
                            <span class="text-danger-500 font-medium">— {{ $obligatoriosFallidos }} obligatorio(s) sin cumplir</span>
                        @endif
                    </div>
                    <x-filament::button wire:click="guardar" wire:loading.attr="disabled" icon="heroicon-m-check" color="{{ $obligatoriosFallidos > 0 ? 'warning' : 'success' }}">
                        Guardar checklist
                    </x-filament::button>
                </div>
            </div>
        @elseif ($plantilla_id)
            <div class="text-center py-8 text-gray-500">
                <p>Cargando items...</p>
            </div>
        @endif
    </div>
</x-filament-panels::page>
