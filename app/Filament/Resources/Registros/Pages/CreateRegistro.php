<?php

namespace App\Filament\Resources\Registros\Pages;

use App\Enums\TipoFormato;
use App\Filament\Resources\Registros\RegistroResource;
use App\Services\RegistroNumberService;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateRegistro extends CreateRecord
{
    protected static string $resource = RegistroResource::class;

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
