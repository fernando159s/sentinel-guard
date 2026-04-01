<x-filament-panels::page>
    {{-- Counters --}}
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
        <button wire:click="$set('filtroAgente', 'sin_asignar')"
                class="fi-wi-stats-overview-stat relative rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 text-left transition hover:ring-primary-500/50 {{ $filtroAgente === 'sin_asignar' ? 'ring-primary-500 ring-2' : '' }}">
            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Sin asignar</span>
            <span class="mt-1 block text-2xl font-bold text-danger-600 dark:text-danger-400">{{ $this->counters['sin_asignar'] }}</span>
        </button>

        <button wire:click="$set('filtroEstado', 'nuevo')"
                class="fi-wi-stats-overview-stat relative rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 text-left transition hover:ring-primary-500/50 {{ $filtroEstado === 'nuevo' ? 'ring-primary-500 ring-2' : '' }}">
            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Nuevos hoy</span>
            <span class="mt-1 block text-2xl font-bold text-info-600 dark:text-info-400">{{ $this->counters['nuevos_hoy'] }}</span>
        </button>

        <button wire:click="$set('filtroAgente', 'mis')"
                class="fi-wi-stats-overview-stat relative rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 text-left transition hover:ring-primary-500/50 {{ $filtroAgente === 'mis' ? 'ring-primary-500 ring-2' : '' }}">
            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Mis tickets</span>
            <span class="mt-1 block text-2xl font-bold text-primary-600 dark:text-primary-400">{{ $this->counters['mis_tickets'] }}</span>
        </button>

        <button wire:click="$set('filtroEstado', 'en_revision')"
                class="fi-wi-stats-overview-stat relative rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 text-left transition hover:ring-primary-500/50 {{ $filtroEstado === 'en_revision' ? 'ring-primary-500 ring-2' : '' }}">
            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">En revision</span>
            <span class="mt-1 block text-2xl font-bold text-warning-600 dark:text-warning-400">{{ $this->counters['en_revision'] }}</span>
        </button>

        <button wire:click="$set('filtroEstado', 'esperando_usuario')"
                class="fi-wi-stats-overview-stat relative rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 text-left transition hover:ring-primary-500/50 {{ $filtroEstado === 'esperando_usuario' ? 'ring-primary-500 ring-2' : '' }}">
            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Esperando usuario</span>
            <span class="mt-1 block text-2xl font-bold text-gray-600 dark:text-gray-400">{{ $this->counters['esperando'] }}</span>
        </button>

        <div class="fi-wi-stats-overview-stat relative rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 text-left">
            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Total abiertos</span>
            <span class="mt-1 block text-2xl font-bold text-gray-900 dark:text-white">{{ $this->counters['total_abiertos'] }}</span>
        </div>
    </div>

    {{-- Split layout --}}
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-5" style="min-height: 70vh;">

        {{-- LEFT: Ticket list --}}
        <div class="lg:col-span-2 flex flex-col rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">

            {{-- Filters bar --}}
            <div class="border-b border-gray-200 dark:border-white/10 p-3 space-y-2">
                <div class="flex gap-2">
                    <input type="text"
                           wire:model.live.debounce.300ms="busqueda"
                           placeholder="Buscar ticket..."
                           class="fi-input block w-full rounded-lg border-gray-300 text-sm shadow-sm dark:border-white/10 dark:bg-white/5 dark:text-white focus:border-primary-500 focus:ring-primary-500">
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
                            class="fi-input block w-full rounded-lg border-gray-300 text-xs shadow-sm dark:border-white/10 dark:bg-white/5 dark:text-white">
                        <option value="">Estado: Todos</option>
                        <option value="nuevo">Nuevo</option>
                        <option value="en_revision">En revision</option>
                        <option value="esperando_usuario">Esperando usuario</option>
                        <option value="resuelto">Resuelto</option>
                        <option value="cerrado">Cerrado</option>
                    </select>
                    <select wire:model.live="filtroPrioridad"
                            class="fi-input block w-full rounded-lg border-gray-300 text-xs shadow-sm dark:border-white/10 dark:bg-white/5 dark:text-white">
                        <option value="">Prioridad: Todas</option>
                        <option value="urgente">Urgente</option>
                        <option value="alta">Alta</option>
                        <option value="media">Media</option>
                        <option value="baja">Baja</option>
                    </select>
                    <select wire:model.live="filtroEmpresa"
                            class="fi-input block w-full rounded-lg border-gray-300 text-xs shadow-sm dark:border-white/10 dark:bg-white/5 dark:text-white">
                        <option value="">Empresa: Todas</option>
                        @foreach ($this->empresas as $id => $nombre)
                            <option value="{{ $id }}">{{ $nombre }}</option>
                        @endforeach
                    </select>
                    <select wire:model.live="filtroAgente"
                            class="fi-input block w-full rounded-lg border-gray-300 text-xs shadow-sm dark:border-white/10 dark:bg-white/5 dark:text-white">
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
                                <x-filament::button size="sm" color="info" wire:click="asignarAMi" icon="heroicon-m-hand-raised">
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
                                        @foreach ($this->formatosSugeridos as $tipoEnum => $label)
                                            <x-filament::dropdown.list.item
                                                wire:click="crearRegistroDesdeTicket('{{ $tipoEnum->value }}')"
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
                    <div style="max-width: 640px; margin: 0 auto; display: flex; flex-direction: column; gap: 12px;">

                        {{-- Original description --}}
                        <div style="display: flex; justify-content: flex-start;">
                            <div style="max-width: 80%;">
                                <div style="font-size: 11px; font-weight: 600; color: #818cf8; margin-bottom: 2px; margin-left: 8px;">
                                    {{ $ticket->creador?->name ?? 'Usuario' }}
                                </div>
                                <div style="background: rgba(255,255,255,0.08); border-radius: 16px 16px 16px 4px; padding: 10px 16px;">
                                    <div style="font-size: 14px; line-height: 1.5; color: #e5e7eb; overflow: hidden;">
                                        <style>.chat-desc img { max-width: 160px !important; height: auto !important; border-radius: 8px; display: block; margin: 4px 0; }</style>
                                        <div class="chat-desc">{!! strip_tags($ticket->descripcion, '<p><br><strong><em><u><a><img><ul><ol><li>') !!}</div>
                                    </div>
                                    <div style="text-align: right; margin-top: 4px;">
                                        <span style="font-size: 10px; color: #6b7280;">{{ $ticket->created_at->format('d/m/Y H:i') }}</span>
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
                                <div style="display: flex; justify-content: center;">
                                    <div style="max-width: 85%; width: 100%;">
                                        <div style="background: rgba(245,158,11,0.08); border: 1px dashed rgba(245,158,11,0.3); border-radius: 12px; padding: 10px 16px;">
                                            <div style="display: flex; align-items: center; gap: 6px; margin-bottom: 4px;">
                                                <span style="font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #f59e0b;">&#128274; Nota interna</span>
                                                <span style="font-size: 10px; color: rgba(245,158,11,0.6);">&middot; {{ $mensaje->autor?->name ?? 'Agente' }}</span>
                                            </div>
                                            <p style="font-size: 14px; color: #fde68a; margin: 0;">{{ $mensaje->contenido }}</p>
                                            <div style="text-align: right; margin-top: 4px;">
                                                <span style="font-size: 10px; color: rgba(245,158,11,0.4);">{{ $mensaje->created_at->format('d/m/Y H:i') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            @elseif ($isAgent)
                                {{-- Agent message --}}
                                <div style="display: flex; justify-content: flex-end;">
                                    <div style="max-width: 80%;">
                                        <div style="font-size: 11px; font-weight: 600; color: #34d399; margin-bottom: 2px; text-align: right; margin-right: 8px;">
                                            &#9989; {{ $mensaje->autor?->name ?? 'Agente' }}
                                        </div>
                                        <div style="background: rgba(16,185,129,0.12); border-radius: 16px 4px 16px 16px; padding: 10px 16px;">
                                            <p style="font-size: 14px; color: #e5e7eb; margin: 0; line-height: 1.5;">{{ $mensaje->contenido }}</p>
                                            @if ($mensaje->adjuntos->isNotEmpty())
                                                <div style="margin-top: 8px; display: flex; flex-direction: column; gap: 4px;">
                                                    @foreach ($mensaje->adjuntos as $adjunto)
                                                        <a href="{{ route('tickets.adjunto.download', $adjunto) }}"
                                                           style="display: flex; align-items: center; gap: 8px; background: rgba(16,185,129,0.1); border-radius: 8px; padding: 6px 10px; text-decoration: none;">
                                                            <span style="font-size: 14px;">&#128206;</span>
                                                            <span style="font-size: 12px; color: #6ee7b7; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 180px;">{{ $adjunto->nombre_original }}</span>
                                                            <span style="font-size: 10px; color: rgba(16,185,129,0.5); margin-left: auto;">{{ number_format($adjunto->tamano / 1024, 0) }}KB</span>
                                                        </a>
                                                    @endforeach
                                                </div>
                                            @endif
                                            <div style="text-align: right; margin-top: 4px;">
                                                <span style="font-size: 10px; color: rgba(16,185,129,0.4);">{{ $mensaje->created_at->format('d/m/Y H:i') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            @else
                                {{-- User message --}}
                                <div style="display: flex; justify-content: flex-start;">
                                    <div style="max-width: 80%;">
                                        <div style="font-size: 11px; font-weight: 600; color: #818cf8; margin-bottom: 2px; margin-left: 8px;">
                                            {{ $mensaje->autor?->name ?? 'Usuario' }}
                                        </div>
                                        <div style="background: rgba(255,255,255,0.08); border-radius: 16px 16px 16px 4px; padding: 10px 16px;">
                                            <p style="font-size: 14px; color: #e5e7eb; margin: 0; line-height: 1.5;">{{ $mensaje->contenido }}</p>
                                            @if ($mensaje->adjuntos->isNotEmpty())
                                                <div style="margin-top: 8px; display: flex; flex-direction: column; gap: 4px;">
                                                    @foreach ($mensaje->adjuntos as $adjunto)
                                                        <a href="{{ route('tickets.adjunto.download', $adjunto) }}"
                                                           style="display: flex; align-items: center; gap: 8px; background: rgba(255,255,255,0.05); border-radius: 8px; padding: 6px 10px; text-decoration: none;">
                                                            <span style="font-size: 14px;">&#128206;</span>
                                                            <span style="font-size: 12px; color: #d1d5db; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 180px;">{{ $adjunto->nombre_original }}</span>
                                                            <span style="font-size: 10px; color: #6b7280; margin-left: auto;">{{ number_format($adjunto->tamano / 1024, 0) }}KB</span>
                                                        </a>
                                                    @endforeach
                                                </div>
                                            @endif
                                            <div style="text-align: right; margin-top: 4px;">
                                                <span style="font-size: 10px; color: #6b7280;">{{ $mensaje->created_at->format('d/m/Y H:i') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach

                        @if ($this->mensajes->isEmpty())
                            <div style="text-align: center; padding: 24px 0;">
                                <p style="font-size: 14px; color: #6b7280;">No hay mensajes aun.</p>
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
                                      class="fi-input block w-full rounded-lg border-gray-300 text-sm shadow-sm dark:border-white/10 dark:bg-white/5 dark:text-white focus:border-primary-500 focus:ring-primary-500"></textarea>
                            <div class="mt-2 flex items-center justify-between">
                                <label class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                    <input type="checkbox" wire:model="respuestaInterna"
                                           class="fi-checkbox-input rounded border-gray-300 text-primary-600 shadow-sm dark:border-white/10 dark:bg-white/5">
                                    Nota interna
                                </label>
                                <x-filament::button type="submit" size="sm" icon="heroicon-m-paper-airplane">
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
    </div>
</x-filament-panels::page>
