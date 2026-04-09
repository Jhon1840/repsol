<?php

namespace App\Filament\Resources\Articulos;

use App\Filament\Resources\Articulos\Pages\CreateArticulos;
use App\Filament\Resources\Articulos\Pages\EditArticulos;
use App\Filament\Resources\Articulos\Pages\ListArticulos;
use App\Filament\Resources\Articulos\Pages\ViewArticulos;
use App\Filament\Resources\Articulos\Schemas\ArticulosForm;
use App\Filament\Resources\Articulos\Schemas\ArticulosInfolist;
use App\Filament\Resources\Articulos\Tables\ArticulosTable;
use App\Models\Articulos;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ArticulosResource extends Resource
{
    protected static ?string $model = Articulos::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'nombre';

    public static function form(Schema $schema): Schema
    {
        return ArticulosForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ArticulosInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ArticulosTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListArticulos::route('/'),
            'create' => CreateArticulos::route('/create'),
            'view' => ViewArticulos::route('/{record}'),
            'edit' => EditArticulos::route('/{record}/edit'),
        ];
    }
}
