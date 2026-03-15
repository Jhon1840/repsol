<?php

namespace App\Filament\Resources\Riders\Pages;

use App\Filament\Resources\Riders\RiderResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditRider extends EditRecord
{
    protected static string $resource = RiderResource::class;

    public function getTitle(): string
    {
        return 'Editar rider';
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make()->label('Eliminar'),
        ];
    }
}
