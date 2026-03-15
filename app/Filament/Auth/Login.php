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
        return new HtmlString('Panel interno en Laravel 12 + Filament 4 para control de riders, puntos y documentos PDF.');
    }

    protected function getEmailFormComponent(): Component
    {
        return parent::getEmailFormComponent()
            ->label('Correo administrativo')
            ->placeholder('admin@repsol-filament.test');
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
