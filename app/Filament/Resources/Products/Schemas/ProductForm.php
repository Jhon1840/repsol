<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\Select;
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
                    ->description('Administra el codigo del producto y sus puntos por caja y por litro.')
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
                        Select::make('oil_type')
                            ->label('Tipo de aceite')
                            ->required()
                            ->options([
                                'RIDER' => 'RIDER',
                                'SMARTER SPORT' => 'SMARTER SPORT',
                                'SMARTER SYNTHETIC' => 'SMARTER SYNTHETIC',
                                'RACING' => 'RACING',
                                'QUALIFIER' => 'QUALIFIER',
                            ])
                            ->native(false),
                        TextInput::make('liters')
                            ->label('Equivalencia en litros')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->step('0.01')
                            ->helperText('Litros que representa una caja o unidad de venta.'),
                        TextInput::make('points_per_box')
                            ->label('Puntos por caja')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->step('0.01'),
                        TextInput::make('points_per_liter')
                            ->label('Puntos por litro')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->step('0.01')
                            ->helperText('El Excel se calcula como Litros comprados x Puntos por litro.'),
                    ])
                    ->columns(2),
            ]);
    }
}
