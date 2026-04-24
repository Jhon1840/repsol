<?php

namespace App\Filament\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class Login extends BaseLogin
{
    public function getTitle(): string | Htmlable
    {
        return 'Acceso administrativo';
    }

    public function getHeading(): string | Htmlable | null
    {
        return 'Administra riders y puntos';
    }

    public function getSubheading(): string | Htmlable | null
    {
        return new HtmlString('Panel interno para gestionar los riders y sus puntos acumulados. <br>Utiliza el username administrativo para iniciar sesión.');
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('Username administrativo')
            ->placeholder('usuario')
            ->required()
            ->autocomplete('username')
            ->autofocus()
            ->type('text');
    }

    protected function getPasswordFormComponent(): Component
    {
        /** @var TextInput $component */
        $component = parent::getPasswordFormComponent();

        return $component
            ->label('Contraseña')
            ->placeholder('••••••••');
    }
}
