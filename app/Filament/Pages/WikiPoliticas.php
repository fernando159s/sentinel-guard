<?php

namespace App\Filament\Pages;

use App\Models\Politica;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;

class WikiPoliticas extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static string|\UnitEnum|null $navigationGroup = 'Seguridad';

    protected static ?string $title = 'Wiki de Politicas';

    protected static ?string $slug = 'wiki-politicas';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.wiki-politicas';

    public ?int $politicaSeleccionadaId = null;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole(['super_admin', 'admin_empresa']) ?? false;
    }

    public function mount(): void
    {
        $primera = Politica::where('empresa_id', Filament::getTenant()?->id)
            ->where('activa', true)
            ->first();

        $this->politicaSeleccionadaId = $primera?->id;
    }

    public function seleccionarPolitica(int $id): void
    {
        $this->politicaSeleccionadaId = $id;
    }

    public function descargarArchivo()
    {
        $politica = Politica::findOrFail($this->politicaSeleccionadaId);

        if (! $politica->archivo_path) {
            return null;
        }

        return Storage::disk('local')->download(
            $politica->archivo_path,
            $politica->archivo_nombre ?? 'documento'
        );
    }

    public function getPoliticasProperty()
    {
        return Politica::where('empresa_id', Filament::getTenant()?->id)
            ->where('activa', true)
            ->orderBy('titulo')
            ->get();
    }

    public function getPoliticaSeleccionadaProperty(): ?Politica
    {
        if (! $this->politicaSeleccionadaId) {
            return null;
        }

        return Politica::find($this->politicaSeleccionadaId);
    }

    public function getIsAdminProperty(): bool
    {
        return auth()->user()?->hasRole(['super_admin', 'admin_empresa']) ?? false;
    }
}
