<?php

namespace App\Filament\Resources\Articulos\Pages;

use App\Filament\Resources\Articulos\ArticulosResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListArticulos extends ListRecords
{
    protected static string $resource = ArticulosResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->visible(fn (): bool => ArticulosResource::canCreate()),
        ];
    }
}
