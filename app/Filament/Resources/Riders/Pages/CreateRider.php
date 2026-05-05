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

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['name'] = $this->buildFullName($data);

        unset($data['first_names'], $data['last_names']);

        return $data;
    }

    protected function handleRecordCreation(array $data): Rider
    {
        if ($branch = auth()->user()?->branchScope()) {
            $data['branch'] = $branch;
        }

        $data['created_by'] = auth()->id();
        $data['creation_source'] = 'manual';

        return Rider::query()->create($data);
    }

    protected function buildFullName(array $data): string
    {
        if (! array_key_exists('first_names', $data) && ! array_key_exists('last_names', $data)) {
            return trim((string) ($data['name'] ?? ''));
        }

        return trim(collect([
            $data['first_names'] ?? null,
            $data['last_names'] ?? null,
        ])
            ->filter(fn (mixed $value): bool => filled($value))
            ->implode(' '));
    }
}
