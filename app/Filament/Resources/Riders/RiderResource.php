<?php

namespace App\Filament\Resources\Riders;

use App\Filament\Resources\Riders\Pages\CreateRider;
use App\Filament\Resources\Riders\Pages\EditRider;
use App\Filament\Resources\Riders\Pages\ListRiders;
use App\Filament\Resources\Riders\Pages\ViewRider;
use App\Filament\Resources\Riders\Schemas\RiderForm;
use App\Filament\Resources\Riders\Schemas\RiderInfolist;
use App\Filament\Resources\Riders\Tables\RidersTable;
use App\Models\Rider;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RiderResource extends Resource
{
    protected static ?string $model = Rider::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $modelLabel = 'Rider';

    protected static ?string $pluralModelLabel = 'Riders';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return RiderForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return RiderInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RidersTable::configure($table);
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
            'index' => ListRiders::route('/'),
            'create' => CreateRider::route('/create'),
            'view' => ViewRider::route('/{record}'),
            'edit' => EditRider::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withPointsBalance();
    }
}
