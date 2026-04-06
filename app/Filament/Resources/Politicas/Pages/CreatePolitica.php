<?php

namespace App\Filament\Resources\Politicas\Pages;

use App\Filament\Resources\Politicas\PoliticaResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\HtmlString;

class CreatePolitica extends CreateRecord
{
    protected static string $resource = PoliticaResource::class;

    public function mount(): void
    {
        parent::mount();

        \Filament\Support\Facades\FilamentView::registerRenderHook(
            PanelsRenderHook::HEAD_END,
            fn () => new HtmlString('<style>.fi-grid.lg\:fi-grid-cols { columns: 1 !important; }</style>'),
        );
    }
}
