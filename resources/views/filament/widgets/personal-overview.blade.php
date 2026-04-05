<x-filament-widgets::widget>
    <x-filament::section heading="Personal" icon="heroicon-o-user-group">
        @php $personal = $this->getPersonal(); @endphp

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="pb-2 text-xs font-medium text-gray-500">Nombre</th>
                        <th class="pb-2 text-xs font-medium text-gray-500">Rol</th>
                        <th class="pb-2 text-xs font-medium text-gray-500">Equipo</th>
                        <th class="pb-2 text-xs font-medium text-gray-500">NDA</th>
                        <th class="pb-2 text-xs font-medium text-gray-500 text-right">Tickets</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach ($personal as $p)
                        <tr>
                            <td class="py-1.5 text-sm text-gray-900 dark:text-white">{{ $p['nombre'] }}</td>
                            <td class="py-1.5"><span class="text-[10px] font-medium text-gray-500 dark:text-gray-400">{{ $p['rol'] }}</span></td>
                            <td class="py-1.5 text-xs {{ $p['equipo'] ? 'text-gray-600 dark:text-gray-300' : 'text-gray-400' }}">{{ $p['equipo'] ?? '—' }}</td>
                            <td class="py-1.5">
                                @if ($p['nda'])
                                    <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-success-600 dark:text-success-400">
                                        <x-heroicon-s-check-circle class="h-3 w-3" /> OK
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-danger-600 dark:text-danger-400">
                                        <x-heroicon-s-x-circle class="h-3 w-3" /> {{ $p['nda_label'] }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-1.5 text-right">
                                @if ($p['tickets'] > 0)
                                    <span class="inline-flex items-center justify-center rounded-full bg-warning-100 px-1.5 py-0.5 text-[10px] font-bold text-warning-700 dark:bg-warning-500/20 dark:text-warning-400">{{ $p['tickets'] }}</span>
                                @else
                                    <span class="text-xs text-gray-400">0</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
