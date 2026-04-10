<?php

namespace App\Filament\Pages;

use App\Models\Articulos;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class Descuento extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTicket;

    protected static ?string $navigationLabel = 'Descuento';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.pages.descuento';

    public function getTitle(): string
    {
        return 'Descuento';
    }

    protected function getViewData(): array
    {
        return [
            'articulos' => Articulos::query()
                ->orderBy('nombre')
                ->get(['id', 'nombre']),
            'redirectTo' => static::getUrl(),
        ];
    }
}
