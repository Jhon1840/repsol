<?php

namespace App\Filament\Resources\Riders\Pages;

use App\Filament\Resources\Riders\RiderResource;
use App\Models\Rider;
use App\Models\RiderMovement;
use App\Models\UploadedDocument;
use App\Services\ExcelRiderImportService;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\WithFileUploads;

class ListRiders extends ListRecords
{
    use WithFileUploads;

    protected static string $resource = RiderResource::class;

    public $pendingExcel = null;

    public function cancelExcelSelection(): void
    {
        $this->reset('pendingExcel');
        $this->dispatch('excel-selection-cancelled');
    }

    public function storeExcel(): void
    {
        $this->validate([
            'pendingExcel' => ['required', 'file', 'mimes:xlsx', 'max:20480'],
        ]);

        try {
            $document = app(ExcelRiderImportService::class)->storeAndImport(
                $this->pendingExcel,
                auth()->id(),
                [
                    'source' => 'riders_list_upload',
                    'notes' => 'Carga automatizada de Excel desde la pantalla de riders.',
                    'branch_scope' => auth()->user()?->branchScope(),
                ],
            );
        } catch (ValidationException $exception) {
            Notification::make()
                ->title('No se pudo procesar el Excel')
                ->body(collect($exception->errors())->flatten()->implode(' '))
                ->danger()
                ->send();

            return;
        }

        $this->reset('pendingExcel');

        Notification::make()
            ->title('Excel cargado')
            ->body($this->buildUploadMessage($document))
            ->success()
            ->send();

        $this->dispatch('excel-uploaded');
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Crear rider'),
        ];
    }

    public function getHeader(): ?View
    {
        return view('filament.resources.riders.pages.list-riders-header', [
            'totalRiders' => Rider::query()->visibleTo(auth()->user())->count(),
            'totalPoints' => (int) $this->movementQuery()->sum('points'),
            'recentDocuments' => $this->documentQuery()
                ->latest('uploaded_at')
                ->limit(5)
                ->get(),
        ]);
    }

    protected function movementQuery()
    {
        $query = RiderMovement::query();
        $branch = auth()->user()?->branchScope();

        return $branch === null
            ? $query
            : $query->whereHas('rider', fn ($query) => $query->where('branch', $branch));
    }

    protected function documentQuery()
    {
        $query = UploadedDocument::query();
        $branch = auth()->user()?->branchScope();

        if ($branch === null) {
            return $query;
        }

        return $query->whereHas('rider', fn ($query) => $query->where('branch', $branch));
    }

    protected function buildUploadMessage(UploadedDocument $document): string
    {
        $name = $document->original_name;
        $points = (int) ($document->metadata['parsed_points'] ?? 0);
        $processedRiders = count($document->metadata['processed_items'] ?? []);
        $skippedRows = count($document->metadata['skipped_items'] ?? []);

        if ($processedRiders === 0) {
            return "El archivo {$name} quedó registrado, pero no se encontraron riders validos para sumar puntos.";
        }

        if ($skippedRows > 0) {
            return "El archivo {$name} procesó {$processedRiders} movimiento(s) por rider/sucursal, omitió {$skippedRows} fila(s) y sumó {$points} punto(s).";
        }

        return "El archivo {$name} procesó {$processedRiders} movimiento(s) por rider/sucursal y sumó {$points} punto(s).";
    }
}
