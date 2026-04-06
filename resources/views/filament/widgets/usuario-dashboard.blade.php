@php $d = $this->getData(); @endphp

<x-filament-widgets::widget>
    {{-- Row 1: Welcome bar --}}
    <x-filament::section>
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Hola, {{ explode(' ', $d['user']->name)[0] }}</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ $d['ticketsAbiertos'] }} ticket(s) abierto(s) · {{ $d['registrosMes'] }} registro(s) este mes
                    @if ($d['equipo']) · Equipo {{ $d['equipo']->codigo_interno }} @endif
                    @if (!$d['politicasOk']) · <span class="text-danger-500">{{ $d['politicasPendientes'] }} politica(s) pendiente(s)</span> @endif
                </p>
            </div>
            <div class="flex gap-2">
                <x-filament::button tag="a" href="/admin/{{ $d['tenant']?->ruc }}/tickets/create" icon="heroicon-m-plus" size="sm">
                    Nuevo ticket
                </x-filament::button>
                <x-filament::button tag="a" href="/admin/{{ $d['tenant']?->ruc }}/registros/create" icon="heroicon-m-plus" size="sm" color="success">
                    Nuevo registro
                </x-filament::button>
            </div>
        </div>
    </x-filament::section>

    {{-- Row 2: Tickets + Registros --}}
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-top:16px;">

        {{-- Tickets --}}
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
                    <div class="py-8 text-center">
                        <x-heroicon-o-ticket class="mx-auto h-10 w-10 text-gray-300 dark:text-gray-600 mb-2" />
                        <p class="text-sm text-gray-500 dark:text-gray-400">Sin tickets recientes</p>
                        <x-filament::button tag="a" href="/admin/{{ $d['tenant']?->ruc }}/tickets/create" size="sm" class="mt-3">Crear ticket</x-filament::button>
                    </div>
                @endforelse
            </div>
        </x-filament::section>

        {{-- Registros --}}
        <x-filament::section heading="Mis registros" icon="heroicon-o-document-text">
            <x-slot name="headerEnd">
                <a href="/admin/{{ $d['tenant']?->ruc }}/registros" class="text-xs font-medium text-primary-600 hover:underline dark:text-primary-400">Ver todos</a>
            </x-slot>

            <div class="divide-y divide-gray-100 dark:divide-white/5">
                @forelse ($d['misRegistros'] as $registro)
                    <a href="/admin/{{ $d['tenant']?->ruc }}/registros/{{ $registro->id }}/edit" class="flex items-center justify-between py-3 transition hover:opacity-80">
                        <div class="min-w-0 flex-1 pr-3">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $registro->numero_registro }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $registro->created_at->diffForHumans() }}</p>
                        </div>
                        <x-filament::badge color="primary">{{ $registro->tipo_formato }}</x-filament::badge>
                    </a>
                @empty
                    <div class="py-8 text-center">
                        <x-heroicon-o-document-text class="mx-auto h-10 w-10 text-gray-300 dark:text-gray-600 mb-2" />
                        <p class="text-sm text-gray-500 dark:text-gray-400">Sin registros recientes</p>
                        <x-filament::button tag="a" href="/admin/{{ $d['tenant']?->ruc }}/registros/create" size="sm" color="success" class="mt-3">Crear registro</x-filament::button>
                    </div>
                @endforelse
            </div>
        </x-filament::section>
    </div>
</x-filament-widgets::widget>
