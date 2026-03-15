<?php

namespace App\Filament\Resources\Riders\Pages;

use App\Filament\Resources\Riders\RiderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRiders extends ListRecords
{
    protected static string $resource = RiderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Crear rider'),
        ];
    }
}
