<?php

namespace App\Filament\Resources\Products\Tables;

use App\Filament\Resources\Products\ProductResource;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Codigo')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                TextColumn::make('oil_type')
                    ->label('Tipo')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('points_per_box')
                    ->label('Pts caja')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('points_per_liter')
                    ->label('Pts litro')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Editar')
                    ->visible(fn ($record): bool => ProductResource::canEdit($record)),
            ])
            ->defaultSort('name')
            ->paginated([10, 25, 50]);
    }
}
