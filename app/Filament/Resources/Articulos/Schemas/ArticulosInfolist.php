<?php

namespace App\Filament\Resources\Articulos\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ArticulosInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Resumen')
                    ->schema([
                        TextEntry::make('nombre')
                            ->label('Nombre'),
                        TextEntry::make('descripcion')
                            ->label('Descripcion')
                            ->placeholder('Sin descripcion')
                            ->columnSpanFull(),
                        TextEntry::make('created_at')
                            ->label('Creado')
                            ->since(),
                        TextEntry::make('updated_at')
                            ->label('Actualizado')
                            ->since(),
                    ])
                    ->columns(2),
            ]);
    }
}
