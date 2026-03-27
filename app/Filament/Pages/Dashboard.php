<?php

namespace App\Filament\Pages;

use App\Models\Rider;
use App\Models\RiderMovement;
use App\Models\UploadedDocument;
use App\Services\ExcelRiderImportService;
use Illuminate\Validation\ValidationException;
use Filament\Notifications\Notification;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Support\Icons\Heroicon;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Dashboard extends BaseDashboard
{
    use WithFileUploads;
    use WithPagination;

    protected static string|null|\BackedEnum $navigationIcon = Heroicon::OutlinedHome;

    protected static ?string $navigationLabel = 'Dashboard';

    protected string $view = 'filament.pages.dashboard';

    public $pendingExcel = null;

    public function getTitle(): string
    {
        return 'Dashboard';
    }

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
                    'source' => 'dashboard_upload',
                    'notes' => 'Carga automatizada de Excel desde dashboard.',
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

    protected function getViewData(): array
    {
        return [
            'totalRiders' => Rider::query()->count(),
            'totalPoints' => (int) RiderMovement::query()->sum('points'),
            'riders' => Rider::query()
                ->withPointsBalance()
                ->orderBy('name')
                ->paginate(10),
            'recentDocuments' => UploadedDocument::query()
                ->latest('uploaded_at')
                ->limit(5)
                ->get(),
        ];
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
            return "El archivo {$name} procesó {$processedRiders} rider(es), omitió {$skippedRows} fila(s) y sumó {$points} punto(s).";
        }

        return "El archivo {$name} procesó {$processedRiders} rider(es) y sumó {$points} punto(s).";
    }
}
