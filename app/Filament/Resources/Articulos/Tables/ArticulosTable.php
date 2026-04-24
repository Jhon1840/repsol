<?php

namespace App\Filament\Resources\Articulos\Tables;

use App\Filament\Resources\Articulos\ArticulosResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ArticulosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('imagenes')
                    ->label('Foto')
                    ->disk('public')
                    ->state(fn ($record): ?string => $record->primaryImagePath())
                    ->imageSize(54)
                    ->square(),
                TextColumn::make('nombre')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('descripcion')
                    ->label('Descripcion')
                    ->limit(60)
                    ->placeholder('Sin descripcion')
                    ->wrap(),
                TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make()->label('Ver'),
                EditAction::make()
                    ->label('Editar')
                    ->visible(fn ($record): bool => ArticulosResource::canEdit($record)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Eliminar seleccionados')
                        ->visible(fn (): bool => ArticulosResource::canDeleteAny()),
                ]),
            ])
            ->defaultSort('nombre')
            ->paginated([10, 25, 50]);
    }
}
