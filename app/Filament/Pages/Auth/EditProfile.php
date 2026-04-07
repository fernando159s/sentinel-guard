<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EditProfile extends BaseEditProfile
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Datos personales')
                    ->icon('heroicon-o-user')
                    ->columns(2)
                    ->schema([
                        $this->getNameFormComponent(),
                        $this->getEmailFormComponent(),
                        TextInput::make('dni')
                            ->label('DNI')
                            ->maxLength(20),
                        TextInput::make('telefono')
                            ->label('Telefono')
                            ->tel()
                            ->maxLength(30),
                        TextInput::make('direccion')
                            ->label('Direccion')
                            ->maxLength(500)
                            ->columnSpanFull(),
                        TextInput::make('puesto')
                            ->label('Puesto en la empresa')
                            ->maxLength(150),
                    ]),
                Section::make('Cambiar contrasena')
                    ->icon('heroicon-o-lock-closed')
                    ->schema([
                        $this->getPasswordFormComponent(),
                        $this->getPasswordConfirmationFormComponent(),
                        $this->getCurrentPasswordFormComponent(),
                    ])
                    ->collapsible()
                    ->collapsed(),
                Section::make('Preferencias de notificacion')
                    ->icon('heroicon-o-bell')
                    ->description('Elige que notificaciones quieres recibir por email.')
                    ->schema([
                        Toggle::make('notif_tickets')
                            ->label('Notificaciones de tickets')
                            ->helperText('Recibir emails cuando te asignan un ticket, responden o reabren.'),
                        Toggle::make('notif_incidencias')
                            ->label('Notificaciones de incidencias')
                            ->helperText('Recibir emails cuando se registra una nueva incidencia de seguridad (F09).'),
                    ]),
                Section::make('Mi firma')
                    ->icon('heroicon-o-pencil')
                    ->description('Sube una imagen de tu firma para usarla al firmar documentos. Se pre-llenara automaticamente.')
                    ->schema([
                        FileUpload::make('firma_guardada_file')
                            ->label('Imagen de firma')
                            ->image()
                            ->disk('public')
                            ->directory('firmas')
                            ->maxSize(1024)
                            ->helperText('PNG o JPG, max 1MB. Fondo transparente recomendado.'),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}
