<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        $isSuperAdmin = auth()->user()?->hasRole('super_admin');

        return $schema
            ->components([
                Section::make('Datos del usuario')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre completo')
                            ->required()
                            ->maxLength(150),
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(150),
                        TextInput::make('password')
                            ->label('Contraseña')
                            ->password()
                            ->revealable()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->minLength(8)
                            ->default(fn (string $operation): ?string => $operation === 'create' ? Str::random(12) : null)
                            ->helperText(fn (string $operation): ?string => $operation === 'create' ? 'Se genera una contraseña temporal.' : 'Dejar vacío para mantener la actual.'),
                        Select::make('empresa_id')
                            ->label('Empresa')
                            ->relationship('empresa', 'razon_social')
                            ->searchable()
                            ->preload()
                            ->visible($isSuperAdmin)
                            ->default(fn () => auth()->user()?->empresa_id),
                    ]),
                Section::make('Rol y estado')
                    ->columns(2)
                    ->schema([
                        Select::make('rol')
                            ->label('Rol')
                            ->options(function () use ($isSuperAdmin) {
                                $roles = [
                                    'admin_empresa' => 'Admin Empresa',
                                    'usuario' => 'Usuario',
                                    'solo_lectura' => 'Solo Lectura',
                                ];
                                if ($isSuperAdmin) {
                                    $roles = [
                                        'super_admin' => 'Super Admin',
                                        'agente_helpdesk' => 'Agente Helpdesk',
                                    ] + $roles;
                                }
                                return $roles;
                            })
                            ->required()
                            ->default('usuario'),
                        Select::make('estado')
                            ->label('Estado')
                            ->options([
                                'activo' => 'Activo',
                                'inactivo' => 'Inactivo',
                            ])
                            ->default('activo')
                            ->required(),
                    ]),
                Section::make('Notificaciones')
                    ->columns(2)
                    ->schema([
                        Toggle::make('notif_tickets')
                            ->label('Notificaciones de tickets')
                            ->default(true),
                        Toggle::make('notif_incidencias')
                            ->label('Notificaciones de incidencias')
                            ->default(true),
                    ]),
            ]);
    }
}
