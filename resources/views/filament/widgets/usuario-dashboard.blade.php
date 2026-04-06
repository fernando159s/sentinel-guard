@php $d = $this->getData(); @endphp

<x-filament-widgets::widget>
    <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px;">

        {{-- COL 1: Mi ficha + accesos directos --}}
        <div style="display:flex; flex-direction:column; gap:12px;">

            {{-- Greeting --}}
            <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <p class="text-lg font-semibold text-gray-900 dark:text-white">Hola, {{ explode(' ', $d['user']->name)[0] }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ now()->translatedFormat('l, d \d\e F Y') }}</p>
            </div>

            {{-- Quick actions --}}
            <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-3">Accesos rapidos</p>
                <div style="display:flex; flex-direction:column; gap:6px;">
                    <a href="/admin/{{ $d['tenant']?->ruc }}/tickets/create" class="flex items-center gap-3 rounded-lg border border-gray-200 px-3 py-2.5 text-sm font-medium text-gray-900 transition hover:bg-gray-50 dark:border-gray-700 dark:text-white dark:hover:bg-white/5">
                        <x-heroicon-o-ticket class="h-4 w-4 text-primary-500" /> Crear ticket de soporte
                    </a>
                    <a href="/admin/{{ $d['tenant']?->ruc }}/registros/create" class="flex items-center gap-3 rounded-lg border border-gray-200 px-3 py-2.5 text-sm font-medium text-gray-900 transition hover:bg-gray-50 dark:border-gray-700 dark:text-white dark:hover:bg-white/5">
                        <x-heroicon-o-document-plus class="h-4 w-4 text-success-500" /> Nuevo registro de seguridad
                    </a>
                    <a href="/admin/{{ $d['tenant']?->ruc }}/tickets" class="flex items-center gap-3 rounded-lg border border-gray-200 px-3 py-2.5 text-sm font-medium text-gray-900 transition hover:bg-gray-50 dark:border-gray-700 dark:text-white dark:hover:bg-white/5">
                        <x-heroicon-o-queue-list class="h-4 w-4 text-info-500" /> Ver mis tickets
                    </a>
                    <a href="/admin/profile" class="flex items-center gap-3 rounded-lg border border-gray-200 px-3 py-2.5 text-sm font-medium text-gray-900 transition hover:bg-gray-50 dark:border-gray-700 dark:text-white dark:hover:bg-white/5">
                        <x-heroicon-o-user-circle class="h-4 w-4 text-gray-400" /> Mi perfil
                    </a>
                </div>
            </div>

            {{-- Mi estado --}}
            <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-3">Mi estado</p>
                <div style="display:flex; flex-direction:column; gap:4px;">
                    <div class="flex items-center justify-between rounded-lg px-3 py-2 {{ $d['equipo'] ? 'bg-success-50/30 dark:bg-success-950/20' : 'bg-danger-50/30 dark:bg-danger-950/20' }}">
                        <div class="flex items-center gap-2">
                            @if($d['equipo']) <x-heroicon-s-check-circle class="h-3.5 w-3.5 text-success-500" /> @else <x-heroicon-s-x-circle class="h-3.5 w-3.5 text-danger-500" /> @endif
                            <span class="text-xs text-gray-700 dark:text-gray-300">Equipo</span>
                        </div>
                        <span class="text-[10px] font-medium {{ $d['equipo'] ? 'text-success-600 dark:text-success-400' : 'text-danger-600 dark:text-danger-400' }}">
                            {{ $d['equipo'] ? $d['equipo']->codigo_interno : 'Sin equipo' }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between rounded-lg px-3 py-2 {{ $d['politicasOk'] ? 'bg-success-50/30 dark:bg-success-950/20' : 'bg-danger-50/30 dark:bg-danger-950/20' }}">
                        <div class="flex items-center gap-2">
                            @if($d['politicasOk']) <x-heroicon-s-check-circle class="h-3.5 w-3.5 text-success-500" /> @else <x-heroicon-s-x-circle class="h-3.5 w-3.5 text-danger-500" /> @endif
                            <span class="text-xs text-gray-700 dark:text-gray-300">Politicas</span>
                        </div>
                        <span class="text-[10px] font-medium {{ $d['politicasOk'] ? 'text-success-600 dark:text-success-400' : 'text-danger-600 dark:text-danger-400' }}">
                            {{ $d['politicasOk'] ? 'Al dia' : $d['politicasPendientes'] . ' pendiente(s)' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- COL 2: Mis tickets --}}
        <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10" style="display:flex; flex-direction:column;">
            <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-white/10">
                <div>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">Mis tickets</p>
                    <p class="text-[10px] text-gray-500">{{ $d['ticketsAbiertos'] }} abierto(s)</p>
                </div>
                <a href="/admin/{{ $d['tenant']?->ruc }}/tickets/create" class="text-xs font-medium text-primary-600 hover:underline dark:text-primary-400">+ Nuevo</a>
            </div>
            <div style="flex:1; overflow-y:auto;">
                @forelse ($d['misTickets'] as $ticket)
                    <a href="/admin/{{ $d['tenant']?->ruc }}/tickets/{{ $ticket->id }}" class="flex items-center justify-between px-4 py-2.5 border-b border-gray-100 transition hover:bg-gray-50 dark:border-white/5 dark:hover:bg-white/5">
                        <div class="min-w-0 flex-1">
                            <p class="text-sm text-gray-900 dark:text-white truncate">{{ $ticket->asunto }}</p>
                            <p class="text-[10px] text-gray-400">{{ $ticket->numero_ticket }} · {{ $ticket->created_at->diffForHumans() }}</p>
                        </div>
                        @php $color = match($ticket->estado) { 'nuevo' => 'info', 'en_revision' => 'warning', 'esperando_usuario' => 'gray', 'resuelto' => 'success', default => 'gray' }; @endphp
                        <x-filament::badge :color="$color" size="sm">{{ $ticket->estado }}</x-filament::badge>
                    </a>
                @empty
                    <div class="flex flex-col items-center justify-center py-8 text-gray-400">
                        <x-heroicon-o-ticket class="h-8 w-8 mb-2" />
                        <p class="text-xs">Sin tickets recientes</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- COL 3: Mis registros --}}
        <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10" style="display:flex; flex-direction:column;">
            <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-white/10">
                <div>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">Mis registros</p>
                    <p class="text-[10px] text-gray-500">{{ $d['registrosMes'] }} este mes</p>
                </div>
                <a href="/admin/{{ $d['tenant']?->ruc }}/registros/create" class="text-xs font-medium text-primary-600 hover:underline dark:text-primary-400">+ Nuevo</a>
            </div>
            <div style="flex:1; overflow-y:auto;">
                @forelse ($d['misRegistros'] as $registro)
                    <a href="/admin/{{ $d['tenant']?->ruc }}/registros/{{ $registro->id }}/edit" class="flex items-center justify-between px-4 py-2.5 border-b border-gray-100 transition hover:bg-gray-50 dark:border-white/5 dark:hover:bg-white/5">
                        <div class="min-w-0 flex-1">
                            <p class="text-sm text-gray-900 dark:text-white">{{ $registro->numero_registro }}</p>
                            <p class="text-[10px] text-gray-400">{{ $registro->created_at->diffForHumans() }}</p>
                        </div>
                        <x-filament::badge color="primary" size="sm">{{ $registro->tipo_formato }}</x-filament::badge>
                    </a>
                @empty
                    <div class="flex flex-col items-center justify-center py-8 text-gray-400">
                        <x-heroicon-o-document-text class="h-8 w-8 mb-2" />
                        <p class="text-xs">Sin registros recientes</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
