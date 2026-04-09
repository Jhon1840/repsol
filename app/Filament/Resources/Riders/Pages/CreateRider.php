<?php

namespace App\Filament\Resources\Riders\Pages;

use App\Filament\Resources\Riders\RiderResource;
use App\Models\Rider;
use Filament\Resources\Pages\CreateRecord;

class CreateRider extends CreateRecord
{
    protected static string $resource = RiderResource::class;

    public function getTitle(): string
    {
        return 'Crear rider';
    }

    protected function handleRecordCreation(array $data): Rider
    {
        $data['created_by'] = auth()->id();
        $data['creation_source'] = 'manual';

        return Rider::query()->create($data);
    }
}
