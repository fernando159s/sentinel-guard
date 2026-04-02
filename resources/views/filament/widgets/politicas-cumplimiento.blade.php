<x-filament-widgets::widget>
    <x-filament::section heading="Cumplimiento de politicas" icon="heroicon-o-shield-check" collapsible>
        @php $data = $this->getData(); @endphp

        @if ($data['politicas']->isEmpty())
            <p class="text-sm text-gray-500">No hay politicas obligatorias activas.</p>
        @else
            <div class="space-y-3">
                @foreach ($data['politicas'] as $p)
                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center justify-between mb-2">
                            <div>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $p['titulo'] }}</span>
                                <span class="ml-2 text-xs text-gray-500">v{{ $p['version'] }}</span>
                            </div>
                            <span class="text-sm font-bold {{ $p['porcentaje'] === 100 ? 'text-success-600' : ($p['porcentaje'] >= 50 ? 'text-warning-600' : 'text-danger-600') }}">
                                {{ $p['porcentaje'] }}%
                            </span>
                        </div>
                        <div class="h-2 w-full rounded-full bg-gray-200 dark:bg-gray-700">
                            <div class="h-2 rounded-full {{ $p['porcentaje'] === 100 ? 'bg-success-500' : ($p['porcentaje'] >= 50 ? 'bg-warning-500' : 'bg-danger-500') }}"
                                 style="width: {{ $p['porcentaje'] }}%"></div>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">{{ $p['aceptadas'] }} de {{ $p['total'] }} usuarios</p>
                    </div>
                @endforeach
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
