<?php

namespace App\Filament\Resources\ActivosDigitales\RelationManagers;

use App\Models\ActivoDigitalCredencial;
use App\Services\AuditService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class CredencialesRelationManager extends RelationManager
{
    protected static string $relationship = 'credenciales';

    protected static ?string $title = 'Credenciales';

    /**
     * El tab de credenciales solo aparece para quien puede verlas:
     * permiso `ver_credenciales` o ser el responsable de la cuenta.
     */
    public static function canViewForRecord($ownerRecord, string $pageClass): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        if ($user->hasRole('super_admin')) {
            return true;
        }

        if ($user->hasRole('admin_empresa') && $ownerRecord->empresa_id === $user->empresa_id) {
            return true;
        }

        return $ownerRecord->esResponsable($user);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('etiqueta')
                    ->label('Etiqueta')
                    ->placeholder('Admin principal, API key, cuenta de respaldo...')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                TextInput::make('usuario')
                    ->label('Usuario / correo')
                    ->maxLength(255)
                    ->autocomplete(false),
                TextInput::make('password')
                    ->label('Contraseña')
                    ->password()
                    ->revealable()
                    ->autocomplete('new-password'),
                TextInput::make('dato_2fa')
                    ->label('2FA / TOTP seed')
                    ->password()
                    ->revealable()
                    ->helperText('Semilla del autenticador, si aplica'),
                Textarea::make('recovery')
                    ->label('Códigos de recuperación')
                    ->rows(2),
                Textarea::make('notas')
                    ->label('Notas')
                    ->rows(2)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('etiqueta')
                    ->label('Etiqueta')
                    ->weight('medium')
                    ->searchable(),
                TextColumn::make('usuario')
                    ->label('Usuario')
                    ->formatStateUsing(fn ($state): string => filled($state) ? '••••••••' : '—'),
                TextColumn::make('password')
                    ->label('Contraseña')
                    ->state('••••••••'),
                TextColumn::make('updated_at')
                    ->label('Actualizada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
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
                    // El log se registra al montar la accion (una sola vez por apertura);
                    // modalContent puede re-renderizarse y duplicaria el registro.
                    ->mountUsing(function (ActivoDigitalCredencial $record): void {
                        AuditService::log(
                            accion: 'credencial_revelada',
                            entidad: 'ActivoDigitalCredencial',
                            entidadId: $record->id,
                            datosNuevos: [
                                'etiqueta' => $record->etiqueta,
                                'activo' => $record->activoDigital?->nombre,
                                'activo_digital_id' => $record->activo_digital_id,
                                'por' => auth()->user()?->name,
                            ],
                            empresaId: $record->activoDigital?->empresa_id,
                        );
                    })
                    ->modalContent(function (ActivoDigitalCredencial $record): HtmlString {
                        $row = fn (string $label, ?string $value): string => '<div class="py-2 border-b border-gray-100 dark:border-gray-800">'
                            . '<p class="text-xs font-medium text-gray-500">' . e($label) . '</p>'
                            . '<p class="text-sm text-gray-900 dark:text-gray-100 font-mono break-all">' . (filled($value) ? e($value) : '—') . '</p></div>';

                        return new HtmlString(
                            $row('Etiqueta', $record->etiqueta)
                            . $row('Usuario / correo', $record->usuario)
                            . $row('Contraseña', $record->password)
                            . $row('2FA / TOTP seed', $record->dato_2fa)
                            . $row('Códigos de recuperación', $record->recovery)
                            . $row('Notas', $record->notas)
                        );
                    }),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->emptyStateHeading('Sin credenciales registradas')
            ->emptyStateDescription('Las credenciales se guardan cifradas y solo son visibles para usuarios autorizados.');
    }
}
