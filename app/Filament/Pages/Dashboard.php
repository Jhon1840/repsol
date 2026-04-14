<?php

namespace App\Filament\Pages;

use App\Models\Rider;
use App\Models\RiderMovement;
use App\Models\UploadedDocument;
use App\Services\ExcelRiderImportService;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;
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
        $this->setDefaultPointsChartFilters();
    }

    public function filtersForm(Schema $schema): Schema
    {
        return $schema
            ->columns([
                'sm' => 2,
            ])
            ->schema([
                DatePicker::make('pointsChartStartDate')
                    ->label('Desde')
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->format('Y-m-d')
                    ->closeOnDateSelection()
                    ->live(),
                DatePicker::make('pointsChartEndDate')
                    ->label('Hasta')
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->format('Y-m-d')
                    ->closeOnDateSelection()
                    ->live(),
            ]);
    }

    public function updatedFilters(): void
    {
        $this->syncPointsChartPropertiesFromFilters();
    }

    public function persistsFiltersInSession(): bool
    {
        return false;
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

        try {
            $document = app(ExcelRiderImportService::class)->storeAndImport(
                $this->pendingExcel,
                auth()->id(),
                [
                    'source' => 'dashboard_upload',
                    'notes' => 'Carga automatizada de Excel desde dashboard.',
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

    protected function getViewData(): array
    {
        $this->setDefaultPointsChartFilters();

        $totalRiders = Rider::query()->visibleTo(auth()->user())->count();
        $totalPoints = (int) $this->movementQuery()->sum('points');

        return [
            'totalRiders' => $totalRiders,
            'averagePoints' => $totalRiders > 0 ? $totalPoints / $totalRiders : 0,
            'riders' => Rider::query()
                ->visibleTo(auth()->user())
                ->withPointsBalance(auth()->user())
                ->orderByDesc('points_balance')
                ->orderBy('name')
                ->limit(5)
                ->get(),
            'recentDocuments' => $this->documentQuery()
                ->latest('uploaded_at')
                ->limit(5)
                ->get(),
            'pointsChart' => $this->getPointsChartData(),
            'canUploadExcel' => $this->canUploadExcel(),
        ];
    }

    protected function getPointsChartData(): array
    {
        $startDateValue = $this->filters['pointsChartStartDate'] ?? $this->pointsChartStartDate;
        $endDateValue = $this->filters['pointsChartEndDate'] ?? $this->pointsChartEndDate;

        $startDate = $startDateValue
            ? Carbon::parse($startDateValue)->startOfDay()
            : now()->startOfMonth();

        $endDate = $endDateValue
            ? Carbon::parse($endDateValue)->endOfDay()
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

        $this->movementQuery()
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

    private function setDefaultPointsChartFilters(): void
    {
        $this->filters ??= [];

        $this->filters['pointsChartStartDate'] ??= now()->startOfMonth()->toDateString();
        $this->filters['pointsChartEndDate'] ??= now()->toDateString();

        $this->syncPointsChartPropertiesFromFilters();
    }

    private function syncPointsChartPropertiesFromFilters(): void
    {
        $this->pointsChartStartDate = $this->filters['pointsChartStartDate'] ?? null;
        $this->pointsChartEndDate = $this->filters['pointsChartEndDate'] ?? null;
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
