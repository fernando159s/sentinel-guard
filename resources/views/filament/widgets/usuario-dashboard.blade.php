@php $d = $this->getData(); @endphp

<x-filament-widgets::widget>
    <div style="column-span:all; display:grid; grid-template-columns:1fr 1fr; gap:16px; width:100%;">

        {{-- LEFT COL --}}
        <div style="display:flex; flex-direction:column; gap:16px;">

            {{-- Welcome + Actions --}}
            <x-filament::section>
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Hola, {{ explode(' ', $d['user']->name)[0] }}</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            {{ $d['ticketsAbiertos'] }} ticket(s) abierto(s) · {{ $d['registrosMes'] }} registro(s) este mes
                        </p>
                    </div>
                    <div class="flex gap-2">
                        <x-filament::button tag="a" href="/admin/{{ $d['tenant']?->ruc }}/tickets/create" icon="heroicon-m-plus" size="sm">Ticket</x-filament::button>
                        <x-filament::button tag="a" href="/admin/{{ $d['tenant']?->ruc }}/registros/create" icon="heroicon-m-plus" size="sm" color="success">Registro</x-filament::button>
                    </div>
                </div>
            </x-filament::section>

            {{-- Mis tickets --}}
            <x-filament::section heading="Mis tickets" icon="heroicon-o-ticket">
                <x-slot name="headerEnd">
                    <a href="/admin/{{ $d['tenant']?->ruc }}/tickets" class="text-xs font-medium text-primary-600 hover:underline dark:text-primary-400">Ver todos</a>
                </x-slot>
                <div class="divide-y divide-gray-100 dark:divide-white/5">
                    @forelse ($d['misTickets'] as $ticket)
                        <a href="/admin/{{ $d['tenant']?->ruc }}/tickets/{{ $ticket->id }}" class="flex items-center justify-between py-3 transition hover:opacity-80">
                            <div class="min-w-0 flex-1 pr-3">
                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $ticket->asunto }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $ticket->numero_ticket }} · {{ $ticket->created_at->diffForHumans() }}</p>
                            </div>
                            @php $color = match($ticket->estado) { 'nuevo' => 'info', 'en_revision' => 'warning', 'esperando_usuario' => 'gray', 'resuelto' => 'success', default => 'gray' }; @endphp
                            <x-filament::badge :color="$color">{{ match($ticket->estado) { 'nuevo' => 'Nuevo', 'en_revision' => 'En revision', 'esperando_usuario' => 'Esperando', 'resuelto' => 'Resuelto', 'cerrado' => 'Cerrado', default => $ticket->estado } }}</x-filament::badge>
                        </a>
                    @empty
                        <div class="py-6 text-center">
                            <p class="text-sm text-gray-400">Sin tickets recientes</p>
                            <x-filament::button tag="a" href="/admin/{{ $d['tenant']?->ruc }}/tickets/create" size="sm" class="mt-2">Crear ticket</x-filament::button>
                        </div>
                    @endforelse
                </div>
            </x-filament::section>

            {{-- Mis registros --}}
            <x-filament::section heading="Mis registros" icon="heroicon-o-document-text">
                <x-slot name="headerEnd">
                    <a href="/admin/{{ $d['tenant']?->ruc }}/registros" class="text-xs font-medium text-primary-600 hover:underline dark:text-primary-400">Ver todos</a>
                </x-slot>
                <div class="divide-y divide-gray-100 dark:divide-white/5">
                    @forelse ($d['misRegistros'] as $registro)
                        <a href="/admin/{{ $d['tenant']?->ruc }}/registros/{{ $registro->id }}/edit" class="flex items-center justify-between py-2.5 transition hover:opacity-80">
                            <div class="min-w-0 flex-1 pr-3">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $registro->numero_registro }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $registro->created_at->diffForHumans() }}</p>
                            </div>
                            <x-filament::badge color="primary">{{ $registro->tipo_formato }}</x-filament::badge>
                        </a>
                    @empty
                        <div class="py-6 text-center">
                            <p class="text-sm text-gray-400">Sin registros recientes</p>
                            <x-filament::button tag="a" href="/admin/{{ $d['tenant']?->ruc }}/registros/create" size="sm" color="success" class="mt-2">Crear registro</x-filament::button>
                        </div>
                    @endforelse
                </div>
            </x-filament::section>
        </div>

        {{-- RIGHT COL --}}
        <div style="display:flex; flex-direction:column; gap:20px;">

            {{-- Equipos asignados --}}
            @if ($d['equipos']->isNotEmpty())
                @foreach ($d['equipos'] as $equipo)
                    @php $cl = $d['checklists'][$equipo->id] ?? null; @endphp
                    <x-filament::section>
                        {{-- Equipo --}}
                        <div class="flex items-center gap-3">
                            <div class="shrink-0 relative">
                                <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-primary-50 dark:bg-primary-500/10">
                                    @if (in_array($equipo->tipo, ['laptop', 'pc_escritorio', 'servidor']))
                                        <x-heroicon-o-computer-desktop class="w-5 h-5 text-primary-500" />
                                    @elseif ($equipo->tipo === 'impresora')
                                        <x-heroicon-o-printer class="w-5 h-5 text-primary-500" />
                                    @else
                                        <x-heroicon-o-cube class="w-5 h-5 text-primary-500" />
                                    @endif
                                </div>
                                @if ($equipo->estado === 'activo')
                                    <span style="position:absolute;top:-3px;right:-3px;width:12px;height:12px;border-radius:50%;background:#22c55e;border:2px solid #09090b;box-shadow:0 0 0 1px rgba(34,197,94,.3);"></span>
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $equipo->marca }} {{ $equipo->modelo }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                    {{ $equipo->codigo_interno }}
                                    · {{ ucfirst(str_replace('_', ' ', $equipo->tipo)) }}
                                    @if ($equipo->sistema_operativo) · {{ $equipo->sistema_operativo }} @endif
                                    @if ($equipo->ram_gb) · {{ $equipo->ram_gb }}GB RAM @endif
                                    @if ($equipo->disco_gb) · {{ $equipo->disco_gb }}GB @endif
                                </p>
                                @if ($equipo->ubicacion)
                                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{ $equipo->ubicacion }}</p>
                                @endif
                            </div>
                        </div>

                        {{-- Checklist --}}
                        @if ($cl)
                            @php
                                $cumple = $cl->itemsCumplen();
                                $total = $cl->totalItems();
                                $pct = $total > 0 ? round(($cumple / $total) * 100) : 0;
                                $clColor = $pct === 100 ? 'success' : ($pct >= 50 ? 'warning' : 'danger');
                            @endphp
                            {{-- Checklist items --}}
                            <div style="padding: 27px 0;">
                                @if ($cl->resultados)
                                    <div class="grid grid-cols-2 gap-x-4 gap-y-1">
                                        @foreach (collect($cl->resultados) as $item)
                                            <div class="flex items-center gap-2 text-xs py-1">
                                                @if ($item['cumple'] ?? false)
                                                    <span class="text-success-500 shrink-0">&#x2713;</span>
                                                @else
                                                    <span class="text-danger-500 shrink-0">&#x2717;</span>
                                                @endif
                                                <span class="text-gray-600 dark:text-gray-400 truncate">{{ $item['nombre'] ?? $item['item'] ?? '—' }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            {{-- Footer --}}
                            <div class="flex items-center justify-between rounded-lg bg-gray-50 dark:bg-white/5 px-3 py-2">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-medium text-gray-600 dark:text-gray-300">{{ $cl->plantilla?->nombre ?? 'Checklist' }}</span>
                                    <span class="text-[11px] text-gray-400">· {{ $cl->fecha_ejecucion->diffForHumans() }}</span>
                                </div>
                                <x-filament::badge :color="$clColor">{{ $cumple }}/{{ $total }}</x-filament::badge>
                            </div>
                        @endif
                    </x-filament::section>
                @endforeach
            @endif

            {{-- Documentos firmados --}}
            <x-filament::section heading="Documentos firmados" icon="heroicon-o-document-check">
                <div class="divide-y divide-gray-100 dark:divide-white/5">
                    @forelse ($d['documentosFirmados'] as $aceptacion)
                        <div class="flex items-center justify-between py-2.5">
                            <div class="min-w-0 flex-1 pr-3">
                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $aceptacion->politica?->titulo }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    v{{ $aceptacion->version_aceptada }} · Firmado {{ $aceptacion->fecha_aceptacion->diffForHumans() }}
                                </p>
                            </div>
                            <a href="{{ route('politicas.pdf', $aceptacion->politica_id) }}" target="_blank" class="shrink-0 inline-flex items-center gap-1 text-xs font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300 transition">
                                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                                PDF
                            </a>
                        </div>
                    @empty
                        <div class="py-6 text-center">
                            <p class="text-sm text-gray-400">Sin documentos firmados</p>
                        </div>
                    @endforelse
                </div>
            </x-filament::section>
        </div>
    </div>
</x-filament-widgets::widget>
