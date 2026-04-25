<?php

namespace App\Filament\Resources\ImportErrors\Pages;

use App\Filament\Resources\ImportErrors\ImportErrorResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\URL;

class ViewImportError extends ViewRecord
{
    protected static string $resource = ImportErrorResource::class;

    protected string $view = 'filament.resources.import-errors.pages.view-import-error';

    public function mount(int | string $record): void
    {
        parent::mount($record);

        $this->record->load(['uploader', 'rider']);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('download')
                ->label('Descargar Excel')
                ->url(fn (): string => URL::signedRoute('documents.download', ['document' => $this->record])),
            Action::make('back')
                ->label('Volver')
                ->url(ImportErrorResource::getUrl('index'))
                ->color('gray'),
        ];
    }
}
