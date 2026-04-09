<?php

namespace App\Filament\Resources\Riders\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RiderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Resumen')
                    ->schema([
                        TextEntry::make('rider_id')
                            ->label('ID'),
                        TextEntry::make('name')
                            ->label('Nombre'),
                        TextEntry::make('branch')
                            ->label('Sucursal')
                            ->placeholder('Sin sucursal'),
                        TextEntry::make('rango')
                            ->label('Rango')
                            ->placeholder('Sin rango'),
                        TextEntry::make('points_balance')
                            ->label('Puntos')
                            ->state(fn ($record): int => $record->points_balance),
                    ])
                    ->columns(4),
            ]);
    }
}
