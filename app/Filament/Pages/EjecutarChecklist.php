<?php

namespace App\Filament\Pages;

use App\Models\ChecklistEjecucion;
use App\Models\ChecklistPlantilla;
use App\Models\Equipo;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Livewire\Attributes\Url;

class EjecutarChecklist extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static bool $shouldRegisterNavigation = false;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole(['super_admin', 'admin_empresa']) ?? false;
    }

    protected static ?string $title = 'Ejecutar checklist';

    protected static ?string $slug = 'ejecutar-checklist';

    protected string $view = 'filament.pages.ejecutar-checklist';

    #[Url]
    public ?string $equipo_id = null;

    public ?string $plantilla_id = null;

    public ?Equipo $equipo = null;

    public ?ChecklistPlantilla $plantilla = null;

    public array $items = [];

    public string $observaciones_generales = '';

    public function mount(): void
    {
        if ($this->equipo_id) {
            $this->equipo = Equipo::find($this->equipo_id);
        }
    }

    public function getPlantillasProperty(): array
    {
        $empresaId = Filament::getTenant()?->id;

        return ChecklistPlantilla::where('empresa_id', $empresaId)
            ->where('activa', true)
            ->pluck('nombre', 'id')
            ->toArray();
    }

    public function getEquiposProperty(): array
    {
        $empresaId = Filament::getTenant()?->id;

        return Equipo::where('empresa_id', $empresaId)
            ->where('estado', 'activo')
            ->get()
            ->mapWithKeys(fn ($e) => [$e->id => "{$e->codigo_interno} — {$e->marca} {$e->modelo}"])
            ->toArray();
    }

    public function updatedPlantillaId(): void
    {
        $this->loadItems();
    }

    public function updatedEquipoId(): void
    {
        $this->equipo = $this->equipo_id ? Equipo::find($this->equipo_id) : null;
    }

    public function loadItems(): void
    {
        if (! $this->plantilla_id) {
            $this->items = [];
            $this->plantilla = null;

            return;
        }

        $this->plantilla = ChecklistPlantilla::find($this->plantilla_id);

        if (! $this->plantilla) {
            $this->items = [];

            return;
        }

        $this->items = collect($this->plantilla->items)->map(fn ($item) => [
            'nombre' => $item['nombre'],
            'descripcion' => $item['descripcion'] ?? '',
            'obligatorio' => $item['obligatorio'] ?? false,
            'cumple' => false,
            'observacion' => '',
        ])->toArray();
    }

    public function guardar(): void
    {
        if (! $this->equipo_id || ! $this->plantilla_id || empty($this->items)) {
            Notification::make()
                ->title('Selecciona equipo y plantilla')
                ->warning()
                ->send();

            return;
        }

        $resultados = collect($this->items)->map(fn ($item) => [
            'item' => $item['nombre'],
            'cumple' => (bool) $item['cumple'],
            'observacion' => $item['observacion'] ?? '',
        ])->toArray();

        $obligatoriosFallidos = collect($this->items)
            ->where('obligatorio', true)
            ->where('cumple', false)
            ->count();

        $estado = $obligatoriosFallidos > 0 ? 'con_observaciones' : 'completo';

        ChecklistEjecucion::create([
            'checklist_plantilla_id' => $this->plantilla_id,
            'equipo_id' => $this->equipo_id,
            'ejecutado_por' => auth()->id(),
            'fecha_ejecucion' => now(),
            'resultados' => $resultados,
            'estado' => $estado,
            'observaciones_generales' => $this->observaciones_generales ?: null,
        ]);

        $cumple = collect($resultados)->where('cumple', true)->count();
        $total = count($resultados);

        Notification::make()
            ->title('Checklist guardado')
            ->body("{$this->plantilla->nombre}: {$cumple}/{$total} items cumplen.")
            ->success()
            ->send();

        $tenant = Filament::getTenant();
        $this->redirect("/admin/{$tenant->ruc}/equipos/{$this->equipo_id}/edit");
    }
}
