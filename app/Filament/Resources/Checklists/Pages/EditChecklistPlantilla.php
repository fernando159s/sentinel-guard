<?php

namespace App\Filament\Resources\Checklists\Pages;

use App\Filament\Resources\Checklists\ChecklistPlantillaResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\HtmlString;

class EditChecklistPlantilla extends EditRecord
{
    protected static string $resource = ChecklistPlantillaResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }

    public function mount(int|string $record): void
    {
        parent::mount($record);

        // Force single column for this page
        \Filament\Support\Facades\FilamentView::registerRenderHook(
            PanelsRenderHook::HEAD_END,
            fn () => new HtmlString('<style>.fi-grid.lg\:fi-grid-cols { columns: 1 !important; }</style>'),
        );
    }
}
