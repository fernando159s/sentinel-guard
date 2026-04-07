<x-filament-panels::page>
    <div style="margin-bottom:16px; padding:16px 20px; border-radius:10px; display:flex; align-items:center; gap:12px;" class="bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        <x-heroicon-o-lock-closed style="width:24px; height:24px;" class="text-warning-600 dark:text-warning-400" />
        <div>
            <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ $this->record->titulo }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400">
                v{{ $this->record->version }}
                @if ($this->record->vigencia_meses)
                    · Vigencia: {{ $this->record->vigencia_meses }} meses
                @endif
                · {{ $this->record->aceptaciones()->count() }} firma(s) registrada(s)
            </div>
        </div>
    </div>

    {{ $this->table }}
</x-filament-panels::page>
