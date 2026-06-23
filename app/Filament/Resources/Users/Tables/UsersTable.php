<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                TextColumn::make('role')
                    ->label('Rol')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        User::ROLE_ADMIN => 'Admin',
                        User::ROLE_MARKETING => 'Marketing',
                        User::ROLE_BRANCH_MANAGER => 'Encargado de sucursal',
                        User::ROLE_ADVISOR => 'Asesor',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        User::ROLE_ADMIN => 'danger',
                        User::ROLE_MARKETING => 'info',
                        User::ROLE_BRANCH_MANAGER => 'success',
                        User::ROLE_ADVISOR => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('branch')
                    ->label('Sucursal')
                    ->placeholder('Global')
                    ->formatStateUsing(fn (?string $state): string => $state === User::BRANCH_GLOBAL ? 'Global' : (string) $state)
                    ->badge()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Username')
                    ->searchable()
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
                EditAction::make()->label('Editar'),
            ])
            ->defaultSort('name')
            ->paginated([10, 25, 50]);
    }
}
