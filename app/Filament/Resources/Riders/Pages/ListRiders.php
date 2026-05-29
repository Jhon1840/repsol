<?php

namespace App\Filament\Resources\Riders\Pages;

use App\Filament\Resources\Riders\RiderResource;
use App\Models\Rider;
use App\Models\RiderMovement;
use App\Models\UploadedDocument;
use App\Models\User;
use App\Services\ExcelRiderImportService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
use Livewire\WithFileUploads;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListRiders extends ListRecords
{
    use WithFileUploads;

    protected static string $resource = RiderResource::class;

    public $pendingExcel = null;

    public ?array $excelImportPreview = null;

    public function cancelExcelSelection(): void
    {
        $this->reset('pendingExcel', 'excelImportPreview');
        $this->dispatch('excel-selection-cancelled');
    }

    public function storeExcel(bool $confirmed = false): void
    {
        if (! $this->canUploadExcel()) {
            Notification::make()
                ->title('No tienes permisos para subir Excel')
                ->danger()
                ->send();

            return;
        }

        $this->validate([
            'pendingExcel' => ['required', 'file', 'mimes:xlsx', 'max:20480'],
        ]);

        $metadata = [
            'source' => 'riders_list_upload',
            'notes' => 'Carga automatizada de Excel desde la pantalla de riders.',
            'branch_scope' => auth()->user()?->branchScope(),
        ];

        try {
            if (! $confirmed) {
                $this->excelImportPreview = app(ExcelRiderImportService::class)->previewImport(
                    $this->pendingExcel,
                    null,
                    $metadata,
                );

                if ($this->excelImportPreview['has_new_records'] ?? false) {
                    return;
                }
            }

            $document = app(ExcelRiderImportService::class)->storeAndImport(
                $this->pendingExcel,
                auth()->id(),
                $metadata,
            );
        } catch (ValidationException $exception) {
            Notification::make()
                ->title('No se pudo procesar el Excel')
                ->body(collect($exception->errors())->flatten()->implode(' '))
                ->danger()
                ->send();

            return;
        }

        $this->reset('pendingExcel', 'excelImportPreview');

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
            Action::make('exportRiders')
                ->label('Exportar riders')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->visible(fn (): bool => $this->canExportRiders())
                ->action(fn (): StreamedResponse => $this->exportRiders()),
            CreateAction::make()->label('Crear rider'),
        ];
    }

    public function exportRiders(): StreamedResponse
    {
        abort_unless($this->canExportRiders(), 403);

        $filename = 'riders-'.now()->format('Y-m-d-His').'.xlsx';

        return response()->streamDownload(function (): void {
            $writer = new Writer;
            $writer->openToFile('php://output');

            $writer->addRow(Row::fromValues([
                'ID',
                'Nombre',
                'Sucursal',
                'Rango',
                'Puntos',
                'Origen',
                'Creado por',
                'Editado por',
                'Fecha de creacion',
                'Ultima edicion',
            ]));

            $this->exportRidersQuery()
                ->chunk(500, function ($riders) use ($writer): void {
                    foreach ($riders as $rider) {
                        $writer->addRow(Row::fromValues([
                            $rider->rider_id,
                            $rider->name,
                            $rider->branch,
                            $rider->rango,
                            (int) $rider->points_balance,
                            $rider->creationSourceLabel(),
                            $rider->creator?->email ?? $rider->creator?->name ?? 'Sistema',
                            $rider->editor?->email ?? $rider->editor?->name,
                            $rider->created_at?->format('d/m/Y H:i'),
                            $rider->updated_at?->format('d/m/Y H:i'),
                        ]));
                    }
                });

            $writer->close();
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    protected function exportRidersQuery(): Builder
    {
        return Rider::query()
            ->visibleTo(auth()->user())
            ->with(['creator', 'editor'])
            ->withPointsBalance(auth()->user())
            ->orderBy('updated_at', 'desc');
    }

    protected function canExportRiders(): bool
    {
        $user = auth()->user();

        return $user?->isAdmin() === true || $user?->role === User::ROLE_MARKETING;
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
            'canUploadExcel' => $this->canUploadExcel(),
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

    protected function canUploadExcel(): bool
    {
        return auth()->user()?->isAdmin() === true;
    }
}
