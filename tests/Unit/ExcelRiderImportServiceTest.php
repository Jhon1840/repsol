<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\Rider;
use App\Services\ExcelRiderImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;
use Tests\TestCase;

class ExcelRiderImportServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_groups_rows_by_rider_and_branch_and_skips_invalid_points(): void
    {
        $this->createProduct('RPP001', 25);
        $this->createProduct('RPP003', 10);
        $this->createProduct('RPP004', 10);
        $this->createProduct('RPP005', 20);

        $service = new ExcelRiderImportService;
        $path = $this->createExcelFile([
            ['Sucursal', 'Codigo', 'Nombre del Rider', 'N Documento', 'Articulo', 'Descripcion', 'Cantidad', 'Litros', 'PtsSku', 'Total Puntos'],
            ['Central', 'sc00065', 'Sandra Parada', 'NV-001', 'RPP001', 'Producto 1', 1, 12, 100, '999'],
            ['Central', 'sc00065', 'Sandra Parada', 'NV-002', 'RPP002', 'Producto 2', 1, 12, 100, 0],
            ['Norte', 'sc00065', 'Sandra Parada', 'NV-003', 'RPP003', 'Producto 3', 1, 12, 100, 40],
            ['Sur', 'sc00081', 'Jorge Mamani', 'NV-004', 'RPP004', 'Producto 4', 1, 'texto', 100, 200],
            ['Sur', 'sc00081', 'Jorge Mamani', 'NV-005', 'RPP005', 'Producto 5', 1, 12, 100, 400],
        ]);

        $parsed = $service->extractImportData($path);

        $this->assertCount(3, $parsed['parsed_riders']);
        $this->assertSame('SC00065', $parsed['parsed_riders'][0]['rider_id']);
        $this->assertSame('CENTRAL', $parsed['parsed_riders'][0]['branch']);
        $this->assertSame(300.0, $parsed['parsed_riders'][0]['points_total']);
        $this->assertSame('Sandra Parada', $parsed['parsed_riders'][0]['rider_name']);
        $this->assertSame(['RPP001'], $parsed['parsed_riders'][0]['article_codes']);
        $this->assertSame('SC00065', $parsed['parsed_riders'][1]['rider_id']);
        $this->assertSame('NORTE', $parsed['parsed_riders'][1]['branch']);
        $this->assertSame(120.0, $parsed['parsed_riders'][1]['points_total']);
        $this->assertSame('SC00081', $parsed['parsed_riders'][2]['rider_id']);
        $this->assertSame('SUR', $parsed['parsed_riders'][2]['branch']);
        $this->assertSame(240.0, $parsed['parsed_riders'][2]['points_total']);
        $this->assertSame('Jorge Mamani', $parsed['parsed_riders'][2]['rider_name']);
        $this->assertCount(2, $parsed['skipped_items']);
    }

    public function test_it_creates_riders_and_one_movement_per_rider_from_excel(): void
    {
        Storage::fake('local');
        $this->createProduct('RPP001', 10);
        $this->createProduct('RPP002', 20);
        $this->createProduct('RPP003', 30);

        Rider::query()->create([
            'rider_id' => 'SC00065',
            'name' => 'Sandra Parada',
            'branch' => 'CENTRAL',
        ]);

        $service = new ExcelRiderImportService;
        $uploadedFile = $this->uploadedExcel([
            ['Sucursal', 'Codigo', 'Nombre del Rider', 'N Documento', 'Articulo', 'Descripcion', 'Cantidad', 'Litros', 'PtsSku', 'Total Puntos'],
            ['Central', 'SC00065', 'Sandra Parada', 'NV-001', 'RPP001', 'Producto 1', 1, 12, 100, 200],
            ['Central', 'SC00065', 'Sandra Parada', 'NV-002', 'RPP002', 'Producto 2', 1, 12, 100, 300],
            ['Norte', 'SC00099', 'Nuevo Rider', 'NV-003', 'RPP003', 'Producto 3', 1, 12, 100, 400],
        ]);

        $document = $service->storeAndImport($uploadedFile, null, ['source' => 'test']);

        $this->assertSame('processed', $document->status);
        $this->assertSame(720, $document->metadata['parsed_points']);
        $this->assertCount(2, $document->metadata['processed_items']);

        $this->assertDatabaseHas('riders', [
            'rider_id' => 'SC00099',
            'name' => 'Nuevo Rider',
            'branch' => 'NORTE',
        ]);

        $existingRider = Rider::query()->where('rider_id', 'SC00065')->firstOrFail();
        $newRider = Rider::query()->where('rider_id', 'SC00099')->firstOrFail();

        $this->assertDatabaseHas('rider_movements', [
            'rider_id' => $existingRider->getKey(),
            'uploaded_document_id' => $document->getKey(),
            'branch' => 'CENTRAL',
            'movement_type' => 'purchase',
            'points' => 360,
        ]);

        $this->assertDatabaseHas('rider_movements', [
            'rider_id' => $newRider->getKey(),
            'uploaded_document_id' => $document->getKey(),
            'branch' => 'NORTE',
            'movement_type' => 'purchase',
            'points' => 360,
        ]);
    }

    public function test_it_calculates_points_from_liters_for_excel_without_branch_column(): void
    {
        $this->createProduct('RPP2000MHC', 500);
        $this->createProduct('RPP2064MGB', 300);

        $service = new ExcelRiderImportService;
        $path = $this->createExcelFile([
            ['Codigo', 'Nombre del Rider', 'N Documento', 'Articulo', 'Descripcion', 'Cantidad', 'Litros', 'PtsSku', 'Total Puntos'],
            ['PY12702330', 'Luis Llancu', 'Nro Doc: 1', 'RPP2000MHC', 'RACING 4T 10W-40 12X1L', 1, 12, 500, 500],
            ['PY12702330', 'Luis Llancu', 'Nro Doc: 2', 'RPP2064MGB', 'SMARTER SYNTHETIC 4T 10W-40 5X4L', 1, 20, 400, 400],
        ]);

        $parsed = $service->extractImportData($path);

        $this->assertCount(1, $parsed['parsed_riders']);
        $this->assertSame('PY12702330', $parsed['parsed_riders'][0]['rider_id']);
        $this->assertNull($parsed['parsed_riders'][0]['branch']);
        $this->assertSame(12000.0, $parsed['parsed_riders'][0]['points_total']);
        $this->assertSame([], $parsed['skipped_items']);
    }

    public function test_it_skips_rows_outside_branch_scope(): void
    {
        $this->createProduct('RPP001', 100);
        $this->createProduct('RPP002', 200);

        $service = new ExcelRiderImportService;
        $path = $this->createExcelFile([
            ['Sucursal', 'Codigo', 'Nombre del Rider', 'N Documento', 'Articulo', 'Descripcion', 'Cantidad', 'Litros', 'PtsSku', 'Total Puntos'],
            ['Santa Cruz', 'SCZ001', 'Rider SCZ', 'NV-001', 'RPP001', 'Producto 1', 1, 12, 100, 100],
            ['La Paz', 'LPZ001', 'Rider LPZ', 'NV-002', 'RPP002', 'Producto 2', 1, 12, 100, 100],
        ]);

        $parsed = $service->extractImportData($path, 'SANTA CRUZ');

        $this->assertCount(1, $parsed['parsed_riders']);
        $this->assertSame('SCZ001', $parsed['parsed_riders'][0]['rider_id']);
        $this->assertSame(1200.0, $parsed['parsed_riders'][0]['points_total']);
        $this->assertCount(1, $parsed['skipped_items']);
        $this->assertSame('La fila pertenece a otra sucursal.', $parsed['skipped_items'][0]['reason']);
    }

    public function test_it_rejects_excel_with_rows_for_another_rider_when_importing_from_rider_view(): void
    {
        Storage::fake('local');
        $this->createProduct('RPP001', 10);
        $this->createProduct('RPP002', 10);

        $rider = Rider::query()->create([
            'rider_id' => 'SC00065',
            'name' => 'Sandra Parada',
            'branch' => 'CENTRAL',
        ]);

        $service = new ExcelRiderImportService;
        $uploadedFile = $this->uploadedExcel([
            ['Sucursal', 'Codigo', 'Nombre del Rider', 'N Documento', 'Articulo', 'Descripcion', 'Cantidad', 'Litros', 'PtsSku', 'Total Puntos'],
            ['Central', 'SC00065', 'Sandra Parada', 'NV-001', 'RPP001', 'Producto 1', 1, 12, 100, 200],
            ['Central', 'SC00081', 'Jorge Mamani', 'NV-002', 'RPP002', 'Producto 2', 1, 12, 100, 300],
        ]);

        $this->expectException(ValidationException::class);

        try {
            $service->storeAndImportForRider($uploadedFile, $rider, null, ['source' => 'test']);
        } finally {
            $this->assertDatabaseCount('rider_movements', 0);
        }
    }

    protected function uploadedExcel(array $rows): UploadedFile
    {
        $path = $this->createExcelFile($rows);

        return new UploadedFile(
            $path,
            'import.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );
    }

    protected function createExcelFile(array $rows): string
    {
        $path = tempnam(sys_get_temp_dir(), 'excel-import-');

        if ($path === false) {
            throw new \RuntimeException('No se pudo crear el archivo temporal para la prueba.');
        }

        $xlsxPath = $path.'.xlsx';
        $writer = new Writer;
        $writer->openToFile($xlsxPath);

        foreach ($rows as $row) {
            $writer->addRow(Row::fromValues($row));
        }

        $writer->close();
        @unlink($path);

        return $xlsxPath;
    }

    protected function createProduct(string $code, float $pointsPerLiter): Product
    {
        return Product::query()->create([
            'name' => "Producto {$code}",
            'code' => $code,
            'liters' => 12,
            'points_per_box' => 12 * $pointsPerLiter,
            'points_per_liter' => $pointsPerLiter,
        ]);
    }
}
