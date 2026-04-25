<?php

namespace App\Filament\Resources\ImportErrors;

use App\Filament\Resources\ImportErrors\Pages\ListImportErrors;
use App\Filament\Resources\ImportErrors\Pages\ViewImportError;
use App\Filament\Resources\ImportErrors\Tables\ImportErrorsTable;
use App\Models\UploadedDocument;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class ImportErrorResource extends Resource
{
    protected static ?string $model = UploadedDocument::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedExclamationTriangle;

    protected static ?string $modelLabel = 'Error de Excel';

    protected static ?string $pluralModelLabel = 'Errores de Excel';

    protected static ?string $navigationLabel = 'Errores de Excel';

    protected static UnitEnum|string|null $navigationGroup = 'Importaciones';

    protected static ?int $navigationSort = 5;

    public static function table(Table $table): Table
    {
        return ImportErrorsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListImportErrors::route('/'),
            'view' => ViewImportError::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withImportErrors()
            ->visibleTo(auth()->user())
            ->with(['uploader', 'rider']);
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function canView(Model $record): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }
}
