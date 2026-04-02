<?php

namespace App\Filament\Resources\Tickets\Pages;

use App\Filament\Resources\Tickets\TicketResource;
use App\Services\TicketNumberService;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateTicket extends CreateRecord
{
    protected static string $resource = TicketResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $tenant = Filament::getTenant();

        $data['empresa_id'] = $tenant->id;
        $data['creado_por'] = auth()->id();
        $data['numero_ticket'] = TicketNumberService::generate();
        $data['estado'] = 'nuevo';
        $data['fecha_ultima_actividad'] = now();

        return $data;
    }

    protected function afterCreate(): void
    {
        $equipos = $this->data['equipos'] ?? [];

        if (! empty($equipos)) {
            $this->record->equipos()->sync($equipos);
        }
    }
}
