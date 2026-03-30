<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function afterCreate(): void
    {
        $this->record->syncRoles($this->record->rol);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // admin_empresa auto-assigns their own empresa
        if (! auth()->user()->hasRole('super_admin') && auth()->user()->empresa_id) {
            $data['empresa_id'] = auth()->user()->empresa_id;
        }

        return $data;
    }
}
