<?php

namespace App\Filament\Resources\Registros\Pages;

use App\Filament\Resources\Registros\RegistroResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\HtmlString;

class EditRegistro extends EditRecord
{
    protected static string $resource = RegistroResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->label('Desactivar'),
            RestoreAction::make()->label('Restaurar'),
            ForceDeleteAction::make()->label('Eliminar permanente'),
        ];
    }

    public function mount(int|string $record): void
    {
        parent::mount($record);

        \Filament\Support\Facades\FilamentView::registerRenderHook(
            PanelsRenderHook::HEAD_END,
            fn () => new HtmlString('<style>.fi-grid.lg\:fi-grid-cols { columns: 1 !important; }</style>'),
        );
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['modificado_por'] = auth()->id();

        return $data;
    }
}
