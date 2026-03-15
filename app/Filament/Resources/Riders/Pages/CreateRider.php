<?php

namespace App\Filament\Resources\Riders\Pages;

use App\Filament\Resources\Riders\RiderResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRider extends CreateRecord
{
    protected static string $resource = RiderResource::class;

    public function getTitle(): string
    {
        return 'Crear rider';
    }
}
