<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Datos del usuario')
                    ->description('Administra el nombre, rol y credenciales de acceso al panel.')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Administrador Repsol'),
                        Select::make('role')
                            ->label('Rol')
                            ->options([
                                User::ROLE_ADMIN => 'Admin',
                                User::ROLE_MARKETING => 'Marketing',
                                User::ROLE_BRANCH_MANAGER => 'Encargado de sucursal',
                                User::ROLE_ADVISOR => 'Asesor',
                            ])
                            ->required()
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(function ($state, $set, $get): void {
                                if (in_array($state, [User::ROLE_ADMIN, User::ROLE_ADVISOR], true)) {
                                    $set('branch', null);

                                    return;
                                }

                                if ($state === User::ROLE_MARKETING && blank($get('branch'))) {
                                    $set('branch', User::BRANCH_GLOBAL);

                                    return;
                                }

                                if ($state === User::ROLE_BRANCH_MANAGER && $get('branch') === User::BRANCH_GLOBAL) {
                                    $set('branch', null);
                                }
                            })
                            ->default(User::ROLE_MARKETING),
                        Select::make('branch')
                            ->label('Sucursal')
                            ->options(fn ($get): array => $get('role') === User::ROLE_MARKETING
                                ? [User::BRANCH_GLOBAL => 'Global'] + User::BRANCH_OPTIONS
                                : User::BRANCH_OPTIONS)
                            ->required(fn ($get): bool => in_array($get('role'), User::RIDER_BRANCH_SCOPED_ROLES, true))
                            ->visible(fn ($get): bool => in_array($get('role'), User::RIDER_BRANCH_SCOPED_ROLES, true))
                            ->default(User::BRANCH_GLOBAL)
                            ->dehydrated()
                            ->native(false),
                        TextInput::make('email')
                            ->label('Username')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->placeholder('usuario'),
                        TextInput::make('password')
                            ->label('Contraseña')
                            ->password()
                            ->revealable()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->confirmed()
                            ->minLength(8)
                            ->maxLength(255),
                        TextInput::make('password_confirmation')
                            ->label('Confirmar contraseña')
                            ->password()
                            ->revealable()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(false)
                            ->maxLength(255),
                    ])
                    ->columns(2),
            ]);
    }
}
