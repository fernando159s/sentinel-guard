<x-filament-panels::page>
    {{-- Datos de la capacitacion --}}
    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-900">
        <div class="flex items-start justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $this->record->tema }}</h2>
                @if($this->record->descripcion)
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $this->record->descripcion }}</p>
                @endif
            </div>
            <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold
                {{ $this->record->modalidad->value === 'presencial' ? 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400' : 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-400' }}">
                @if($this->record->modalidad->value === 'presencial')
                    <x-heroicon-s-user-group class="h-3.5 w-3.5" />
                @else
                    <x-heroicon-s-computer-desktop class="h-3.5 w-3.5" />
                @endif
                {{ $this->record->modalidad->label() }}
            </span>
        </div>

        <div class="mt-5 grid grid-cols-2 gap-4 md:grid-cols-4">
            <div class="flex items-center gap-2">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary-50 dark:bg-primary-500/10">
                    <x-heroicon-o-calendar class="h-4 w-4 text-primary-600 dark:text-primary-400" />
                </div>
                <div>
                    <span class="text-[10px] font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Fecha</span>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $this->record->fecha->format('d/m/Y') }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary-50 dark:bg-primary-500/10">
                    <x-heroicon-o-clock class="h-4 w-4 text-primary-600 dark:text-primary-400" />
                </div>
                <div>
                    <span class="text-[10px] font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Hora</span>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ \Illuminate\Support\Str::substr($this->record->hora_inicio, 0, 5) }} ({{ $this->record->duracion_minutos }} min)</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary-50 dark:bg-primary-500/10">
                    <x-heroicon-o-user class="h-4 w-4 text-primary-600 dark:text-primary-400" />
                </div>
                <div>
                    <span class="text-[10px] font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Expositor</span>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $this->record->expositor }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                @php
                    $pct = $this->record->porcentajeAsistencia();
                    $pctColor = $pct >= 80 ? 'success' : ($pct >= 50 ? 'warning' : 'danger');
                @endphp
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-{{ $pctColor }}-50 dark:bg-{{ $pctColor }}-500/10">
                    <x-heroicon-o-chart-bar class="h-4 w-4 text-{{ $pctColor }}-600 dark:text-{{ $pctColor }}-400" />
                </div>
                <div>
                    <span class="text-[10px] font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Asistencia</span>
                    <p class="text-sm font-semibold text-{{ $pctColor }}-600 dark:text-{{ $pctColor }}-400">{{ $pct }}%</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Seccion del trabajador (no admin) --}}
    @unless($this->isAdmin)
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Mi asistencia</h3>

            @php
                $miAsistencia = $this->miAsistencia;
                $yaTermino = $this->record->yaTermino();
                $enVentana = $this->record->dentroVentanaConfirmacion();
            @endphp

            @if($miAsistencia && $miAsistencia->asistio)
                <div class="flex items-center gap-2 rounded-lg bg-green-50 p-3 dark:bg-green-500/10">
                    <x-heroicon-s-check-circle class="h-5 w-5 text-green-600 dark:text-green-400" />
                    <span class="text-sm font-medium text-green-700 dark:text-green-400">
                        Asistencia confirmada el {{ $miAsistencia->fecha_confirmacion->format('d/m/Y') }} a las {{ $miAsistencia->fecha_confirmacion->format('H:i') }}
                    </span>
                </div>
            @elseif(!$yaTermino)
                <div class="flex items-center gap-2 rounded-lg bg-gray-50 p-3 dark:bg-gray-800">
                    <x-heroicon-o-clock class="h-5 w-5 text-gray-400" />
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        La capacitacion aun no ha terminado. Podras confirmar tu asistencia despues de que finalice.
                    </span>
                </div>
            @elseif($enVentana)
                <div class="flex items-center justify-between rounded-lg bg-yellow-50 p-3 dark:bg-yellow-500/10">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-exclamation-triangle class="h-5 w-5 text-yellow-600 dark:text-yellow-400" />
                        <span class="text-sm text-yellow-700 dark:text-yellow-400">Tienes 24 horas para confirmar tu asistencia.</span>
                    </div>
                    <button
                        wire:click="confirmarAsistencia"
                        class="rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700 transition"
                    >
                        Confirmar mi asistencia
                    </button>
                </div>
            @else
                <div class="flex items-center gap-2 rounded-lg bg-red-50 p-3 dark:bg-red-500/10">
                    <x-heroicon-s-x-circle class="h-5 w-5 text-red-600 dark:text-red-400" />
                    <span class="text-sm text-red-700 dark:text-red-400">
                        Periodo de confirmacion expirado (24h). Contacta a tu administrador.
                    </span>
                </div>
            @endif
        </div>
    @endunless

    {{-- Tabla de asistencia (admin) --}}
    @if($this->isAdmin)
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-clipboard-document-list class="h-5 w-5 text-gray-400" />
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Control de asistencia</h3>
                </div>
                @php
                    $total = $this->record->asistencias()->count();
                    $asistieron = $this->record->asistencias()->where('asistio', true)->count();
                @endphp
                <span class="text-xs text-gray-500 dark:text-gray-400">{{ $asistieron }}/{{ $total }} confirmados</span>
            </div>
            {{ $this->table }}
        </div>
    @endif
</x-filament-panels::page>
