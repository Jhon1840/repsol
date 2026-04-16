<?php

namespace App\Filament\Resources\Articulos\Pages;

use App\Filament\Resources\Articulos\ArticulosResource;
use App\Filament\Resources\Articulos\Pages\Concerns\ManagesArticuloPointCosts;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditArticulos extends EditRecord
{
    use ManagesArticuloPointCosts;

    protected static string $resource = ArticulosResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return $this->fillPointCosts($data);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->extractPointCosts($data);

        return $data;
    }

    protected function afterSave(): void
    {
        $this->savePointCosts();
    }
}
