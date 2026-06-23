<?php

namespace App\Filament\Resources\Riders\Pages;

use App\Filament\Resources\Riders\RiderResource;
use App\Models\Rider;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

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
        $this->validateFullName($data['name']);

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

    protected function validateFullName(string $name): void
    {
        if ($name === '' || preg_match('/^[\pL\s]+$/u', $name) !== 1) {
            throw ValidationException::withMessages([
                'data.first_names' => 'Revisa los nombres del rider. Solo se permiten letras y espacios.',
                'data.last_names' => 'Revisa los apellidos del rider. Solo se permiten letras y espacios.',
            ]);
        }
    }
}
