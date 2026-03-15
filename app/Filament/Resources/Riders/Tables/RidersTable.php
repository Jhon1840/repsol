<?php

namespace App\Filament\Resources\Riders\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RidersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('rider_id')
                    ->label('ID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                TextColumn::make('points_balance')
                    ->label('Puntos')
                    ->state(fn ($record): int => $record->points_balance)
                    ->alignEnd()
                    ->sortable(query: fn ($query, string $direction) => $query->withPointsBalance()->orderBy('points_balance', $direction)),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Ver detalle'),
                EditAction::make()
                    ->label('Editar'),
            ])
            ->defaultSort('name')
            ->paginated([10, 25, 50]);
    }
}
