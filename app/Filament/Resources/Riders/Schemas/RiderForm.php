<?php

namespace App\Filament\Resources\Riders\Schemas;

use App\Models\Rider;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class RiderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Datos del rider')
                    ->description('Administra el ID, nombre, rango y saldo de puntos del rider.')
                    ->schema([
                        TextInput::make('rider_id')
                            ->label('ID')
                            ->required()
                            ->prefix(Rider::RIDER_ID_PREFIX)
                            ->formatStateUsing(fn (mixed $state): ?string => Rider::riderIdSuffix($state))
                            ->afterStateUpdated(function (Set $set, mixed $state): void {
                                $set('rider_id', Rider::riderIdSuffix($state));
                            })
                            ->dehydrateStateUsing(fn (mixed $state): ?string => Rider::normalizeRiderId($state))
                            ->mutateStateForValidationUsing(fn (mixed $state): ?string => Rider::normalizeRiderId($state))
                            ->rules([
                                fn (): \Closure => fn (string $attribute, mixed $value, \Closure $fail) => Rider::normalizeRiderId($value) === Rider::RIDER_ID_PREFIX
                                    ? $fail('El ID debe incluir números o letras después de PYA.')
                                    : null,
                            ])
                            ->unique(ignoreRecord: true)
                            ->live(onBlur: true)
                            ->maxLength(255)
                            ->placeholder('12647'),
                        TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('SANDRA PARADA CABALLERO'),
                        Select::make('branch')
                            ->label('Sucursal')
                            ->options(User::BRANCH_OPTIONS)
                            ->default(fn (): ?string => auth()->user()?->branchScope())
                            ->disabled(fn (): bool => filled(auth()->user()?->branchScope()))
                            ->dehydrated()
                            ->native(false),
                        Select::make('rango')
                            ->label('Rango')
                            ->options(Rider::RANGO_OPTIONS)
                            ->required()
                            ->native(false),
                        TextInput::make('points_balance')
                            ->label('Puntos')
                            ->numeric()
                            ->integer()
                            ->default(0)
                            ->minValue(0)
                            ->visible(fn (string $operation): bool => $operation === 'edit')
                            ->helperText('Si cambias este valor, se registrará un ajuste automático de puntos.'),
                    ])
                    ->columns(2),
            ]);
    }
}
