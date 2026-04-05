<x-filament-widgets::widget>
    <x-filament::section heading="Mi estado" icon="heroicon-o-user-circle">
        @php $data = $this->getData(); @endphp

        <div class="space-y-1">
            @foreach ($data['items'] as $item)
                <div class="flex items-center justify-between rounded-lg px-3 py-2 {{ $item['ok'] ? 'bg-success-50/30 dark:bg-success-950/20' : 'bg-danger-50/30 dark:bg-danger-950/20' }}">
                    <div class="flex items-center gap-2.5">
                        @if ($item['ok'])
                            <x-heroicon-s-check-circle class="h-4 w-4 text-success-500" />
                        @else
                            <x-heroicon-s-x-circle class="h-4 w-4 text-danger-500" />
                        @endif
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ $item['label'] }}</span>
                    </div>
                    <span class="text-xs font-medium {{ $item['ok'] ? 'text-success-600 dark:text-success-400' : 'text-danger-600 dark:text-danger-400' }}">
                        {{ $item['value'] }}
                    </span>
                </div>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
