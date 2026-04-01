<?php

namespace App\Filament\Resources\EmailTemplates\Schemas;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EmailTemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información de la plantilla')
                    ->columns(2)
                    ->schema([
                        TextInput::make('nombre')
                            ->label('Nombre de la plantilla')
                            ->disabled()
                            ->columnSpanFull(),
                        TextInput::make('slug')
                            ->label('Identificador')
                            ->disabled(),
                        Toggle::make('activo')
                            ->label('Activa')
                            ->helperText('Si se desactiva, se usará la plantilla por defecto'),
                    ]),
                Section::make('Contenido del email')
                    ->description('Usa las variables disponibles entre llaves dobles, ej: {{nombre}}')
                    ->schema([
                        TagsInput::make('variables_disponibles')
                            ->label('Variables disponibles')
                            ->disabled()
                            ->helperText('Estas variables se reemplazan automáticamente al enviar el email'),
                        TextInput::make('asunto')
                            ->label('Asunto del email')
                            ->required()
                            ->maxLength(300)
                            ->helperText('Puedes usar variables como {{nombre}}, {{empresa}}, etc.'),
                        RichEditor::make('contenido')
                            ->label('Cuerpo del email')
                            ->required()
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'strike',
                                'link',
                                'orderedList',
                                'bulletList',
                                'h2',
                                'h3',
                                'blockquote',
                                'undo',
                                'redo',
                            ])
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
