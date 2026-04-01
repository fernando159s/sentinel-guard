<x-filament-widgets::widget>
    <x-filament::section heading="Formatos de seguridad" icon="heroicon-o-document-text" collapsible>
        <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-4">
            @foreach ($this->getFormatos() as $formato)
                <a href="{{ $formato['url'] }}"
                   class="group flex items-center gap-3 rounded-lg border border-gray-200 bg-white p-3 transition hover:border-primary-500 hover:shadow-sm dark:border-white/10 dark:bg-white/5 dark:hover:border-primary-500">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-primary-50 text-xs font-bold text-primary-600 dark:bg-primary-500/10 dark:text-primary-400">
                        {{ $formato['prefix'] }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-gray-900 dark:text-white">{{ $formato['label'] }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $formato['count'] }} registros</p>
                    </div>
                </a>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
