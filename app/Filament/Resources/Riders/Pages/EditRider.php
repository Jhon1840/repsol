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
                ->schema([
                    FileUpload::make('excel')
                        ->label('Archivo Excel')
                        ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                        ->maxSize(20480)
                        ->required()
                        ->storeFiles(false)
                        ->helperText('Se leerá la hoja REPORTE A SUBIR y se calculará puntos como Litros comprados x Puntos por litro del producto.'),
                ])
                ->action(function (array $data): void {
                    if (auth()->user()?->isAdmin() !== true) {
                        Notification::make()
                            ->title('No tienes permisos para subir Excel')
                            ->danger()
                            ->send();

                        return;
                    }

                    try {
                        $document = app(ExcelRiderImportService::class)->storeAndImportForRider(
                            $data['excel'],
                            $this->record,
                            auth()->id(),
                            [
                                'source' => 'rider_edit_upload',
                                'notes' => 'Carga automatizada de Excel desde la vista del rider.',
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

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->targetPointsBalance = (int) ($data['points_balance'] ?? $this->record->points_balance);

        unset($data['points_balance']);

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
}
