<x-filament-panels::page>
    <div style="margin-bottom:16px; padding:16px 20px; border-radius:10px; display:flex; align-items:center; gap:12px;" class="bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        @if ($this->record->es_nda)
            <x-heroicon-o-lock-closed style="width:24px; height:24px;" class="text-warning-600 dark:text-warning-400" />
        @else
            <x-heroicon-o-shield-check style="width:24px; height:24px;" class="text-primary-600 dark:text-primary-400" />
        @endif
        <div style="flex:1;">
            <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ $this->record->titulo }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400">
                v{{ $this->record->version }}
                @if ($this->record->es_nda)
                    · NDA
                    @if ($this->record->vigencia_meses)
                        · Vigencia: {{ $this->record->vigencia_meses }} meses
                    @endif
                @endif
                · {{ $this->record->aceptaciones()->count() }} firma(s) registrada(s)
            </div>
        </div>
        <div style="display:flex; gap:8px;">
            <a href="{{ route('politicas.resumen-firmantes-pdf', $this->record) }}" target="_blank"
               style="display:inline-flex; align-items:center; gap:6px; padding:6px 12px; border-radius:8px; font-size:12px; font-weight:600; text-decoration:none; border:1px solid;"
               class="border-primary-300 bg-primary-50 text-primary-700 hover:bg-primary-100 dark:border-primary-500 dark:bg-primary-500/10 dark:text-primary-400 dark:hover:bg-primary-500/20">
                <x-heroicon-o-document-text style="width:14px; height:14px;" />
                Resumen firmantes
            </a>
            <a href="{{ route('politicas.pdf', $this->record) }}" target="_blank"
               style="display:inline-flex; align-items:center; gap:6px; padding:6px 12px; border-radius:8px; font-size:12px; font-weight:600; text-decoration:none; border:1px solid;"
               class="border-gray-200 text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-800">
                <x-heroicon-o-arrow-down-tray style="width:14px; height:14px;" />
                PDF base
            </a>
        </div>
    </div>

    {{ $this->table }}
</x-filament-panels::page>
