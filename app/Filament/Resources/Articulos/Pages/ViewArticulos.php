<?php

namespace App\Filament\Resources\Articulos\Pages;

use App\Filament\Resources\Articulos\ArticulosResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewArticulos extends ViewRecord
{
    protected static string $resource = ArticulosResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->visible(fn (): bool => ArticulosResource::canEdit($this->getRecord())),
        ];
    }
}
