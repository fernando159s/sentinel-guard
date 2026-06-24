<x-filament-panels::page>
    <div
        x-data="{
            scrollAbajo() { this.$nextTick(() => { const c = this.$refs.contenedor; if (c) c.scrollTop = c.scrollHeight; }); },
        }"
        x-init="scrollAbajo()"
        @asistente-responder.window="$wire.responder().then(() => scrollAbajo())"
        class="flex flex-col"
        style="height: calc(100vh - 14rem); min-height: 28rem;"
    >
        <x-filament::section class="flex-1 flex flex-col overflow-hidden !p-0">
            {{-- Mensajes --}}
            <div
                x-ref="contenedor"
                x-effect="$wire.mensajes.length; scrollAbajo()"
                class="flex-1 overflow-y-auto p-4 space-y-4"
            >
                @foreach ($mensajes as $mensaje)
                    @php $esUsuario = $mensaje['role'] === 'user'; @endphp
                    <div @class([
                        'flex',
                        'justify-end' => $esUsuario,
                        'justify-start' => ! $esUsuario,
                    ])>
                        <div @class([
                            'max-w-[80%] rounded-xl px-4 py-2.5 text-sm leading-relaxed whitespace-pre-line shadow-sm',
                            'bg-primary-600 text-white' => $esUsuario,
                            'bg-gray-100 text-gray-900 dark:bg-white/5 dark:text-gray-100' => ! $esUsuario,
                        ])>
                            @unless ($esUsuario)
                                <p class="mb-1 text-xs font-semibold text-primary-600 dark:text-primary-400">
                                    Asistente SecuriForm
                                </p>
                            @endunless

                            {{ $mensaje['content'] }}

                            @if (! empty($mensaje['herramientas']))
                                <div class="mt-2 flex flex-wrap gap-1">
                                    @foreach (array_unique($mensaje['herramientas']) as $herramienta)
                                        <span class="inline-flex items-center gap-1 rounded-md bg-primary-50 px-2 py-0.5 text-[10px] font-medium text-primary-700 dark:bg-primary-400/10 dark:text-primary-300">
                                            <x-filament::icon icon="heroicon-m-wrench-screwdriver" class="h-3 w-3" />
                                            {{ $herramienta }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach

                {{-- Indicador de "escribiendo" --}}
                <div x-show="$wire.procesando" x-cloak class="flex justify-start">
                    <div class="rounded-xl bg-gray-100 px-4 py-3 dark:bg-white/5">
                        <div class="flex items-center gap-1.5">
                            <span class="h-2 w-2 animate-bounce rounded-full bg-gray-400 [animation-delay:-0.3s]"></span>
                            <span class="h-2 w-2 animate-bounce rounded-full bg-gray-400 [animation-delay:-0.15s]"></span>
                            <span class="h-2 w-2 animate-bounce rounded-full bg-gray-400"></span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Entrada --}}
            <form
                wire:submit="enviar"
                class="border-t border-gray-200 p-3 dark:border-white/10"
            >
                <div class="flex items-end gap-2">
                    <textarea
                        wire:model.live.debounce.300ms="entrada"
                        x-on:keydown.enter.prevent="$event.target.value.trim() && $wire.enviar()"
                        x-bind:disabled="$wire.procesando"
                        rows="1"
                        placeholder="Escribe tu mensaje… (p. ej. «registra una cuenta WhatsApp de ventas» o «¿qué activos vencen este mes?»)"
                        class="fi-input block w-full resize-none rounded-lg border-gray-300 bg-white text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/20 dark:bg-white/5 dark:text-white"
                    ></textarea>

                    <x-filament::button
                        type="submit"
                        icon="heroicon-m-paper-airplane"
                        x-bind:disabled="$wire.procesando || ! $wire.entrada.trim()"
                    >
                        Enviar
                    </x-filament::button>
                </div>

                <div class="mt-2 flex items-center justify-between px-1">
                    <p class="text-[11px] text-gray-400 dark:text-gray-500">
                        Solo responde sobre SecuriForm · Nunca compartas contraseñas ni códigos 2FA.
                    </p>
                    <button
                        type="button"
                        wire:click="limpiar"
                        class="text-[11px] font-medium text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                    >
                        Limpiar conversación
                    </button>
                </div>
            </form>
        </x-filament::section>
    </div>
</x-filament-panels::page>
