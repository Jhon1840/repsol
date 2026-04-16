<?php

namespace App\Filament\Resources\Articulos\Pages;

use App\Filament\Resources\Articulos\ArticulosResource;
use App\Filament\Resources\Articulos\Pages\Concerns\ManagesArticuloPointCosts;
use Filament\Resources\Pages\CreateRecord;

class CreateArticulos extends CreateRecord
{
    use ManagesArticuloPointCosts;

    protected static string $resource = ArticulosResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->extractPointCosts($data);

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->savePointCosts();
    }
}
