<?php

namespace App\Filament\Resources\Riders\Schemas;

use App\Models\Rider;
use Filament\Forms\Components\Select;
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
                    ->description('Administra el ID, nombre, rango y saldo de puntos del rider.')
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
                        TextInput::make('branch')
                            ->label('Sucursal')
                            ->maxLength(255)
                            ->placeholder('SANTA CRUZ'),
                        Select::make('rango')
                            ->label('Rango')
                            ->options(Rider::RANGO_OPTIONS)
                            ->required()
                            ->native(false),
                        TextInput::make('points_balance')
                            ->label('Puntos')
                            ->numeric()
                            ->integer()
                            ->default(0)
                            ->minValue(0)
                            ->visible(fn (string $operation): bool => $operation === 'edit')
                            ->helperText('Si cambias este valor, se registrará un ajuste automático de puntos.'),
                    ])
                    ->columns(2),
            ]);
    }
}
