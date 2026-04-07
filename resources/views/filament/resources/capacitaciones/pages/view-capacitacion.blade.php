<x-filament-panels::page>
    <div style="display:grid; grid-template-columns: 1fr 3fr; gap:20px; min-height:calc(100vh - 12rem);">

        {{-- LEFT: Datos de la capacitacion --}}
        <div style="display:flex; flex-direction:column; border-radius:12px; overflow:hidden;" class="bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">

            {{-- Header --}}
            <div style="padding:16px 20px; border-bottom:1px solid rgba(128,128,128,0.15); display:flex; align-items:center; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:10px;">
                    <x-heroicon-o-academic-cap style="width:20px; height:20px;" class="text-primary-600 dark:text-primary-400" />
                    <div class="text-sm font-semibold text-gray-900 dark:text-white">Detalle</div>
                </div>
                <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold
                    {{ $this->record->modalidad->value === 'presencial' ? 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400' : 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-400' }}">
                    {{ $this->record->modalidad->label() }}
                </span>
            </div>

            {{-- Body --}}
            <div style="flex:1; overflow-y:auto; padding:20px; display:flex; flex-direction:column; gap:16px;">

                {{-- Tema --}}
                <div>
                    <div class="text-[10px] font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400" style="margin-bottom:2px;">Tema</div>
                    <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ $this->record->tema }}</div>
                </div>

                @if($this->record->descripcion)
                <div>
                    <div class="text-[10px] font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400" style="margin-bottom:2px;">Descripcion</div>
                    <div class="text-xs text-gray-600 dark:text-gray-400">{{ $this->record->descripcion }}</div>
                </div>
                @endif

                <div style="border-top:1px solid rgba(128,128,128,0.1); padding-top:12px;">
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        <div>
                            <div class="text-[10px] font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400" style="margin-bottom:2px;">Fecha</div>
                            <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ $this->record->fecha->format('d/m/Y') }}</div>
                        </div>
                        <div>
                            <div class="text-[10px] font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400" style="margin-bottom:2px;">Hora</div>
                            <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ \Illuminate\Support\Str::substr($this->record->hora_inicio, 0, 5) }}</div>
                        </div>
                        <div>
                            <div class="text-[10px] font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400" style="margin-bottom:2px;">Duracion</div>
                            <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ $this->record->duracion_minutos }} min</div>
                        </div>
                        <div>
                            <div class="text-[10px] font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400" style="margin-bottom:2px;">Expositor</div>
                            <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ $this->record->expositor }}</div>
                        </div>
                    </div>
                </div>

                {{-- Asistencia resumen --}}
                <div style="border-top:1px solid rgba(128,128,128,0.1); padding-top:12px;">
                    @php
                        $pct = $this->record->porcentajeAsistencia();
                        $total = $this->record->asistencias()->count();
                        $asistieron = $this->record->asistencias()->where('asistio', true)->count();
                    @endphp
                    <div class="text-[10px] font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400" style="margin-bottom:6px;">Asistencia</div>
                    <div style="display:flex; align-items:center; gap:10px;">
                        <div style="flex:1; height:6px; border-radius:3px; background:rgba(128,128,128,0.15); overflow:hidden;">
                            <div style="height:100%; width:{{ $pct }}%; border-radius:3px; background:{{ $pct >= 80 ? '#10b981' : ($pct >= 50 ? '#f59e0b' : '#ef4444') }};"></div>
                        </div>
                        <span class="text-xs font-semibold" style="color:{{ $pct >= 80 ? '#10b981' : ($pct >= 50 ? '#f59e0b' : '#ef4444') }};">{{ $pct }}%</span>
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400" style="margin-top:4px;">{{ $asistieron }}/{{ $total }} confirmados</div>
                </div>

                {{-- Mi asistencia (trabajador) --}}
                @unless($this->isAdmin)
                <div style="border-top:1px solid rgba(128,128,128,0.1); padding-top:12px;">
                    <div class="text-[10px] font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400" style="margin-bottom:6px;">Mi asistencia</div>

                    @php
                        $miAsistencia = $this->miAsistencia;
                        $yaTermino = $this->record->yaTermino();
                        $enVentana = $this->record->dentroVentanaConfirmacion();
                    @endphp

                    @if($miAsistencia && $miAsistencia->asistio)
                        <div style="display:flex; align-items:center; gap:6px; padding:8px 10px; border-radius:8px;" class="bg-green-50 dark:bg-green-500/10">
                            <x-heroicon-s-check-circle style="width:16px; height:16px;" class="text-green-600 dark:text-green-400" />
                            <span class="text-xs font-medium text-green-700 dark:text-green-400">
                                Confirmada el {{ $miAsistencia->fecha_confirmacion->format('d/m/Y H:i') }}
                            </span>
                        </div>
                    @elseif(!$yaTermino)
                        <div style="display:flex; align-items:center; gap:6px; padding:8px 10px; border-radius:8px;" class="bg-gray-50 dark:bg-gray-800">
                            <x-heroicon-o-clock style="width:16px; height:16px;" class="text-gray-400" />
                            <span class="text-xs text-gray-500 dark:text-gray-400">Aun no ha terminado</span>
                        </div>
                    @elseif($enVentana)
                        <div style="display:flex; flex-direction:column; gap:8px; padding:8px 10px; border-radius:8px;" class="bg-yellow-50 dark:bg-yellow-500/10">
                            <div style="display:flex; align-items:center; gap:6px;">
                                <x-heroicon-o-exclamation-triangle style="width:16px; height:16px;" class="text-yellow-600 dark:text-yellow-400" />
                                <span class="text-xs text-yellow-700 dark:text-yellow-400">24h para confirmar</span>
                            </div>
                            <button wire:click="confirmarAsistencia" class="w-full rounded-lg bg-green-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-green-700 transition">
                                Confirmar asistencia
                            </button>
                        </div>
                    @else
                        <div style="display:flex; align-items:center; gap:6px; padding:8px 10px; border-radius:8px;" class="bg-red-50 dark:bg-red-500/10">
                            <x-heroicon-s-x-circle style="width:16px; height:16px;" class="text-red-600 dark:text-red-400" />
                            <span class="text-xs text-red-700 dark:text-red-400">Periodo expirado</span>
                        </div>
                    @endif
                </div>
                @endunless

            </div>
        </div>

        {{-- RIGHT: Tabla de asistencia (admin) o mensaje (trabajador) --}}
        <div style="display:flex; flex-direction:column; border-radius:12px; overflow:hidden;" class="bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">

            <div style="padding:16px 20px; border-bottom:1px solid rgba(128,128,128,0.15); display:flex; align-items:center; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:10px;">
                    <x-heroicon-o-clipboard-document-list style="width:20px; height:20px;" class="text-primary-600 dark:text-primary-400" />
                    <div class="text-sm font-semibold text-gray-900 dark:text-white">Control de asistencia</div>
                </div>
                @php
                    $total = $this->record->asistencias()->count();
                    $asistieron = $this->record->asistencias()->where('asistio', true)->count();
                @endphp
                <span class="text-xs text-gray-500 dark:text-gray-400">{{ $asistieron }}/{{ $total }}</span>
            </div>

            <div style="flex:1; overflow-y:auto;">
                @if($this->isAdmin)
                    {{ $this->table }}
                @else
                    <div style="padding:40px 20px; text-align:center;">
                        <x-heroicon-o-lock-closed style="width:32px; height:32px; margin:0 auto 12px;" class="text-gray-300 dark:text-gray-600" />
                        <p class="text-sm text-gray-500 dark:text-gray-400">El control detallado de asistencia es visible solo para administradores.</p>
                    </div>
                @endif
            </div>
        </div>

    </div>
</x-filament-panels::page>
