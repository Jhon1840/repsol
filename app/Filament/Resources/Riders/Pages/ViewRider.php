<?php

namespace App\Filament\Resources\Riders\Pages;

use App\Filament\Resources\Riders\RiderResource;
use Illuminate\Support\Collection;
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
            'creator',
            'editor',
            'movements' => fn ($query) => $query
                ->with(['actor', 'document.uploader'])
                ->latest('occurred_at'),
            'documents' => fn ($query) => $query->with('uploader')->latest('uploaded_at'),
        ]);

        $this->record->loadSum('movements as points_balance', 'points');
    }

    public function getRelatedDocuments(): Collection
    {
        return $this->record->documents
            ->merge($this->record->movements->pluck('document')->filter())
            ->unique('id')
            ->sortByDesc('uploaded_at')
            ->values();
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
