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

    public function test_it_groups_rows_by_rider_and_skips_invalid_liters(): void
    {
        $service = new ExcelRiderImportService();
        $path = $this->createExcelFile([
            ['Sucursal', 'Codigo del Rider', 'Nombre del Rider', 'N de Nota de Venta', 'E', 'F', 'G', 'Litros'],
            ['Central', 'sc00065', 'Sandra Parada', 'NV-001', '', '', '', '3.5'],
            ['Central', 'sc00065', 'Sandra Parada', 'NV-002', '', '', '', 0],
            ['Norte', 'sc00065', 'Sandra Parada', 'NV-003', '', '', '', 2],
            ['Sur', 'sc00081', 'Jorge Mamani', 'NV-004', '', '', '', 'texto'],
            ['Sur', 'sc00081', 'Jorge Mamani', 'NV-005', '', '', '', 4],
        ]);

        $parsed = $service->extractImportData($path);

        $this->assertCount(2, $parsed['parsed_riders']);
        $this->assertSame('SC00065', $parsed['parsed_riders'][0]['rider_id']);
        $this->assertSame(5.5, $parsed['parsed_riders'][0]['liters_total']);
        $this->assertSame('Sandra Parada', $parsed['parsed_riders'][0]['rider_name']);
        $this->assertSame(['NV-001', 'NV-003'], $parsed['parsed_riders'][0]['sale_note_numbers']);
        $this->assertSame('SC00081', $parsed['parsed_riders'][1]['rider_id']);
        $this->assertSame(4.0, $parsed['parsed_riders'][1]['liters_total']);
        $this->assertSame('Jorge Mamani', $parsed['parsed_riders'][1]['rider_name']);
        $this->assertCount(2, $parsed['skipped_items']);
    }

    public function test_it_creates_riders_and_one_movement_per_rider_from_excel(): void
    {
        Storage::fake('public');

        Rider::query()->create([
            'rider_id' => 'SC00065',
            'name' => 'Sandra Parada',
        ]);

        $service = new ExcelRiderImportService();
        $uploadedFile = $this->uploadedExcel([
            ['Sucursal', 'Codigo del Rider', 'Nombre del Rider', 'N de Nota de Venta', 'E', 'F', 'G', 'Litros'],
            ['Central', 'SC00065', 'Sandra Parada', 'NV-001', '', '', '', 2],
            ['Central', 'SC00065', 'Sandra Parada', 'NV-002', '', '', '', 3],
            ['Norte', 'SC00099', 'Nuevo Rider', 'NV-003', '', '', '', 4],
        ]);

        $document = $service->storeAndImport($uploadedFile, null, ['source' => 'test']);

        $this->assertSame('processed', $document->status);
        $this->assertSame(9, $document->metadata['parsed_points']);
        $this->assertCount(2, $document->metadata['processed_items']);

        $this->assertDatabaseHas('riders', [
            'rider_id' => 'SC00099',
            'name' => 'Nuevo Rider',
        ]);

        $existingRider = Rider::query()->where('rider_id', 'SC00065')->firstOrFail();
        $newRider = Rider::query()->where('rider_id', 'SC00099')->firstOrFail();

        $this->assertDatabaseHas('rider_movements', [
            'rider_id' => $existingRider->getKey(),
            'uploaded_document_id' => $document->getKey(),
            'movement_type' => 'purchase',
            'points' => 5,
        ]);

        $this->assertDatabaseHas('rider_movements', [
            'rider_id' => $newRider->getKey(),
            'uploaded_document_id' => $document->getKey(),
            'movement_type' => 'purchase',
            'points' => 4,
        ]);
    }

    public function test_it_rejects_excel_with_rows_for_another_rider_when_importing_from_rider_view(): void
    {
        Storage::fake('public');

        $rider = Rider::query()->create([
            'rider_id' => 'SC00065',
            'name' => 'Sandra Parada',
        ]);

        $service = new ExcelRiderImportService();
        $uploadedFile = $this->uploadedExcel([
            ['Sucursal', 'Codigo del Rider', 'Nombre del Rider', 'N de Nota de Venta', 'E', 'F', 'G', 'Litros'],
            ['Central', 'SC00065', 'Sandra Parada', 'NV-001', '', '', '', 2],
            ['Central', 'SC00081', 'Jorge Mamani', 'NV-002', '', '', '', 3],
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
        $writer = new Writer();
        $writer->openToFile($xlsxPath);

        foreach ($rows as $row) {
            $writer->addRow(Row::fromValues($row));
        }

        $writer->close();
        @unlink($path);

        return $xlsxPath;
    }
}
