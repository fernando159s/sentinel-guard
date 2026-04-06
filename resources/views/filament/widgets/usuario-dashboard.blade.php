@php $d = $this->getData(); @endphp

<x-filament-widgets::widget>
    <div style="display:flex; flex-direction:column; gap:16px;">

        {{-- ROW 1: Greeting + Quick actions + Status --}}
        <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px;">

            {{-- Greeting + Actions --}}
            <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <p class="text-base font-semibold text-gray-900 dark:text-white">Hola, {{ explode(' ', $d['user']->name)[0] }} 👋</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">{{ now()->translatedFormat('l, d \d\e F Y') }}</p>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px;">
                    <a href="/admin/{{ $d['tenant']?->ruc }}/tickets/create" class="flex items-center gap-2 rounded-lg bg-primary-50 px-3 py-2.5 text-xs font-semibold text-primary-700 transition hover:bg-primary-100 dark:bg-primary-500/10 dark:text-primary-400 dark:hover:bg-primary-500/20">
                        <x-heroicon-s-plus-circle class="h-4 w-4" /> Nuevo ticket
                    </a>
                    <a href="/admin/{{ $d['tenant']?->ruc }}/registros/create" class="flex items-center gap-2 rounded-lg bg-success-50 px-3 py-2.5 text-xs font-semibold text-success-700 transition hover:bg-success-100 dark:bg-success-500/10 dark:text-success-400 dark:hover:bg-success-500/20">
                        <x-heroicon-s-plus-circle class="h-4 w-4" /> Nuevo registro
                    </a>
                </div>
            </div>

            {{-- Stats cards --}}
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 flex flex-col justify-center">
                    <p class="text-[10px] font-medium text-gray-500 dark:text-gray-400 uppercase">Tickets abiertos</p>
                    <p class="text-2xl font-bold {{ $d['ticketsAbiertos'] > 0 ? 'text-warning-600' : 'text-success-600' }}">{{ $d['ticketsAbiertos'] }}</p>
                </div>
                <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 flex flex-col justify-center">
                    <p class="text-[10px] font-medium text-gray-500 dark:text-gray-400 uppercase">Registros este mes</p>
                    <p class="text-2xl font-bold text-primary-600 dark:text-primary-400">{{ $d['registrosMes'] }}</p>
                </div>
                <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 flex flex-col justify-center">
                    <p class="text-[10px] font-medium text-gray-500 dark:text-gray-400 uppercase">Equipo</p>
                    <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $d['equipo']?->codigo_interno ?? '—' }}</p>
                </div>
                <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 flex flex-col justify-center">
                    <p class="text-[10px] font-medium text-gray-500 dark:text-gray-400 uppercase">Politicas</p>
                    <p class="text-sm font-bold {{ $d['politicasOk'] ? 'text-success-600' : 'text-danger-600' }}">
                        {{ $d['politicasOk'] ? 'Al dia' : $d['politicasPendientes'] . ' pend.' }}
                    </p>
                </div>
            </div>

            {{-- Mi equipo card --}}
            <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-3">Mi equipo</p>
                @if ($d['equipo'])
                    <div class="mb-3">
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $d['equipo']->marca }} {{ $d['equipo']->modelo }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $d['equipo']->codigo_interno }} · {{ $d['equipo']->tipo }} · {{ $d['equipo']->ubicacion ?? '—' }}</p>
                        @if ($d['equipo']->numero_serie)
                            <p class="text-[10px] text-gray-400 mt-0.5">S/N: {{ $d['equipo']->numero_serie }}</p>
                        @endif
                    </div>
                    <div style="display:flex; gap:6px; flex-wrap:wrap;">
                        <span class="inline-flex items-center rounded-full bg-success-50 px-2 py-0.5 text-[10px] font-semibold text-success-700 dark:bg-success-500/10 dark:text-success-400">{{ $d['equipo']->sistema_operativo ?? 'N/A' }}</span>
                        @if ($d['equipo']->ram_gb)<span class="inline-flex items-center rounded-full bg-info-50 px-2 py-0.5 text-[10px] font-semibold text-info-700 dark:bg-info-500/10 dark:text-info-400">{{ $d['equipo']->ram_gb }}GB RAM</span>@endif
                        @if ($d['equipo']->disco_gb)<span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-semibold text-gray-600 dark:bg-gray-700 dark:text-gray-300">{{ $d['equipo']->disco_gb }}GB</span>@endif
                    </div>
                @else
                    <p class="text-sm text-gray-400">Sin equipo asignado</p>
                @endif
            </div>
        </div>

        {{-- ROW 2: Tickets + Registros --}}
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">

            {{-- Mis tickets --}}
            <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="flex items-center justify-between border-b border-gray-200 px-5 py-3 dark:border-white/10">
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">Mis tickets</p>
                    <a href="/admin/{{ $d['tenant']?->ruc }}/tickets/create" class="text-xs font-medium text-primary-600 hover:underline dark:text-primary-400">+ Nuevo</a>
                </div>
                @forelse ($d['misTickets'] as $ticket)
                    <a href="/admin/{{ $d['tenant']?->ruc }}/tickets/{{ $ticket->id }}" class="flex items-center justify-between px-5 py-3 border-b border-gray-100 transition hover:bg-gray-50 dark:border-white/5 dark:hover:bg-white/5">
                        <div class="min-w-0 flex-1 mr-3">
                            <p class="text-sm text-gray-900 dark:text-white truncate">{{ $ticket->asunto }}</p>
                            <p class="text-[10px] text-gray-400">{{ $ticket->numero_ticket }} · {{ $ticket->created_at->diffForHumans() }}</p>
                        </div>
                        @php $color = match($ticket->estado) { 'nuevo' => 'info', 'en_revision' => 'warning', 'esperando_usuario' => 'gray', 'resuelto' => 'success', default => 'gray' }; @endphp
                        <x-filament::badge :color="$color" size="sm">{{ $ticket->estado }}</x-filament::badge>
                    </a>
                @empty
                    <div class="py-10 text-center text-gray-400">
                        <x-heroicon-o-ticket class="mx-auto h-8 w-8 mb-2" />
                        <p class="text-xs">Sin tickets recientes</p>
                        <a href="/admin/{{ $d['tenant']?->ruc }}/tickets/create" class="mt-2 inline-block text-xs font-medium text-primary-600 hover:underline dark:text-primary-400">Crear primer ticket</a>
                    </div>
                @endforelse
            </div>

            {{-- Mis registros --}}
            <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="flex items-center justify-between border-b border-gray-200 px-5 py-3 dark:border-white/10">
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">Mis registros</p>
                    <a href="/admin/{{ $d['tenant']?->ruc }}/registros/create" class="text-xs font-medium text-primary-600 hover:underline dark:text-primary-400">+ Nuevo</a>
                </div>
                @forelse ($d['misRegistros'] as $registro)
                    <a href="/admin/{{ $d['tenant']?->ruc }}/registros/{{ $registro->id }}/edit" class="flex items-center justify-between px-5 py-3 border-b border-gray-100 transition hover:bg-gray-50 dark:border-white/5 dark:hover:bg-white/5">
                        <div class="min-w-0 flex-1 mr-3">
                            <p class="text-sm text-gray-900 dark:text-white">{{ $registro->numero_registro }}</p>
                            <p class="text-[10px] text-gray-400">{{ $registro->created_at->diffForHumans() }}</p>
                        </div>
                        <x-filament::badge color="primary" size="sm">{{ $registro->tipo_formato }}</x-filament::badge>
                    </a>
                @empty
                    <div class="py-10 text-center text-gray-400">
                        <x-heroicon-o-document-text class="mx-auto h-8 w-8 mb-2" />
                        <p class="text-xs">Sin registros recientes</p>
                        <a href="/admin/{{ $d['tenant']?->ruc }}/registros/create" class="mt-2 inline-block text-xs font-medium text-primary-600 hover:underline dark:text-primary-400">Crear primer registro</a>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
