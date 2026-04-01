<x-filament-panels::page>
    {{ $this->infolist }}

    <x-filament::section heading="Conversación">
        <div style="max-width: 640px; margin: 0 auto; display: flex; flex-direction: column; gap: 12px;">

            {{-- Descripción original (usuario, izquierda) --}}
            <div style="display: flex; justify-content: flex-start;">
                <div style="max-width: 75%;">
                    <div style="font-size: 11px; font-weight: 600; color: #818cf8; margin-bottom: 2px; margin-left: 8px;">
                        {{ $this->record->creador?->name ?? 'Usuario' }}
                    </div>
                    <div style="background: rgba(255,255,255,0.08); border-radius: 16px 16px 16px 4px; padding: 10px 16px;">
                        <div style="font-size: 14px; line-height: 1.5; color: #e5e7eb; overflow: hidden;">
                            <style>.chat-desc img { max-width: 160px !important; height: auto !important; border-radius: 8px; display: block; margin: 4px 0; }</style>
                            <div class="chat-desc">{!! strip_tags($this->record->descripcion, '<p><br><strong><em><u><a><img><ul><ol><li>') !!}</div>
                        </div>
                        <div style="text-align: right; margin-top: 4px;">
                            <span style="font-size: 10px; color: #6b7280;">{{ $this->record->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            @foreach ($this->getMensajes() as $mensaje)
                @php
                    $isAgent = $mensaje->autor?->hasRole(['super_admin', 'agente_helpdesk']);
                @endphp

                {{-- Nota interna (centrada, amarillo) --}}
                @if ($mensaje->isInterno())
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

                {{-- Agente (derecha, verde) --}}
                @elseif ($isAgent)
                    <div style="display: flex; justify-content: flex-end;">
                        <div style="max-width: 75%;">
                            <div style="font-size: 11px; font-weight: 600; color: #34d399; margin-bottom: 2px; text-align: right; margin-right: 8px;">
                                &#9989; {{ $mensaje->autor?->name ?? 'Agente' }}
                            </div>
                            <div style="background: rgba(16,185,129,0.12); border-radius: 16px 4px 16px 16px; padding: 10px 16px;">
                                <p style="font-size: 14px; color: #e5e7eb; margin: 0; line-height: 1.5;">{{ $mensaje->contenido }}</p>

                                @if ($mensaje->adjuntos->isNotEmpty())
                                    <div style="margin-top: 8px; display: flex; flex-direction: column; gap: 4px;">
                                        @foreach ($mensaje->adjuntos as $adjunto)
                                            <a href="{{ route('tickets.adjunto.download', $adjunto) }}"
                                               style="display: flex; align-items: center; gap: 8px; background: rgba(16,185,129,0.1); border-radius: 8px; padding: 6px 10px; text-decoration: none; transition: background 0.2s;"
                                               onmouseover="this.style.background='rgba(16,185,129,0.2)'"
                                               onmouseout="this.style.background='rgba(16,185,129,0.1)'">
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

                {{-- Usuario (izquierda, gris) --}}
                @else
                    <div style="display: flex; justify-content: flex-start;">
                        <div style="max-width: 75%;">
                            <div style="font-size: 11px; font-weight: 600; color: #818cf8; margin-bottom: 2px; margin-left: 8px;">
                                {{ $mensaje->autor?->name ?? 'Usuario' }}
                            </div>
                            <div style="background: rgba(255,255,255,0.08); border-radius: 16px 16px 16px 4px; padding: 10px 16px;">
                                <p style="font-size: 14px; color: #e5e7eb; margin: 0; line-height: 1.5;">{{ $mensaje->contenido }}</p>

                                @if ($mensaje->adjuntos->isNotEmpty())
                                    <div style="margin-top: 8px; display: flex; flex-direction: column; gap: 4px;">
                                        @foreach ($mensaje->adjuntos as $adjunto)
                                            <a href="{{ route('tickets.adjunto.download', $adjunto) }}"
                                               style="display: flex; align-items: center; gap: 8px; background: rgba(255,255,255,0.05); border-radius: 8px; padding: 6px 10px; text-decoration: none; transition: background 0.2s;"
                                               onmouseover="this.style.background='rgba(255,255,255,0.1)'"
                                               onmouseout="this.style.background='rgba(255,255,255,0.05)'">
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

            @if ($this->getMensajes()->isEmpty())
                <div style="text-align: center; padding: 32px 0;">
                    <p style="font-size: 24px; margin-bottom: 4px;">&#128172;</p>
                    <p style="font-size: 14px; color: #6b7280;">No hay mensajes aún. Usa "Responder" para iniciar la conversación.</p>
                </div>
            @endif
        </div>
    </x-filament::section>
</x-filament-panels::page>
