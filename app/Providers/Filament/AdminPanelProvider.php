<?php

namespace App\Providers\Filament;

use App\Filament\Auth\Login;
use Filament\Enums\ThemeMode;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationItem;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(Login::class)
            ->brandName('LATCO Riders')
            ->brandLogo(asset('assets/REPSOL LUBRICANTS HORIZONTAL BLANCO.png'))
            ->brandLogoHeight('2.25rem')
            ->globalSearch(false)
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->darkMode(true, isForced: true)
            ->defaultThemeMode(ThemeMode::Dark)
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn (): string => view('partials.session-refresh-script')->render(),
            )
            ->navigationItems([
                NavigationItem::make('Consulta de puntos')
                    ->icon(Heroicon::OutlinedMagnifyingGlass)
                    ->url('/consulta-puntos/', shouldOpenInNewTab: true)
                    ->sort(2),
                NavigationItem::make('Descuento')
                    ->icon(Heroicon::OutlinedTicket)
                    ->url('/descuento', shouldOpenInNewTab: true)
                    ->sort(3),
            ])
            ->colors([
                'primary' => Color::hex('#3F5C79'),
                'info' => Color::hex('#E39B63'),
                'success' => Color::hex('#F0D98A'),
                'danger' => Color::hex('#C12A3A'),
                'gray' => Color::Slate,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
