<?php

namespace App\Filament\Resources\Riders\Pages;

use App\Filament\Resources\Riders\RiderResource;
use App\Models\RiderMovement;
use App\Services\ExcelRiderImportService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class EditRider extends EditRecord
{
    protected static string $resource = RiderResource::class;

    protected ?int $targetPointsBalance = null;

    public ?array $excelImportPreview = null;

    public bool $excelImportConfirmed = false;

    public function getTitle(): string
    {
        return 'Editar rider';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('uploadExcel')
                ->label('Subir Excel')
                ->icon('heroicon-o-document-arrow-up')
                ->visible(fn (): bool => auth()->user()?->isAdmin() === true)
                ->beforeFormFilled(function (): void {
                    $this->excelImportPreview = null;
                    $this->excelImportConfirmed = false;
                })
                ->schema([
                    FileUpload::make('excel')
                        ->label('Archivo Excel')
                        ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                        ->maxSize(20480)
                        ->required()
                        ->storeFiles(false)
                        ->helperText('Se leerá la hoja REPORTE A SUBIR y se sumarán puntos desde la columna Total Puntos.'),
                ])
                ->modalContentFooter(fn () => $this->excelImportPreview
                    ? view('filament.resources.riders.pages.excel-import-preview', [
                        'preview' => $this->excelImportPreview,
                    ])
                    : null)
                ->modalSubmitActionLabel(fn (): string => $this->excelImportPreview ? 'Crear y continuar' : 'Continuar')
                ->action(function (array $data, Action $action): void {
                    if (auth()->user()?->isAdmin() !== true) {
                        Notification::make()
                            ->title('No tienes permisos para subir Excel')
                            ->danger()
                            ->send();

                        return;
                    }

                    $metadata = [
                        'source' => 'rider_edit_upload',
                        'notes' => 'Carga automatizada de Excel desde la vista del rider.',
                        'branch_scope' => auth()->user()?->branchScope(),
                    ];

                    try {
                        if (! $this->excelImportConfirmed) {
                            $this->excelImportPreview = app(ExcelRiderImportService::class)->previewImport(
                                $data['excel'],
                                $this->record,
                                $metadata,
                            );

                            if ($this->excelImportPreview['has_new_records'] ?? false) {
                                $this->excelImportConfirmed = true;
                                $action->halt();
                            }
                        }

                        $document = app(ExcelRiderImportService::class)->storeAndImportForRider(
                            $data['excel'],
                            $this->record,
                            auth()->id(),
                            $metadata,
                        );
                    } catch (ValidationException $exception) {
                        $this->excelImportPreview = null;
                        $this->excelImportConfirmed = false;

                        Notification::make()
                            ->title('No se pudo procesar el Excel')
                            ->body(collect($exception->errors())->flatten()->implode(' '))
                            ->danger()
                            ->send();

                        return;
                    }

                    $this->excelImportPreview = null;
                    $this->excelImportConfirmed = false;

                    $processedItems = count($document->metadata['processed_items'] ?? []);
                    $skippedItems = count($document->metadata['skipped_items'] ?? []);
                    $points = (int) ($document->metadata['parsed_points'] ?? 0);

                    Notification::make()
                        ->title('Excel procesado')
                        ->body("Se registraron {$processedItems} movimiento(s), se omitieron {$skippedItems} fila(s) y se sumaron {$points} punto(s).")
                        ->success()
                        ->send();

                    $this->loadRecordPointsBalance();
                }),
            ViewAction::make(),
            DeleteAction::make()->label('Eliminar'),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['points_balance'] = (int) $this->record->points_balance;
        [$data['first_names'], $data['last_names']] = $this->splitFullName($data['name'] ?? '');
        unset($data['name']);

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['name'] = $this->buildFullName($data);
        $this->validateFullName($data['name']);
        $this->targetPointsBalance = auth()->user()?->isAdmin() === true
            ? (int) ($data['points_balance'] ?? $this->record->points_balance)
            : null;

        unset($data['first_names'], $data['last_names'], $data['points_balance']);

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        if ($branch = auth()->user()?->branchScope()) {
            $data['branch'] = $branch;
        }

        $record->update([
            ...$data,
            'updated_by' => auth()->id(),
        ]);

        $currentPointsBalance = (int) $record->movements()
            ->sum('points');
        $targetPointsBalance = $this->targetPointsBalance ?? $currentPointsBalance;
        $pointsDelta = $targetPointsBalance - $currentPointsBalance;

        if ($pointsDelta !== 0) {
            RiderMovement::create([
                'rider_id' => $record->getKey(),
                'user_id' => auth()->id(),
                'branch' => $record->branch,
                'movement_type' => 'manual_adjustment',
                'reference' => 'FILAMENT-EDIT',
                'description' => 'Ajuste manual desde la edicion del rider.',
                'points' => $pointsDelta,
                'occurred_at' => now(),
                'metadata' => [
                    'source' => 'filament_edit_rider',
                    'actor_type' => 'user',
                    'target_points_balance' => $targetPointsBalance,
                    'previous_points_balance' => $currentPointsBalance,
                ],
            ]);
        }

        $this->loadRecordPointsBalance();

        return $record;
    }

    protected function loadRecordPointsBalance(): void
    {
        $this->record->refresh();
        $this->record->loadSum('movements as points_balance', 'points');
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

    protected function splitFullName(string $name): array
    {
        $parts = preg_split('/\s+/', trim($name)) ?: [];

        if (count($parts) <= 1) {
            return [$name, ''];
        }

        if (count($parts) === 2) {
            return [$parts[0], $parts[1]];
        }

        $lastNames = array_splice($parts, -2);

        return [
            implode(' ', $parts),
            implode(' ', $lastNames),
        ];
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
