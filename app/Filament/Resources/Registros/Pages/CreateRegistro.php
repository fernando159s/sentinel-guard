<?php

namespace App\Filament\Resources\Registros\Pages;

use App\Enums\TipoFormato;
use App\Filament\Resources\Registros\RegistroResource;
use App\Services\RegistroNumberService;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\HtmlString;

class CreateRegistro extends CreateRecord
{
    protected static string $resource = RegistroResource::class;

    public function mount(): void
    {
        parent::mount();

        \Filament\Support\Facades\FilamentView::registerRenderHook(
            PanelsRenderHook::HEAD_END,
            fn () => new HtmlString('<style>
                .fi-sc-form .fi-grid.lg\:fi-grid-cols { display: grid !important; columns: unset !important; break-inside: unset !important; }
                .fi-sc-form .fi-grid.lg\:fi-grid-cols > * { break-inside: unset !important; margin-bottom: 0 !important; }
            </style>'),
        );
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $tipo = TipoFormato::from($data['tipo_formato']);
        $tenant = Filament::getTenant();

        $data['empresa_id'] = $tenant->id;
        $data['creado_por'] = auth()->id();
        $data['numero_registro'] = RegistroNumberService::generate($tenant->id, $tipo);

        return $data;
    }
}
