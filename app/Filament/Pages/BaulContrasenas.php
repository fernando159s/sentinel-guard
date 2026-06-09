<?php

namespace App\Filament\Pages;

use App\Enums\TipoActivoDigital;
use App\Models\ActivoDigital;
use App\Models\ActivoDigitalCredencial;
use App\Services\AuditService;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use Livewire\Component;

/**
 * Baul de Contrasenas: una vista transversal y buscable de TODAS las
 * credenciales de activos digitales que el usuario tiene permitido ver,
 * sin tener que entrar cuenta por cuenta.
 *
 * Reutiliza la infraestructura de seguridad existente:
 *  - Cifrado en reposo (cast 'encrypted' en ActivoDigitalCredencial).
 *  - Autorizacion por ActivoDigitalCredencialPolicy::view() y el scope
 *    ActivoDigitalCredencial::visiblesPara() (aislamiento por empresa + responsables).
 *  - Auditoria de cada revelado/copiado via AuditService.
 */
class BaulContrasenas extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedKey;

    protected static string|\UnitEnum|null $navigationGroup = 'Activos Digitales';

    protected static ?string $title = 'Baúl de Contraseñas';

    protected static ?string $navigationLabel = 'Baúl de Contraseñas';

    protected static ?string $slug = 'baul-contrasenas';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.baul-contrasenas';

    /**
     * Accede quien pueda ver al menos una credencial: super_admin y
     * admin_empresa siempre; un usuario no-admin solo si es responsable
     * de al menos una cuenta (vera unicamente las suyas).
     */
    public static function canAccess(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        if ($user->hasRole(['super_admin', 'admin_empresa'])) {
            return true;
        }

        return ActivoDigital::whereHas('responsables', fn (Builder $q) => $q->whereKey($user->id))->exists();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ActivoDigitalCredencial::query()
                    ->visiblesPara(auth()->user(), Filament::getTenant()?->id)
                    ->with('activoDigital')
            )
            ->columns([
                TextColumn::make('activoDigital.nombre')
                    ->label('Cuenta')
                    ->weight('medium')
                    ->description(fn (ActivoDigitalCredencial $record): ?string => $record->activoDigital?->codigo_interno)
                    ->searchable(),
                TextColumn::make('activoDigital.tipo')
                    ->label('Tipo')
                    ->badge()
                    ->icon(fn ($state): ?string => $state instanceof TipoActivoDigital ? $state->icon() : null)
                    ->formatStateUsing(fn ($state): string => $state instanceof TipoActivoDigital ? $state->label() : (string) $state),
                TextColumn::make('etiqueta')
                    ->label('Credencial')
                    ->searchable(),
                TextColumn::make('usuario')
                    ->label('Usuario')
                    // Nunca se imprime el valor real: solo se indica si existe.
                    ->formatStateUsing(fn ($state): string => filled($state) ? '••••••••' : '—'),
                TextColumn::make('password')
                    ->label('Contraseña')
                    ->state('••••••••'),
                TextColumn::make('activoDigital.proveedor')
                    ->label('Proveedor')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('activoDigital.codigo_interno')
                    ->label('Código')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Actualizada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('tipo')
                    ->label('Tipo de cuenta')
                    ->options(TipoActivoDigital::options())
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        $data['value'] ?? null,
                        fn (Builder $q, $tipo) => $q->whereHas(
                            'activoDigital',
                            fn (Builder $a) => $a->where('tipo', $tipo)
                        )
                    )),
            ])
            ->recordActions([
                Action::make('revelar')
                    ->label('Ver')
                    ->icon('heroicon-o-eye')
                    ->color('warning')
                    ->visible(fn (ActivoDigitalCredencial $record): bool => auth()->user()?->can('view', $record) ?? false)
                    ->modalHeading('Credenciales')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Cerrar')
                    // El log se registra al montar la accion (una vez por apertura);
                    // modalContent puede re-renderizarse y duplicaria el registro.
                    ->mountUsing(fn (ActivoDigitalCredencial $record) => static::auditarRevelacion($record))
                    ->modalContent(fn (ActivoDigitalCredencial $record): HtmlString => static::contenidoRevelado($record)),
                Action::make('copiarUsuario')
                    ->label('Usuario')
                    ->icon('heroicon-o-clipboard-document')
                    ->color('gray')
                    ->visible(fn (ActivoDigitalCredencial $record): bool => filled($record->usuario) && (auth()->user()?->can('view', $record) ?? false))
                    ->action(fn (ActivoDigitalCredencial $record, Component $livewire) => static::copiarCampo($record, 'usuario', 'Usuario', $livewire)),
                Action::make('copiarPassword')
                    ->label('Copiar')
                    ->icon('heroicon-o-clipboard')
                    ->color('primary')
                    ->visible(fn (ActivoDigitalCredencial $record): bool => filled($record->password) && (auth()->user()?->can('view', $record) ?? false))
                    ->action(fn (ActivoDigitalCredencial $record, Component $livewire) => static::copiarCampo($record, 'password', 'Contraseña', $livewire)),
            ])
            ->defaultSort('updated_at', 'desc')
            ->emptyStateHeading('Sin contraseñas disponibles')
            ->emptyStateDescription('Aquí aparecerán, cifradas, todas las credenciales de las cuentas a las que tienes acceso.')
            ->emptyStateIcon(Heroicon::OutlinedKey);
    }

    /**
     * Registra en auditoria que se revelaron las credenciales (una vez por apertura).
     */
    protected static function auditarRevelacion(ActivoDigitalCredencial $record): void
    {
        AuditService::log(
            accion: 'credencial_revelada',
            entidad: 'ActivoDigitalCredencial',
            entidadId: $record->id,
            datosNuevos: static::datosAcceso($record),
            empresaId: $record->activoDigital?->empresa_id,
        );
    }

    /**
     * Renderiza los valores descifrados para el modal de revelado.
     */
    protected static function contenidoRevelado(ActivoDigitalCredencial $record): HtmlString
    {
        $row = fn (string $label, ?string $value): string => '<div class="py-2 border-b border-gray-100 dark:border-gray-800">'
            .'<p class="text-xs font-medium text-gray-500">'.e($label).'</p>'
            .'<p class="text-sm text-gray-900 dark:text-gray-100 font-mono break-all">'.(filled($value) ? e($value) : '—').'</p></div>';

        $cuenta = $record->activoDigital?->nombre;

        return new HtmlString(
            ($cuenta ? $row('Cuenta', $cuenta) : '')
            .$row('Etiqueta', $record->etiqueta)
            .$row('Usuario / correo', $record->usuario)
            .$row('Contraseña', $record->password)
            .$row('2FA / TOTP seed', $record->dato_2fa)
            .$row('Códigos de recuperación', $record->recovery)
            .$row('Notas', $record->notas)
        );
    }

    /**
     * Copia un campo sensible al portapapeles del cliente:
     *  - Reverifica la autorizacion en el servidor (defensa en profundidad).
     *  - Audita el acceso (accion 'credencial_copiada').
     *  - Despacha el valor descifrado a Alpine, que lo escribe en el
     *    portapapeles y lo borra a los ~20s (ver baul-contrasenas.blade.php).
     */
    protected static function copiarCampo(ActivoDigitalCredencial $record, string $campo, string $etiquetaCampo, Component $livewire): void
    {
        if (! (auth()->user()?->can('view', $record) ?? false)) {
            Notification::make()->title('No autorizado')->danger()->send();

            return;
        }

        $valor = (string) ($record->{$campo} ?? '');

        if ($valor === '') {
            Notification::make()->title('Sin valor que copiar')->warning()->send();

            return;
        }

        AuditService::log(
            accion: 'credencial_copiada',
            entidad: 'ActivoDigitalCredencial',
            entidadId: $record->id,
            datosNuevos: ['campo' => $campo] + static::datosAcceso($record),
            empresaId: $record->activoDigital?->empresa_id,
        );

        $livewire->dispatch('baul-copiar', valor: $valor, etiqueta: $etiquetaCampo);

        Notification::make()
            ->title('Copiado al portapapeles')
            ->body($etiquetaCampo.' · se limpiará automáticamente en 20 segundos.')
            ->success()
            ->send();
    }

    /**
     * Metadatos comunes para auditar el acceso a una credencial.
     * Incluye el nombre del actor (denormalizado) para que el registro
     * sea legible aunque luego se elimine el usuario o cambie de empresa.
     *
     * @return array<string, mixed>
     */
    protected static function datosAcceso(ActivoDigitalCredencial $record): array
    {
        return [
            'etiqueta' => $record->etiqueta,
            'activo' => $record->activoDigital?->nombre,
            'activo_digital_id' => $record->activo_digital_id,
            'por' => auth()->user()?->name,
        ];
    }
}
