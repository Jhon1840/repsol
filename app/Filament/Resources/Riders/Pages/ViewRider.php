<?php

namespace App\Filament\Resources\Riders\Pages;

use App\Filament\Resources\Riders\RiderResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewRider extends ViewRecord
{
    protected static string $resource = RiderResource::class;

    protected string $view = 'filament.resources.riders.pages.view-rider';

    public function mount(int | string $record): void
    {
        parent::mount($record);

        $this->record->load([
            'movements' => fn ($query) => $query->latest('occurred_at'),
            'documents' => fn ($query) => $query->latest('uploaded_at'),
        ])->loadSum('movements as points_balance', 'points');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Volver')
                ->url(RiderResource::getUrl('index'))
                ->color('gray'),
            EditAction::make()->label('Editar rider'),
        ];
    }
}
