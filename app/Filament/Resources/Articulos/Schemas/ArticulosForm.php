<?php

namespace App\Filament\Resources\Articulos\Schemas;

use App\Models\Rider;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ArticulosForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Datos del articulo')
                    ->description('Administra el nombre, la descripcion y las fotos visibles para el rider.')
                    ->schema([
                        TextInput::make('nombre')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Gorra'),
                        Textarea::make('descripcion')
                            ->label('Descripcion')
                            ->rows(4)
                            ->maxLength(65535)
                            ->placeholder('Descripcion opcional del articulo')
                            ->columnSpanFull(),
                        FileUpload::make('imagenes')
                            ->label('Imagenes del articulo')
                            ->image()
                            ->multiple()
                            ->maxFiles(6)
                            ->maxSize(5120)
                            ->disk('public')
                            ->directory('articulos')
                            ->visibility('public')
                            ->panelLayout('grid')
                            ->reorderable()
                            ->appendFiles()
                            ->imagePreviewHeight('180')
                            ->helperText('Sube hasta 6 imagenes. La primera se mostrara como foto principal en premios disponibles.')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Puntos por rango')
                    ->description('Define cuántos puntos cuesta este artículo para cada rango de rider.')
                    ->schema(
                        collect(Rider::RANGO_OPTIONS)
                            ->map(fn (string $label, string $rango): TextInput => TextInput::make("point_costs.{$rango}")
                                ->label($label)
                                ->required()
                                ->numeric()
                                ->integer()
                                ->minValue(0)
                                ->placeholder('0'))
                            ->values()
                            ->all()
                    )
                    ->columns(2),
            ]);
    }
}
