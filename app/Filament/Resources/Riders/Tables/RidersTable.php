<?php

namespace App\Filament\Resources\Riders\Tables;

use App\Models\Rider;
use App\Models\RiderMovement;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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
                TextColumn::make('branch')
                    ->label('Sucursal')
                    ->placeholder('Sin sucursal')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('rango')
                    ->label('Rango')
                    ->placeholder('Sin rango')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('points_balance')
                    ->label('Puntos')
                    ->state(fn ($record): int => $record->points_balance)
                    ->alignEnd()
                    ->sortable(query: fn ($query, string $direction) => $query->withPointsBalance(auth()->user())->orderBy('points_balance', $direction)),
                TextColumn::make('updated_at')
                    ->label('Ultima edicion')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('updated_at_sort')
                    ->label('Ordenar por ultima edicion')
                    ->options([
                        'desc' => 'Mas recientes primero',
                        'asc' => 'Mas antiguas primero',
                    ])
                    ->native(false)
                    ->query(function (Builder $query, array $data): Builder {
                        $direction = $data['value'] ?? null;

                        if (! in_array($direction, ['asc', 'desc'], true)) {
                            return $query;
                        }

                        return $query->reorder('updated_at', $direction);
                    }),
                SelectFilter::make('branch')
                    ->label('Sucursal')
                    ->options(fn (): array => self::branchOptions())
                    ->native(false)
                    ->query(function (Builder $query, array $data): Builder {
                        $branch = $data['value'] ?? null;

                        if (blank($branch)) {
                            return $query;
                        }

                        return $query->where('branch', $branch);
                    }),
                SelectFilter::make('rango')
                    ->label('Rango')
                    ->options(Rider::RANGO_OPTIONS)
                    ->native(false),
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

    protected static function branchOptions(): array
    {
        if ($branch = auth()->user()?->branchScope()) {
            return [$branch => $branch];
        }

        return collect([
            ...Rider::query()
                ->whereNotNull('branch')
                ->distinct()
                ->orderBy('branch')
                ->pluck('branch')
                ->all(),
            ...RiderMovement::query()
                ->whereNotNull('branch')
                ->distinct()
                ->orderBy('branch')
                ->pluck('branch')
                ->all(),
        ])
            ->filter()
            ->unique()
            ->sort()
            ->mapWithKeys(fn (string $branch): array => [$branch => $branch])
            ->all();
    }
}
