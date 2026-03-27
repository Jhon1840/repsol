<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Datos del producto')
                    ->description('Administra el codigo del producto y su equivalencia en litros para la API de compras.')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('SMARTER SPORT 4T 20W-50 12X1L'),
                        TextInput::make('code')
                            ->label('Codigo')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->placeholder('RPP2065THC'),
                        TextInput::make('liters')
                            ->label('Equivalencia en litros')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->step('0.01')
                            ->helperText('La API usa este valor para calcular los puntos del movimiento.'),
                    ])
                    ->columns(2),
            ]);
    }
}
