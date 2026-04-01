<x-filament-panels::page>
    <div>@php
        $trends = $this->counterTrends;
        $cards = [
            ['key' => 'total', 'label' => 'Total tickets', 'value' => $this->counters['total'], 'color' => '#22d3ee', 'wire' => "limpiarFiltros", 'active' => !$filtroEstado && !$filtroAgente && !$filtroEmpresa && !$busqueda],
            ['key' => 'sin_asignar', 'label' => 'Sin asignar', 'value' => $this->counters['sin_asignar'], 'color' => '#ef4444', 'wire' => "\$set('filtroAgente', 'sin_asignar')", 'active' => $filtroAgente === 'sin_asignar'],
            ['key' => 'nuevos_hoy', 'label' => 'Nuevos hoy', 'value' => $this->counters['nuevos_hoy'], 'color' => '#3b82f6', 'wire' => "\$set('filtroEstado', 'nuevo')", 'active' => $filtroEstado === 'nuevo'],
            ['key' => 'mis_tickets', 'label' => 'Mis tickets', 'value' => $this->counters['mis_tickets'], 'color' => '#6366f1', 'wire' => "\$set('filtroAgente', 'mis')", 'active' => $filtroAgente === 'mis'],
            ['key' => 'en_revision', 'label' => 'En revision', 'value' => $this->counters['en_revision'], 'color' => '#f59e0b', 'wire' => "\$set('filtroEstado', 'en_revision')", 'active' => $filtroEstado === 'en_revision'],
            ['key' => 'esperando', 'label' => 'Esperando usuario', 'value' => $this->counters['esperando'], 'color' => '#6b7280', 'wire' => "\$set('filtroEstado', 'esperando_usuario')", 'active' => $filtroEstado === 'esperando_usuario'],
            ['key' => 'total_abiertos', 'label' => 'Abiertos', 'value' => $this->counters['total_abiertos'], 'color' => '#8b5cf6', 'wire' => null, 'active' => false],
        ];
    @endphp

    {{-- Main layout: Panel (left) + Counters (right) --}}
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-12" style="min-height: 80vh;">

    {{-- LEFT: Ticket panel (list + detail) --}}
    <div class="lg:col-span-9 grid grid-cols-1 gap-4 lg:grid-cols-5" style="min-height: 70vh;">

        {{-- LEFT: Ticket list --}}
        <div class="lg:col-span-2 flex flex-col rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">

            {{-- Filters bar --}}
            <div class="border-b border-gray-200 dark:border-white/10 p-3 space-y-2">
                <div class="flex gap-2">
                    <input type="text"
                           wire:model.live.debounce.300ms="busqueda"
                           placeholder="Buscar ticket..."
                           class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition placeholder:text-gray-400 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white dark:placeholder:text-gray-500">
                    @if ($filtroEstado || $filtroPrioridad || $filtroAgente || $filtroEmpresa || $busqueda)
                        <button wire:click="limpiarFiltros"
                                class="shrink-0 rounded-lg bg-gray-100 px-3 py-2 text-xs font-medium text-gray-600 hover:bg-gray-200 dark:bg-white/5 dark:text-gray-400 dark:hover:bg-white/10"
                                title="Limpiar filtros">
                            <x-heroicon-m-x-mark class="h-4 w-4" />
                        </button>
                    @endif
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <select wire:model.live="filtroEstado"
                            class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs text-gray-900 shadow-sm transition focus:border-primary-500 focus:ring-1 focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white">
                        <option value="">Estado: Todos</option>
                        <option value="nuevo">Nuevo</option>
                        <option value="en_revision">En revision</option>
                        <option value="esperando_usuario">Esperando usuario</option>
                        <option value="resuelto">Resuelto</option>
                        <option value="cerrado">Cerrado</option>
                    </select>
                    <select wire:model.live="filtroPrioridad"
                            class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs text-gray-900 shadow-sm transition focus:border-primary-500 focus:ring-1 focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white">
                        <option value="">Prioridad: Todas</option>
                        <option value="urgente">Urgente</option>
                        <option value="alta">Alta</option>
                        <option value="media">Media</option>
                        <option value="baja">Baja</option>
                    </select>
                    <select wire:model.live="filtroEmpresa"
                            class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs text-gray-900 shadow-sm transition focus:border-primary-500 focus:ring-1 focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white">
                        <option value="">Empresa: Todas</option>
                        @foreach ($this->empresas as $id => $nombre)
                            <option value="{{ $id }}">{{ $nombre }}</option>
                        @endforeach
                    </select>
                    <select wire:model.live="filtroAgente"
                            class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs text-gray-900 shadow-sm transition focus:border-primary-500 focus:ring-1 focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white">
                        <option value="">Agente: Todos</option>
                        <option value="sin_asignar">Sin asignar</option>
                        <option value="mis">Mis tickets</option>
                        @foreach ($this->agentes as $id => $nombre)
                            <option value="{{ $id }}">{{ $nombre }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Ticket list --}}
            <div class="flex-1 overflow-y-auto divide-y divide-gray-100 dark:divide-white/5">
                @forelse ($this->tickets as $ticket)
                    <button wire:click="selectTicket({{ $ticket->id }})"
                            class="w-full text-left px-4 py-3 transition hover:bg-gray-50 dark:hover:bg-white/5 {{ $selectedTicketId === $ticket->id ? 'bg-primary-50 dark:bg-primary-500/10 border-l-4 border-primary-500' : '' }}">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-mono text-gray-500 dark:text-gray-400">{{ $ticket->numero_ticket }}</span>
                                    @php
                                        $prioColor = match($ticket->prioridad) {
                                            'urgente' => 'bg-danger-100 text-danger-700 dark:bg-danger-500/20 dark:text-danger-400',
                                            'alta' => 'bg-warning-100 text-warning-700 dark:bg-warning-500/20 dark:text-warning-400',
                                            'media' => 'bg-info-100 text-info-700 dark:bg-info-500/20 dark:text-info-400',
                                            default => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center rounded-full px-1.5 py-0.5 text-[10px] font-semibold {{ $prioColor }}">
                                        {{ ucfirst($ticket->prioridad) }}
                                    </span>
                                </div>
                                <p class="mt-1 text-sm font-medium text-gray-900 dark:text-white truncate">{{ $ticket->asunto }}</p>
                                <div class="mt-1 flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                    <span>{{ $ticket->empresa?->razon_social }}</span>
                                    <span>&middot;</span>
                                    <span>{{ $ticket->creador?->name }}</span>
                                </div>
                            </div>
                            <div class="shrink-0 text-right">
                                @php
                                    $estadoColor = match($ticket->estado) {
                                        'nuevo' => 'bg-info-100 text-info-700 dark:bg-info-500/20 dark:text-info-400',
                                        'en_revision' => 'bg-warning-100 text-warning-700 dark:bg-warning-500/20 dark:text-warning-400',
                                        'esperando_usuario' => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400',
                                        'resuelto' => 'bg-success-100 text-success-700 dark:bg-success-500/20 dark:text-success-400',
                                        'cerrado' => 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-500',
                                        default => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400',
                                    };
                                    $estadoLabel = match($ticket->estado) {
                                        'nuevo' => 'Nuevo',
                                        'en_revision' => 'En revision',
                                        'esperando_usuario' => 'Esperando',
                                        'resuelto' => 'Resuelto',
                                        'cerrado' => 'Cerrado',
                                        default => $ticket->estado,
                                    };
                                @endphp
                                <span class="inline-flex items-center rounded-full px-1.5 py-0.5 text-[10px] font-semibold {{ $estadoColor }}">
                                    {{ $estadoLabel }}
                                </span>
                                <p class="mt-1 text-[10px] text-gray-400">
                                    {{ $ticket->fecha_ultima_actividad?->diffForHumans() ?? $ticket->created_at->diffForHumans() }}
                                </p>
                                @if ($ticket->agente)
                                    <p class="mt-0.5 text-[10px] text-gray-400 truncate max-w-[100px]">{{ $ticket->agente->name }}</p>
                                @else
                                    <p class="mt-0.5 text-[10px] text-danger-500 font-medium">Sin asignar</p>
                                @endif
                            </div>
                        </div>
                    </button>
                @empty
                    <div class="flex flex-col items-center justify-center py-12 text-gray-400">
                        <x-heroicon-o-ticket class="h-12 w-12 mb-2" />
                        <p class="text-sm">No se encontraron tickets</p>
                    </div>
                @endforelse
            </div>

            <div class="border-t border-gray-200 dark:border-white/10 px-4 py-2 text-xs text-gray-500">
                {{ $this->tickets->count() }} tickets
            </div>
        </div>

        {{-- RIGHT: Ticket detail --}}
        <div class="lg:col-span-3 flex flex-col rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            @if ($this->selectedTicket)
                @php $ticket = $this->selectedTicket; @endphp

                {{-- Ticket header --}}
                <div class="border-b border-gray-200 dark:border-white/10 p-4">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-sm font-mono text-gray-500 dark:text-gray-400">{{ $ticket->numero_ticket }}</span>
                                @php
                                    $estadoColor = match($ticket->estado) {
                                        'nuevo' => 'info',
                                        'en_revision' => 'warning',
                                        'esperando_usuario' => 'gray',
                                        'resuelto' => 'success',
                                        'cerrado' => 'gray',
                                        default => 'gray',
                                    };
                                @endphp
                                <x-filament::badge :color="$estadoColor">
                                    {{ match($ticket->estado) {
                                        'nuevo' => 'Nuevo',
                                        'en_revision' => 'En revision',
                                        'esperando_usuario' => 'Esperando usuario',
                                        'resuelto' => 'Resuelto',
                                        'cerrado' => 'Cerrado',
                                        default => $ticket->estado,
                                    } }}
                                </x-filament::badge>
                                @php
                                    $prioColor = match($ticket->prioridad) {
                                        'urgente' => 'danger',
                                        'alta' => 'warning',
                                        'media' => 'info',
                                        default => 'gray',
                                    };
                                @endphp
                                <x-filament::badge :color="$prioColor">
                                    {{ ucfirst($ticket->prioridad) }}
                                </x-filament::badge>
                            </div>
                            <h2 class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">{{ $ticket->asunto }}</h2>
                            <div class="mt-1 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-gray-500 dark:text-gray-400">
                                <span>Empresa: <strong>{{ $ticket->empresa?->razon_social }}</strong></span>
                                <span>Creado por: <strong>{{ $ticket->creador?->name }}</strong></span>
                                <span>{{ $ticket->created_at->format('d/m/Y H:i') }}</span>
                                @if ($ticket->agente)
                                    <span>Agente: <strong>{{ $ticket->agente->name }}</strong></span>
                                @endif
                            </div>
                        </div>

                        {{-- Quick actions --}}
                        <div class="flex items-center gap-2 shrink-0">
                            @if (! $ticket->asignado_a || $ticket->asignado_a !== auth()->id())
                                <x-filament::button size="sm" color="info" wire:click="asignarAMi" wire:loading.attr="disabled" icon="heroicon-m-hand-raised">
                                    Tomar
                                </x-filament::button>
                            @endif

                            @can('editar_tickets')
                                <x-filament::dropdown>
                                    <x-slot name="trigger">
                                        <x-filament::button size="sm" color="gray" icon="heroicon-m-ellipsis-vertical">
                                            Estado
                                        </x-filament::button>
                                    </x-slot>
                                    <x-filament::dropdown.list>
                                        @foreach (['nuevo' => 'Nuevo', 'en_revision' => 'En revision', 'esperando_usuario' => 'Esperando usuario', 'resuelto' => 'Resuelto', 'cerrado' => 'Cerrado'] as $val => $label)
                                            @if ($ticket->estado !== $val)
                                                <x-filament::dropdown.list.item wire:click="cambiarEstado('{{ $val }}')">
                                                    {{ $label }}
                                                </x-filament::dropdown.list.item>
                                            @endif
                                        @endforeach
                                    </x-filament::dropdown.list>
                                </x-filament::dropdown>
                            @endcan

                            {{-- Create registro from ticket --}}
                            @if (in_array($ticket->estado, ['resuelto', 'cerrado', 'en_revision']))
                                <x-filament::dropdown>
                                    <x-slot name="trigger">
                                        <x-filament::button size="sm" color="success" icon="heroicon-m-document-plus">
                                            Crear registro
                                        </x-filament::button>
                                    </x-slot>
                                    <x-filament::dropdown.header>
                                        Formato sugerido
                                    </x-filament::dropdown.header>
                                    <x-filament::dropdown.list>
                                        @foreach ($this->formatosSugeridos as $tipoValue => $label)
                                            <x-filament::dropdown.list.item
                                                wire:click="crearRegistroDesdeTicket('{{ $tipoValue }}')"
                                                wire:loading.attr="disabled"
                                                icon="heroicon-m-document-text">
                                                {{ $label }}
                                            </x-filament::dropdown.list.item>
                                        @endforeach
                                    </x-filament::dropdown.list>
                                </x-filament::dropdown>
                            @endif

                            <x-filament::link
                                :href="url('admin/' . ($ticket->empresa?->ruc ?? '') . '/tickets/' . $ticket->id)"
                                size="sm"
                                color="gray"
                                icon="heroicon-m-arrow-top-right-on-square"
                                target="_blank">
                                Abrir
                            </x-filament::link>
                        </div>
                    </div>
                </div>

                {{-- Conversation --}}
                <div class="flex-1 overflow-y-auto p-4" style="max-height: calc(70vh - 220px);">
                    <div class="mx-auto flex max-w-[640px] flex-col gap-3">

                        {{-- Original description --}}
                        <div class="flex justify-start">
                            <div class="max-w-[80%]">
                                <div class="mb-0.5 ml-2 text-[11px] font-semibold text-indigo-400">
                                    {{ $ticket->creador?->name ?? 'Usuario' }}
                                </div>
                                <div class="rounded-2xl rounded-bl-sm bg-gray-100 px-4 py-2.5 dark:bg-white/8">
                                    <div class="m-0 text-sm leading-relaxed text-gray-800 dark:text-gray-200">{!! $this->sanitizeHtml($ticket->descripcion ?? '') !!}</div>
                                    <div class="mt-1 text-right">
                                        <span class="text-[10px] text-gray-400 dark:text-gray-500">{{ $ticket->created_at->format('d/m/Y H:i') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @foreach ($this->mensajes as $mensaje)
                            @php
                                $isAgent = $mensaje->autor?->hasRole(['super_admin', 'agente_helpdesk']);
                            @endphp

                            @if ($mensaje->isInterno())
                                {{-- Internal note --}}
                                <div class="flex justify-center">
                                    <div class="w-full max-w-[85%]">
                                        <div class="rounded-xl border border-dashed border-warning-300/30 bg-warning-50/50 px-4 py-2.5 dark:border-warning-500/30 dark:bg-warning-500/[0.08]">
                                            <div class="mb-1 flex items-center gap-1.5">
                                                <span class="text-[10px] font-bold uppercase tracking-wide text-warning-500">&#128274; Nota interna</span>
                                                <span class="text-[10px] text-warning-400/60 dark:text-warning-500/60">&middot; {{ $mensaje->autor?->name ?? 'Agente' }}</span>
                                            </div>
                                            <p class="m-0 text-sm text-warning-700 dark:text-warning-200">{{ $mensaje->contenido }}</p>
                                            <div class="mt-1 text-right">
                                                <span class="text-[10px] text-warning-300 dark:text-warning-500/40">{{ $mensaje->created_at->format('d/m/Y H:i') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            @elseif ($isAgent)
                                {{-- Agent message --}}
                                <div class="flex justify-end">
                                    <div class="max-w-[80%]">
                                        <div class="mb-0.5 mr-2 text-right text-[11px] font-semibold text-success-500 dark:text-success-400">
                                            &#9989; {{ $mensaje->autor?->name ?? 'Agente' }}
                                        </div>
                                        <div class="rounded-2xl rounded-tr-sm bg-success-50 px-4 py-2.5 dark:bg-success-500/[0.12]">
                                            <p class="m-0 text-sm leading-relaxed text-gray-800 dark:text-gray-200">{{ $mensaje->contenido }}</p>
                                            @if ($mensaje->adjuntos->isNotEmpty())
                                                <div class="mt-2 flex flex-col gap-1">
                                                    @foreach ($mensaje->adjuntos as $adjunto)
                                                        <a href="{{ route('tickets.adjunto.download', $adjunto) }}"
                                                           class="flex items-center gap-2 rounded-lg bg-success-100/50 px-2.5 py-1.5 no-underline dark:bg-success-500/10">
                                                            <span class="text-sm">&#128206;</span>
                                                            <span class="max-w-[180px] truncate text-xs text-success-700 dark:text-success-300">{{ $adjunto->nombre_original }}</span>
                                                            <span class="ml-auto text-[10px] text-success-400 dark:text-success-500/50">{{ number_format($adjunto->tamano / 1024, 0) }}KB</span>
                                                        </a>
                                                    @endforeach
                                                </div>
                                            @endif
                                            <div class="mt-1 text-right">
                                                <span class="text-[10px] text-success-300 dark:text-success-500/40">{{ $mensaje->created_at->format('d/m/Y H:i') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            @else
                                {{-- User message --}}
                                <div class="flex justify-start">
                                    <div class="max-w-[80%]">
                                        <div class="mb-0.5 ml-2 text-[11px] font-semibold text-indigo-400">
                                            {{ $mensaje->autor?->name ?? 'Usuario' }}
                                        </div>
                                        <div class="rounded-2xl rounded-bl-sm bg-gray-100 px-4 py-2.5 dark:bg-white/8">
                                            <p class="m-0 text-sm leading-relaxed text-gray-800 dark:text-gray-200">{{ $mensaje->contenido }}</p>
                                            @if ($mensaje->adjuntos->isNotEmpty())
                                                <div class="mt-2 flex flex-col gap-1">
                                                    @foreach ($mensaje->adjuntos as $adjunto)
                                                        <a href="{{ route('tickets.adjunto.download', $adjunto) }}"
                                                           class="flex items-center gap-2 rounded-lg bg-gray-50 px-2.5 py-1.5 no-underline dark:bg-white/5">
                                                            <span class="text-sm">&#128206;</span>
                                                            <span class="max-w-[180px] truncate text-xs text-gray-600 dark:text-gray-300">{{ $adjunto->nombre_original }}</span>
                                                            <span class="ml-auto text-[10px] text-gray-400 dark:text-gray-500">{{ number_format($adjunto->tamano / 1024, 0) }}KB</span>
                                                        </a>
                                                    @endforeach
                                                </div>
                                            @endif
                                            <div class="mt-1 text-right">
                                                <span class="text-[10px] text-gray-400 dark:text-gray-500">{{ $mensaje->created_at->format('d/m/Y H:i') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach

                        @if ($this->mensajes->isEmpty())
                            <div class="py-6 text-center">
                                <p class="text-sm text-gray-500">No hay mensajes aun.</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Reply box --}}
                @if ($ticket->estado !== 'cerrado')
                    <div class="border-t border-gray-200 dark:border-white/10 p-4">
                        <form wire:submit="enviarRespuesta">
                            <textarea wire:model="respuestaContenido"
                                      rows="3"
                                      placeholder="Escribe tu respuesta..."
                                      class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition placeholder:text-gray-400 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white dark:placeholder:text-gray-500"></textarea>
                            <div class="mt-2 flex items-center justify-between">
                                <label class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                    <input type="checkbox" wire:model="respuestaInterna"
                                           class="rounded border border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500 dark:border-white/10 dark:bg-white/5">
                                    Nota interna
                                </label>
                                <x-filament::button type="submit" size="sm" icon="heroicon-m-paper-airplane" wire:loading.attr="disabled">
                                    Enviar
                                </x-filament::button>
                            </div>
                        </form>
                    </div>
                @endif

            @else
                {{-- No ticket selected --}}
                <div class="flex flex-1 flex-col items-center justify-center text-gray-400">
                    <x-heroicon-o-chat-bubble-left-right class="h-16 w-16 mb-4" />
                    <p class="text-lg font-medium">Selecciona un ticket</p>
                    <p class="text-sm">Haz clic en un ticket de la lista para ver su detalle</p>
                </div>
            @endif
        </div>
    </div>{{-- /LEFT: ticket panel --}}

    {{-- RIGHT: Counter cards with sparklines --}}
    <div class="lg:col-span-3 flex flex-col gap-2">
        @foreach ($cards as $i => $card)
            @php
                $trendData = $trends[$card['key']];
                $today = end($trendData);
                $yesterday = $trendData[count($trendData) - 2] ?? $today;
                $delta = $today - $yesterday;
            @endphp
            <button
                wire:click="{{ $card['wire'] ?? 'limpiarFiltros' }}"
                class="relative overflow-hidden rounded-xl bg-white p-3 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 text-left transition hover:ring-primary-500/50 {{ $card['active'] ? 'ring-primary-500 ring-2' : '' }}"
                x-data="{
                    init() {
                        new Chart(this.$refs.spark{{ $i }}, {
                            type: 'line',
                            data: {
                                labels: @js($trendData).map((_, i) => i),
                                datasets: [{
                                    data: @js($trendData),
                                    borderColor: '{{ $card['color'] }}',
                                    backgroundColor: '{{ $card['color'] }}18',
                                    fill: true,
                                    tension: 0.4,
                                    pointRadius: 0,
                                    borderWidth: 1.5,
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: { legend: { display: false }, tooltip: { enabled: false } },
                                scales: { x: { display: false }, y: { display: false, beginAtZero: true } },
                                animation: { duration: 500 },
                            }
                        });
                    }
                }">
                <div class="flex items-center justify-between">
                    <div class="min-w-0">
                        <span class="text-[11px] font-medium text-gray-500 dark:text-gray-400">{{ $card['label'] }}</span>
                        <div class="flex items-baseline gap-2">
                            <span class="text-xl font-bold" style="color: {{ $card['color'] }}">{{ $card['value'] }}</span>
                            @if ($delta > 0)
                                <span class="text-[10px] font-semibold text-success-500">+{{ $delta }}</span>
                            @elseif ($delta < 0)
                                <span class="text-[10px] font-semibold text-danger-500">{{ $delta }}</span>
                            @else
                                <span class="text-[10px] text-gray-400">=</span>
                            @endif
                        </div>
                    </div>
                    <div style="width: 72px; height: 30px;">
                        <canvas x-ref="spark{{ $i }}"></canvas>
                    </div>
                </div>
            </button>
        @endforeach
    </div>

    </div>{{-- /Main layout --}}
    </div>{{-- /wrapper --}}
</x-filament-panels::page>
