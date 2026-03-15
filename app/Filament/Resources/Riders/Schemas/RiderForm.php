<?php

namespace App\Filament\Resources\Riders\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RiderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Datos del rider')
                    ->description('Solo se administran el ID y el nombre del rider.')
                    ->schema([
                        TextInput::make('rider_id')
                            ->label('ID')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->placeholder('SC00065'),
                        TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('SANDRA PARADA CABALLERO'),
                    ])
                    ->columns(2),
            ]);
    }
}
