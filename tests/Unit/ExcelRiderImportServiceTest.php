<?php

namespace Tests\Unit;

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
        $service = new ExcelRiderImportService;
        $path = $this->createExcelFile([
            ['Sucursal', 'Codigo', 'Nombre del Rider', 'N Documento', 'Articulo', 'Descripcion', 'Cantidad', 'Litros', 'PtsSku', 'Total Puntos'],
            ['Central', 'sc00065', 'Sandra Parada', 'NV-001', 'RPP001', 'Producto 1', 1, 12, 100, '300.5'],
            ['Central', 'sc00065', 'Sandra Parada', 'NV-002', 'RPP002', 'Producto 2', 1, 12, 100, 0],
            ['Norte', 'sc00065', 'Sandra Parada', 'NV-003', 'RPP003', 'Producto 3', 1, 12, 100, 40],
            ['Sur', 'sc00081', 'Jorge Mamani', 'NV-004', 'RPP004', 'Producto 4', 1, 12, 100, 'texto'],
            ['Sur', 'sc00081', 'Jorge Mamani', 'NV-005', 'RPP005', 'Producto 5', 1, 12, 100, 400],
        ]);

        $parsed = $service->extractImportData($path);

        $this->assertCount(3, $parsed['parsed_riders']);
        $this->assertSame('SC00065', $parsed['parsed_riders'][0]['rider_id']);
        $this->assertSame('CENTRAL', $parsed['parsed_riders'][0]['branch']);
        $this->assertSame(300.5, $parsed['parsed_riders'][0]['points_total']);
        $this->assertSame('Sandra Parada', $parsed['parsed_riders'][0]['rider_name']);
        $this->assertSame(['RPP001'], $parsed['parsed_riders'][0]['article_codes']);
        $this->assertSame('SC00065', $parsed['parsed_riders'][1]['rider_id']);
        $this->assertSame('NORTE', $parsed['parsed_riders'][1]['branch']);
        $this->assertSame(40.0, $parsed['parsed_riders'][1]['points_total']);
        $this->assertSame('SC00081', $parsed['parsed_riders'][2]['rider_id']);
        $this->assertSame('SUR', $parsed['parsed_riders'][2]['branch']);
        $this->assertSame(400.0, $parsed['parsed_riders'][2]['points_total']);
        $this->assertSame('Jorge Mamani', $parsed['parsed_riders'][2]['rider_name']);
        $this->assertCount(2, $parsed['skipped_items']);
    }

    public function test_it_creates_riders_and_one_movement_per_rider_from_excel(): void
    {
        Storage::fake('public');

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
        $this->assertSame(900, $document->metadata['parsed_points']);
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
            'points' => 500,
        ]);

        $this->assertDatabaseHas('rider_movements', [
            'rider_id' => $newRider->getKey(),
            'uploaded_document_id' => $document->getKey(),
            'branch' => 'NORTE',
            'movement_type' => 'purchase',
            'points' => 400,
        ]);
    }

    public function test_it_rejects_excel_with_rows_for_another_rider_when_importing_from_rider_view(): void
    {
        Storage::fake('public');

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
}
