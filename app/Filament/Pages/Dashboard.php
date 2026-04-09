<?php

namespace App\Filament\Pages;

use App\Models\Rider;
use App\Models\RiderMovement;
use App\Models\UploadedDocument;
use App\Services\ExcelRiderImportService;
use Filament\Notifications\Notification;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
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

    public ?string $pointsChartStartDate = null;

    public ?string $pointsChartEndDate = null;

    public function mount(): void
    {
        $this->pointsChartStartDate ??= now()->subMonths(5)->startOfMonth()->toDateString();
        $this->pointsChartEndDate ??= now()->toDateString();
    }

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
        $totalRiders = Rider::query()->count();
        $totalPoints = (int) RiderMovement::query()->sum('points');

        return [
            'totalRiders' => $totalRiders,
            'averagePoints' => $totalRiders > 0 ? $totalPoints / $totalRiders : 0,
            'riders' => Rider::query()
                ->withPointsBalance()
                ->orderByDesc('points_balance')
                ->orderBy('name')
                ->limit(5)
                ->get(),
            'recentDocuments' => UploadedDocument::query()
                ->latest('uploaded_at')
                ->limit(5)
                ->get(),
            'pointsChart' => $this->getPointsChartData(),
        ];
    }

    protected function getPointsChartData(): array
    {
        $startDate = $this->pointsChartStartDate
            ? Carbon::parse($this->pointsChartStartDate)->startOfDay()
            : now()->subMonths(5)->startOfMonth();

        $endDate = $this->pointsChartEndDate
            ? Carbon::parse($this->pointsChartEndDate)->endOfDay()
            : now()->endOfDay();

        if ($startDate->greaterThan($endDate)) {
            [$startDate, $endDate] = [$endDate->copy()->startOfDay(), $startDate->copy()->endOfDay()];
        }

        $groupByMonth = $startDate->diffInDays($endDate) > 92;
        $keyFormat = $groupByMonth ? 'Y-m' : 'Y-m-d';
        $labelFormat = $groupByMonth ? 'm/Y' : 'd/m';
        $bucketStartDate = $groupByMonth ? $startDate->copy()->startOfMonth() : $startDate->copy();
        $buckets = [];

        for ($date = $bucketStartDate; $date->lessThanOrEqualTo($endDate); $date = $groupByMonth ? $date->addMonthNoOverflow() : $date->addDay()) {
            $buckets[$date->format($keyFormat)] = [
                'date' => $date->toDateString(),
                'label' => $date->format($labelFormat),
                'loaded' => 0,
                'spent' => 0,
            ];
        }

        RiderMovement::query()
            ->whereBetween('occurred_at', [$startDate, $endDate])
            ->get(['points', 'occurred_at'])
            ->each(function (RiderMovement $movement) use (&$buckets, $keyFormat): void {
                $key = $movement->occurred_at?->format($keyFormat);

                if (! $key || ! isset($buckets[$key])) {
                    return;
                }

                if ($movement->points >= 0) {
                    $buckets[$key]['loaded'] += $movement->points;

                    return;
                }

                $buckets[$key]['spent'] += abs($movement->points);
            });

        $rows = array_values($buckets);
        $maxValue = max(1, ...array_map(fn (array $row): int => max($row['loaded'], $row['spent']), $rows));

        return [
            'rows' => $rows,
            'maxValue' => $maxValue,
            'loadedTotal' => array_sum(array_column($rows, 'loaded')),
            'spentTotal' => array_sum(array_column($rows, 'spent')),
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
            return "El archivo {$name} procesó {$processedRiders} movimiento(s) por rider/sucursal, omitió {$skippedRows} fila(s) y sumó {$points} punto(s).";
        }

        return "El archivo {$name} procesó {$processedRiders} movimiento(s) por rider/sucursal y sumó {$points} punto(s).";
    }
}
