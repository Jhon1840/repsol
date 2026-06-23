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
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

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

    public static function canViewAny(): bool
    {
        return auth()->check() && ! static::isAdvisor();
    }

    public static function canView(Model $record): bool
    {
        return auth()->check() && ! static::isAdvisor();
    }

    public static function canCreate(): bool
    {
        return ! static::isBranchManager() && ! static::isAdvisor();
    }

    public static function canEdit(Model $record): bool
    {
        return ! static::isBranchManager() && ! static::isAdvisor();
    }

    public static function canDelete(Model $record): bool
    {
        return ! static::isBranchManager() && ! static::isAdvisor();
    }

    public static function canDeleteAny(): bool
    {
        return ! static::isBranchManager() && ! static::isAdvisor();
    }

    protected static function isBranchManager(): bool
    {
        return auth()->user()?->role === User::ROLE_BRANCH_MANAGER;
    }

    protected static function isAdvisor(): bool
    {
        return auth()->user()?->isAdvisor() === true;
    }
}
