<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    public function getTitle(): string
    {
        return 'Crear usuario';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (! in_array($data['role'] ?? null, User::RIDER_BRANCH_SCOPED_ROLES, true)) {
            $data['branch'] = null;
        }

        return $data;
    }
}
