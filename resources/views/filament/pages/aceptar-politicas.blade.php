<x-filament-panels::page>
    <div class="mx-auto max-w-3xl">
        @if ($politicaActual)
            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="mb-4 flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary-50 dark:bg-primary-500/10">
                        <x-heroicon-o-shield-check class="h-6 w-6 text-primary-600 dark:text-primary-400" />
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $politicaActual->titulo }}</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Version {{ $politicaActual->version }}</p>
                    </div>
                </div>

                <div class="prose prose-sm dark:prose-invert max-h-[50vh] overflow-y-auto rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800">
                    {!! $politicaActual->contenido !!}
                </div>

                <div class="mt-6 border-t border-gray-200 pt-4 dark:border-gray-700">
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="checkbox" wire:model="acepto"
                               class="mt-0.5 rounded border border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500 dark:border-white/10 dark:bg-white/5">
                        <span class="text-sm text-gray-700 dark:text-gray-300">
                            He leido y acepto esta politica en su totalidad. Entiendo que su incumplimiento puede tener consecuencias legales y laborales.
                        </span>
                    </label>

                    <div class="mt-4 flex justify-end">
                        <x-filament::button wire:click="aceptar" wire:loading.attr="disabled" icon="heroicon-m-check" size="lg">
                            Aceptar y continuar
                        </x-filament::button>
                    </div>
                </div>
            </div>
        @else
            <div class="text-center py-12 text-gray-500">
                <x-heroicon-o-check-circle class="mx-auto h-12 w-12 text-success-500 mb-3" />
                <p class="text-lg font-medium">No tienes politicas pendientes</p>
            </div>
        @endif
    </div>
</x-filament-panels::page>
