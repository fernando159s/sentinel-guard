<x-filament-panels::page>
    <div class="mx-auto max-w-4xl space-y-4">

        {{-- Header: equipo + plantilla selectors --}}
        <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Equipo</label>
                    <x-filament::input.wrapper>
                        <x-filament::input.select wire:model.live="equipo_id">
                            <option value="">Seleccionar equipo...</option>
                            @foreach ($this->equipos as $id => $nombre)
                                <option value="{{ $id }}">{{ $nombre }}</option>
                            @endforeach
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Checklist</label>
                    <x-filament::input.wrapper>
                        <x-filament::input.select wire:model.live="plantilla_id">
                            <option value="">Seleccionar checklist...</option>
                            @foreach ($this->plantillas as $id => $nombre)
                                <option value="{{ $id }}">{{ $nombre }}</option>
                            @endforeach
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>
            </div>
            @if ($equipo)
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                    <strong>{{ $equipo->codigo_interno }}</strong> — {{ $equipo->marca }} {{ $equipo->modelo }}
                    @if ($equipo->numero_serie) (S/N: {{ $equipo->numero_serie }}) @endif
                    | {{ ucfirst($equipo->tipo) }} | {{ $equipo->ubicacion ?? '—' }}
                </p>
            @endif
        </div>

        {{-- Checklist items --}}
        @if (!empty($items))
            <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="border-b border-gray-200 px-4 py-3 dark:border-white/10">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ $plantilla?->nombre }}</h3>
                    @if ($plantilla?->descripcion)
                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ $plantilla->descripcion }}</p>
                    @endif
                </div>

                <div class="divide-y divide-gray-100 dark:divide-white/5">
                    @foreach ($items as $index => $item)
                        <div class="flex items-center gap-3 px-4 py-2.5 {{ $item['cumple'] ? 'bg-success-50/50 dark:bg-success-950/20' : '' }}">
                            {{-- Checkbox --}}
                            <input type="checkbox" wire:model.live="items.{{ $index }}.cumple"
                                   class="h-5 w-5 shrink-0 rounded border-gray-300 text-success-600 focus:ring-success-500 dark:border-white/10 dark:bg-white/5">

                            {{-- Item info --}}
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-sm {{ $item['cumple'] ? 'text-success-700 dark:text-success-400 line-through' : 'text-gray-900 dark:text-white' }}">{{ $item['nombre'] }}</span>
                                    @if ($item['obligatorio'])
                                        <span class="text-[9px] font-bold uppercase text-danger-500">*</span>
                                    @endif
                                </div>
                                @if ($item['descripcion'] && !$item['cumple'])
                                    <p class="text-[11px] text-gray-400">{{ $item['descripcion'] }}</p>
                                @endif
                            </div>

                            {{-- Observation (inline, only when not compliant) --}}
                            @if (!$item['cumple'])
                                <input type="text" wire:model.blur="items.{{ $index }}.observacion"
                                       placeholder="Observacion..."
                                       class="w-48 shrink-0 rounded border border-gray-300 bg-white px-2 py-1 text-xs text-gray-700 dark:border-white/10 dark:bg-white/5 dark:text-gray-300">
                            @else
                                <span class="w-48 shrink-0"></span>
                            @endif
                        </div>
                    @endforeach
                </div>

                {{-- Footer: observaciones + summary + save --}}
                <div class="border-t border-gray-200 px-4 py-3 dark:border-white/10">
                    <div class="flex items-center gap-4">
                        <input type="text" wire:model="observaciones_generales"
                               placeholder="Observaciones generales..."
                               class="flex-1 rounded border border-gray-300 bg-white px-3 py-1.5 text-xs text-gray-700 dark:border-white/10 dark:bg-white/5 dark:text-gray-300">

                        @php
                            $cumple = collect($items)->where('cumple', true)->count();
                            $total = count($items);
                            $obligatoriosFallidos = collect($items)->where('obligatorio', true)->where('cumple', false)->count();
                        @endphp

                        <span class="shrink-0 text-xs {{ $cumple === $total ? 'text-success-600' : 'text-gray-500' }}">
                            <strong>{{ $cumple }}/{{ $total }}</strong>
                            @if ($obligatoriosFallidos > 0)
                                <span class="text-danger-500">({{ $obligatoriosFallidos }} oblig.)</span>
                            @endif
                        </span>

                        <x-filament::button wire:click="guardar" wire:loading.attr="disabled" size="sm"
                                            icon="heroicon-m-check"
                                            color="{{ $obligatoriosFallidos > 0 ? 'warning' : 'success' }}">
                            Guardar
                        </x-filament::button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
