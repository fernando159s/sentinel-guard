<x-filament-panels::page>
    {{-- Infolist del ticket --}}
    {{ $this->infolist }}

    {{-- Hilo de conversación --}}
    <x-filament::section heading="Conversación" icon="heroicon-o-chat-bubble-left-right">
        <div class="space-y-4">
            {{-- Descripción original --}}
            <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4 bg-gray-50 dark:bg-gray-800">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                        {{ $this->record->creador?->name ?? 'Usuario' }}
                    </span>
                    <span class="text-xs text-gray-500">
                        {{ $this->record->created_at->format('d/m/Y H:i') }}
                    </span>
                </div>
                <div class="prose prose-sm dark:prose-invert max-w-none">
                    {!! $this->record->descripcion !!}
                </div>
            </div>

            {{-- Mensajes del hilo --}}
            @foreach ($this->getMensajes() as $mensaje)
                <div @class([
                    'rounded-lg border p-4',
                    'border-yellow-300 bg-yellow-50 dark:border-yellow-700 dark:bg-yellow-900/20' => $mensaje->isInterno(),
                    'border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900' => ! $mensaje->isInterno(),
                ])>
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                {{ $mensaje->autor?->name ?? 'Usuario' }}
                            </span>
                            @if ($mensaje->isInterno())
                                <span class="inline-flex items-center rounded-full bg-yellow-100 px-2 py-0.5 text-xs font-medium text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100">
                                    Nota interna
                                </span>
                            @endif
                        </div>
                        <span class="text-xs text-gray-500">
                            {{ $mensaje->created_at->format('d/m/Y H:i') }}
                        </span>
                    </div>
                    <div class="prose prose-sm dark:prose-invert max-w-none">
                        {{ $mensaje->contenido }}
                    </div>

                    {{-- Adjuntos --}}
                    @if ($mensaje->adjuntos->isNotEmpty())
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach ($mensaje->adjuntos as $adjunto)
                                <a href="{{ Storage::disk('tickets')->url($adjunto->nombre_almacenado) }}"
                                   target="_blank"
                                   class="inline-flex items-center gap-1 rounded-md bg-gray-100 px-2 py-1 text-xs text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                                    <x-heroicon-m-paper-clip class="h-3 w-3" />
                                    {{ $adjunto->nombre_original }}
                                    <span class="text-gray-400">({{ number_format($adjunto->tamano / 1024, 0) }}KB)</span>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach

            @if ($this->getMensajes()->isEmpty())
                <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">
                    No hay mensajes aún. Usa el botón "Responder" para iniciar la conversación.
                </p>
            @endif
        </div>
    </x-filament::section>
</x-filament-panels::page>
