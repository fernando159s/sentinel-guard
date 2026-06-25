<?php

namespace App\Filament\Resources\Baul;

use App\Enums\TipoActivoDigital;
use App\Filament\Pages\BaulContrasenas;
use App\Models\ActivoDigitalCredencial;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Resource "solo busqueda": no aparece en navegacion ni tiene paginas propias.
 * Su unico fin es exponer las credenciales en la busqueda global (Ctrl/Cmd+K)
 * para encontrar una cuenta por nombre/etiqueta/proveedor y aterrizar en el
 * Baul de Contrasenas ya filtrado.
 *
 * Seguridad:
 *  - getGlobalSearchEloquentQuery() acota a lo que el usuario puede ver
 *    (empresa + responsables) via el scope visiblesPara(): nunca filtra
 *    credenciales de otras empresas ni de cuentas ajenas.
 *  - Solo se busca/expone por campos NO sensibles (nombre de cuenta, etiqueta,
 *    proveedor, codigo). El usuario/contrasena cifrados nunca se buscan ni se
 *    muestran en los resultados.
 */
class CredencialBaulResource extends Resource
{
    protected static ?string $model = ActivoDigitalCredencial::class;

    protected static ?string $recordTitleAttribute = 'etiqueta';

    // Encabezado del grupo de resultados en la búsqueda global.
    protected static ?string $modelLabel = 'Contraseña';

    protected static ?string $pluralModelLabel = 'Baúl de Contraseñas';

    protected static ?string $slug = 'baul-credenciales-buscar';

    /**
     * El aislamiento por empresa lo hace getGlobalSearchEloquentQuery() via
     * visiblesPara(). Se desactiva el tenant-scope automatico de Filament
     * porque ActivoDigitalCredencial no tiene relacion `empresa` directa
     * (su empresa es transitiva a traves de activoDigital).
     */
    protected static bool $isScopedToTenant = false;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [];
    }

    /** El acceso a la busqueda es el mismo que al Baul. */
    public static function canAccess(): bool
    {
        return BaulContrasenas::canAccess();
    }

    /** @return array<string> */
    public static function getGloballySearchableAttributes(): array
    {
        return [
            'etiqueta',
            'activoDigital.nombre',
            'activoDigital.proveedor',
            'activoDigital.codigo_interno',
        ];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        $cuenta = $record->activoDigital?->nombre ?? 'Cuenta';

        return $cuenta.' — '.$record->etiqueta;
    }

    /** @return array<string, string> */
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        $tipo = $record->activoDigital?->tipo;

        return [
            'Tipo' => $tipo instanceof TipoActivoDigital ? $tipo->label() : '—',
            'Proveedor' => $record->activoDigital?->proveedor ?: '—',
            'Código' => $record->activoDigital?->codigo_interno ?? '—',
        ];
    }

    public static function getGlobalSearchResultUrl(Model $record): ?string
    {
        // Lleva al Baul pre-filtrado por la cuenta para ver/copiar sus contrasenas.
        return BaulContrasenas::getUrl(['q' => $record->activoDigital?->nombre]);
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return ActivoDigitalCredencial::query()
            ->visiblesPara(auth()->user(), Filament::getTenant()?->id)
            ->with('activoDigital');
    }
}
