<?php

namespace App\Filament\Resources\Articulos\Schemas;

use App\Models\Rider;
use Filament\Infolists\Components\ImageEntry;
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
                        ImageEntry::make('imagenes')
                            ->label('Imagen')
                            ->disk('public')
                            ->imageHeight(120)
                            ->placeholder('Sin imagen')
                            ->columnSpanFull(),
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
                Section::make('Puntos por rango')
                    ->schema(
                        collect(Rider::RANGO_OPTIONS)
                            ->map(fn (string $label, string $rango): TextEntry => TextEntry::make("point_cost_{$rango}")
                                ->label($label)
                                ->state(fn ($record): int => (int) ($record->pointCosts
                                    ->firstWhere('rango', $rango)
                                    ?->points ?? 0)))
                            ->values()
                            ->all()
                    )
                    ->columns(2),
            ]);
    }
}
