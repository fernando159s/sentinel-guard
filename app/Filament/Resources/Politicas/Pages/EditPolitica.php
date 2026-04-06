<?php

namespace App\Filament\Resources\Politicas\Pages;

use App\Filament\Resources\Politicas\PoliticaResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\HtmlString;

class EditPolitica extends EditRecord
{
    protected static string $resource = PoliticaResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }

    public function mount(int|string $record): void
    {
        parent::mount($record);

        \Filament\Support\Facades\FilamentView::registerRenderHook(
            PanelsRenderHook::HEAD_END,
            fn () => new HtmlString('<style>.fi-grid.lg\:fi-grid-cols { columns: 1 !important; }</style>'),
        );
    }
}
