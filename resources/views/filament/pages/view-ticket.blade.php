<x-filament-panels::page>
    <style>
        .fi-main, .fi-page, .fi-page-main, body { overflow: hidden !important; max-height: 100vh !important; }
    </style>
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3" style="height: calc(100vh - 8rem);">

        {{-- LEFT: Ticket info --}}
        <div class="lg:col-span-1 flex flex-col gap-3 overflow-y-auto">
            {{-- Status card --}}
            <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="flex items-center justify-between mb-3">
                    <span class="font-mono text-sm text-gray-500 dark:text-gray-400">{{ $this->record->numero_ticket }}</span>
                    @php
                        $estadoColor = match($this->record->estado) {
                            'nuevo' => 'info', 'en_revision' => 'warning', 'esperando_usuario' => 'gray',
                            'resuelto' => 'success', 'cerrado' => 'gray', default => 'gray',
                        };
                    @endphp
                    <x-filament::badge :color="$estadoColor">
                        {{ match($this->record->estado) {
                            'nuevo' => 'Nuevo', 'en_revision' => 'En revision', 'esperando_usuario' => 'Esperando',
                            'resuelto' => 'Resuelto', 'cerrado' => 'Cerrado', default => $this->record->estado,
                        } }}
                    </x-filament::badge>
                </div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $this->record->asunto }}</h2>
                <p class="mt-1 text-xs text-gray-500">{{ ucfirst($this->record->categoria) }} · {{ ucfirst($this->record->prioridad) }}</p>
            </div>

            {{-- Details --}}
            <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 space-y-2">
                <div class="flex justify-between text-xs">
                    <span class="text-gray-500 dark:text-gray-400">Creado por</span>
                    <span class="font-medium text-gray-900 dark:text-white">{{ $this->record->creador?->name }}</span>
                </div>
                <div class="flex justify-between text-xs">
                    <span class="text-gray-500 dark:text-gray-400">Agente</span>
                    <span class="font-medium text-gray-900 dark:text-white">{{ $this->record->agente?->name ?? 'Sin asignar' }}</span>
                </div>
                <div class="flex justify-between text-xs">
                    <span class="text-gray-500 dark:text-gray-400">Empresa</span>
                    <span class="font-medium text-gray-900 dark:text-white">{{ $this->record->empresa?->razon_social }}</span>
                </div>
                <div class="flex justify-between text-xs">
                    <span class="text-gray-500 dark:text-gray-400">Creado</span>
                    <span class="text-gray-600 dark:text-gray-300">{{ $this->record->created_at->format('d/m/Y H:i') }}</span>
                </div>
                @if ($this->record->fecha_cierre)
                    <div class="flex justify-between text-xs">
                        <span class="text-gray-500 dark:text-gray-400">Cerrado</span>
                        <span class="text-gray-600 dark:text-gray-300">{{ $this->record->fecha_cierre->format('d/m/Y H:i') }}</span>
                    </div>
                @endif
            </div>

            {{-- Equipos vinculados --}}
            @if ($this->record->equipos->isNotEmpty())
                <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-2">Equipos vinculados</p>
                    @foreach ($this->record->equipos as $equipo)
                        <p class="text-sm text-gray-900 dark:text-white">{{ $equipo->codigo_interno }} — {{ $equipo->marca }} {{ $equipo->modelo }}</p>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- RIGHT: Conversation --}}
        <div class="lg:col-span-2 flex flex-col rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 overflow-hidden">

            {{-- Chat header --}}
            <div class="border-b border-gray-200 dark:border-white/10 px-4 py-2.5 flex items-center justify-between">
                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Conversacion</span>
                <span class="text-xs text-gray-400">{{ $this->getMensajes()->count() }} mensaje(s)</span>
            </div>

            {{-- Chat messages --}}
            <div class="flex-1 overflow-y-auto p-4">
                <div class="mx-auto flex max-w-[640px] flex-col gap-3">

                    {{-- Original description --}}
                    <div class="flex justify-start">
                        <div class="max-w-[80%]">
                            <div class="mb-0.5 ml-2 text-[11px] font-semibold text-indigo-400">
                                {{ $this->record->creador?->name ?? 'Usuario' }}
                            </div>
                            <div class="rounded-2xl rounded-bl-sm bg-gray-100 px-4 py-2.5 dark:bg-white/8">
                                <p class="m-0 text-sm leading-relaxed text-gray-800 dark:text-gray-200">{{ strip_tags($this->record->descripcion) }}</p>
                                <div class="mt-1 text-right">
                                    <span class="text-[10px] text-gray-400 dark:text-gray-500">{{ $this->record->created_at->format('d/m/Y H:i') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    @foreach ($this->getMensajes() as $mensaje)
                        @php $isAgent = $mensaje->autor?->hasRole(['super_admin', 'agente_helpdesk']); @endphp

                        @if ($mensaje->isInterno())
                            <div class="flex justify-center">
                                <div class="w-full max-w-[85%]">
                                    <div class="rounded-xl border border-dashed border-warning-300/30 bg-warning-50/50 px-4 py-2.5 dark:border-warning-500/30 dark:bg-warning-500/[0.08]">
                                        <div class="mb-1 flex items-center gap-1.5">
                                            <span class="text-[10px] font-bold uppercase tracking-wide text-warning-500">&#128274; Nota interna</span>
                                            <span class="text-[10px] text-warning-400/60">&middot; {{ $mensaje->autor?->name }}</span>
                                        </div>
                                        <p class="m-0 text-sm text-warning-700 dark:text-warning-200">{{ $mensaje->contenido }}</p>
                                        <div class="mt-1 text-right"><span class="text-[10px] text-warning-300 dark:text-warning-500/40">{{ $mensaje->created_at->format('d/m/Y H:i') }}</span></div>
                                    </div>
                                </div>
                            </div>
                        @elseif ($isAgent)
                            <div class="flex justify-end">
                                <div class="max-w-[80%]">
                                    <div class="mb-0.5 mr-2 text-right text-[11px] font-semibold text-success-500 dark:text-success-400">
                                        &#9989; {{ $mensaje->autor?->name }}
                                    </div>
                                    <div class="rounded-2xl rounded-tr-sm bg-success-50 px-4 py-2.5 dark:bg-success-500/[0.12]">
                                        <p class="m-0 text-sm leading-relaxed text-gray-800 dark:text-gray-200">{{ $mensaje->contenido }}</p>
                                        @if ($mensaje->adjuntos->isNotEmpty())
                                            <div class="mt-2 flex flex-col gap-1">
                                                @foreach ($mensaje->adjuntos as $adjunto)
                                                    <a href="{{ route('tickets.adjunto.download', $adjunto) }}" class="flex items-center gap-2 rounded-lg bg-success-100/50 px-2.5 py-1.5 no-underline dark:bg-success-500/10">
                                                        <span class="text-sm">&#128206;</span>
                                                        <span class="max-w-[180px] truncate text-xs text-success-700 dark:text-success-300">{{ $adjunto->nombre_original }}</span>
                                                        <span class="ml-auto text-[10px] text-success-400">{{ number_format($adjunto->tamano / 1024, 0) }}KB</span>
                                                    </a>
                                                @endforeach
                                            </div>
                                        @endif
                                        <div class="mt-1 text-right"><span class="text-[10px] text-success-300 dark:text-success-500/40">{{ $mensaje->created_at->format('d/m/Y H:i') }}</span></div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="flex justify-start">
                                <div class="max-w-[80%]">
                                    <div class="mb-0.5 ml-2 text-[11px] font-semibold text-indigo-400">{{ $mensaje->autor?->name ?? 'Usuario' }}</div>
                                    <div class="rounded-2xl rounded-bl-sm bg-gray-100 px-4 py-2.5 dark:bg-white/8">
                                        <p class="m-0 text-sm leading-relaxed text-gray-800 dark:text-gray-200">{{ $mensaje->contenido }}</p>
                                        @if ($mensaje->adjuntos->isNotEmpty())
                                            <div class="mt-2 flex flex-col gap-1">
                                                @foreach ($mensaje->adjuntos as $adjunto)
                                                    <a href="{{ route('tickets.adjunto.download', $adjunto) }}" class="flex items-center gap-2 rounded-lg bg-gray-50 px-2.5 py-1.5 no-underline dark:bg-white/5">
                                                        <span class="text-sm">&#128206;</span>
                                                        <span class="max-w-[180px] truncate text-xs text-gray-600 dark:text-gray-300">{{ $adjunto->nombre_original }}</span>
                                                        <span class="ml-auto text-[10px] text-gray-400">{{ number_format($adjunto->tamano / 1024, 0) }}KB</span>
                                                    </a>
                                                @endforeach
                                            </div>
                                        @endif
                                        <div class="mt-1 text-right"><span class="text-[10px] text-gray-400 dark:text-gray-500">{{ $mensaje->created_at->format('d/m/Y H:i') }}</span></div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach

                    @if ($this->getMensajes()->isEmpty())
                        <div class="py-8 text-center">
                            <p class="text-2xl mb-1">&#128172;</p>
                            <p class="text-sm text-gray-500">Sin mensajes aun.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
