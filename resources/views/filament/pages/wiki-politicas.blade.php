<x-filament-panels::page>
    <div style="display:grid; grid-template-columns: 1fr 3fr; gap:20px; min-height:calc(100vh - 12rem);">

        {{-- LEFT: Lista de politicas --}}
        <div style="display:flex; flex-direction:column; border-radius:12px; overflow:hidden;" class="bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">

            <div style="padding:16px 20px; border-bottom:1px solid rgba(128,128,128,0.15); display:flex; align-items:center; gap:10px;">
                <x-heroicon-o-book-open style="width:20px; height:20px;" class="text-primary-600 dark:text-primary-400" />
                <div class="text-sm font-semibold text-gray-900 dark:text-white">Politicas</div>
            </div>

            <div style="flex:1; overflow-y:auto;">
                @foreach($this->politicas as $p)
                    <button
                        wire:click="seleccionarPolitica({{ $p->id }})"
                        style="width:100%; text-align:left; padding:12px 20px; border:none; border-bottom:1px solid rgba(128,128,128,0.08); cursor:pointer; transition:background 0.15s;"
                        class="{{ $this->politicaSeleccionadaId === $p->id ? 'bg-primary-50 dark:bg-primary-500/10' : 'bg-transparent hover:bg-gray-50 dark:hover:bg-gray-800' }}"
                    >
                        <div class="text-sm font-medium {{ $this->politicaSeleccionadaId === $p->id ? 'text-primary-700 dark:text-primary-400' : 'text-gray-900 dark:text-white' }}">
                            {{ $p->titulo }}
                        </div>
                        <div style="display:flex; gap:6px; margin-top:4px;">
                            <span class="inline-flex rounded-full px-1.5 py-0.5 text-[9px] font-semibold bg-info-100 text-info-700 dark:bg-info-500/20 dark:text-info-400">
                                v{{ $p->version }}
                            </span>
                            @if($p->obligatoria)
                                <span class="inline-flex rounded-full px-1.5 py-0.5 text-[9px] font-semibold bg-danger-100 text-danger-700 dark:bg-danger-500/20 dark:text-danger-400">
                                    Obligatoria
                                </span>
                            @else
                                <span class="inline-flex rounded-full px-1.5 py-0.5 text-[9px] font-semibold bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400">
                                    Informativa
                                </span>
                            @endif
                            @if($p->archivo_path)
                                <span class="inline-flex rounded-full px-1.5 py-0.5 text-[9px] font-semibold bg-success-100 text-success-700 dark:bg-success-500/20 dark:text-success-400">
                                    Doc
                                </span>
                            @endif
                        </div>
                    </button>
                @endforeach

                @if($this->politicas->isEmpty())
                    <div style="padding:40px 20px; text-align:center;">
                        <p class="text-sm text-gray-500 dark:text-gray-400">No hay politicas activas.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- RIGHT: Contenido de la politica --}}
        <div style="display:flex; flex-direction:column; border-radius:12px; overflow:hidden;" class="bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">

            @if($this->politicaSeleccionada)
                {{-- Header --}}
                <div style="padding:16px 20px; border-bottom:1px solid rgba(128,128,128,0.15); display:flex; align-items:center; justify-content:space-between;">
                    <div>
                        <div class="text-base font-bold text-gray-900 dark:text-white">{{ $this->politicaSeleccionada->titulo }}</div>
                        <div style="display:flex; gap:6px; margin-top:4px;">
                            <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold bg-info-100 text-info-700 dark:bg-info-500/20 dark:text-info-400">
                                v{{ $this->politicaSeleccionada->version }}
                            </span>
                            @if($this->politicaSeleccionada->obligatoria)
                                <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold bg-danger-100 text-danger-700 dark:bg-danger-500/20 dark:text-danger-400">
                                    Obligatoria
                                </span>
                            @else
                                <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400">
                                    Informativa
                                </span>
                            @endif
                            @php
                                $aceptada = $this->politicaSeleccionada->aceptadaPor(auth()->id());
                            @endphp
                            <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $aceptada ? 'bg-success-100 text-success-700 dark:bg-success-500/20 dark:text-success-400' : 'bg-warning-100 text-warning-700 dark:bg-warning-500/20 dark:text-warning-400' }}">
                                {{ $aceptada ? 'Aceptada' : 'Pendiente' }}
                            </span>
                        </div>
                    </div>

                    <div style="display:flex; gap:8px;">
                        @if($this->politicaSeleccionada->archivo_path)
                            <button wire:click="descargarArchivo" class="inline-flex items-center gap-1.5 rounded-lg bg-primary-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-primary-700 transition">
                                <x-heroicon-o-document-arrow-down style="width:14px; height:14px;" />
                                Descargar documento
                            </button>
                        @endif
                        @if($this->isAdmin)
                            @php
                                $tenant = \Filament\Facades\Filament::getTenant();
                                $editUrl = "/admin/{$tenant->ruc}/politicas/{$this->politicaSeleccionada->id}/edit";
                            @endphp
                            <a href="{{ $editUrl }}" class="inline-flex items-center gap-1.5 rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 transition">
                                <x-heroicon-o-pencil style="width:14px; height:14px;" />
                                Editar
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Content --}}
                <div style="flex:1; overflow-y:auto; padding:24px;">
                    <div class="prose prose-sm dark:prose-invert max-w-none">
                        {!! $this->politicaSeleccionada->contenido !!}
                    </div>
                </div>
            @else
                <div style="flex:1; display:flex; align-items:center; justify-content:center;">
                    <div style="text-align:center;">
                        <x-heroicon-o-book-open style="width:48px; height:48px; margin:0 auto 12px;" class="text-gray-300 dark:text-gray-600" />
                        <p class="text-sm text-gray-500 dark:text-gray-400">Selecciona una politica para ver su contenido.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-filament-panels::page>
