<?php

namespace App\Services;

use App\Models\Rider;
use App\Models\RiderMovement;
use App\Models\UploadedDocument;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use OpenSpout\Reader\Common\Creator\ReaderFactory;

class ExcelRiderImportService
{
    protected const TARGET_SHEET_NAME = 'REPORTE A SUBIR';

    public function storeAndImport(UploadedFile $file, ?int $uploadedBy, array $metadata = []): UploadedDocument
    {
        return $this->storeDocumentAndImport($file, null, $uploadedBy, $metadata);
    }

    public function storeAndImportForRider(UploadedFile $file, Rider $rider, ?int $uploadedBy, array $metadata = []): UploadedDocument
    {
        return $this->storeDocumentAndImport($file, $rider, $uploadedBy, $metadata);
    }

    protected function storeDocumentAndImport(UploadedFile $file, ?Rider $targetRider, ?int $uploadedBy, array $metadata = []): UploadedDocument
    {
        $directory = $targetRider ? 'documents/riders' : 'documents/global';
        $path = $file->store($directory, 'public');

        try {
            return DB::transaction(function () use ($file, $uploadedBy, $metadata, $path, $targetRider): UploadedDocument {
                $document = UploadedDocument::query()->create([
                    'rider_id' => $targetRider?->getKey(),
                    'uploaded_by' => $uploadedBy,
                    'original_name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'disk' => 'public',
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'status' => 'pending_assignment',
                    'uploaded_at' => now(),
                    'metadata' => $metadata,
                ]);

                $parsed = $this->extractImportData(Storage::disk($document->disk)->path($document->path));
                $processedItems = [];
                $skippedItems = $parsed['skipped_items'];
                $processedPoints = 0;
                $documentRiderId = $targetRider?->getKey();

                foreach ($parsed['parsed_riders'] as $parsedRider) {
                    $rider = $this->resolveRider($parsedRider, $targetRider);
                    $points = (int) round($parsedRider['points_total']);

                    if ($points < 1) {
                        $skippedItems[] = [
                            'rider_id' => $parsedRider['rider_id'],
                            'branch' => $parsedRider['branch'],
                            'row_numbers' => $parsedRider['row_numbers'],
                            'points_total' => $parsedRider['points_total'],
                            'reason' => 'El total de puntos del rider no alcanza para generar puntos.',
                        ];

                        continue;
                    }

                    $movement = RiderMovement::query()->create([
                        'rider_id' => $rider->getKey(),
                        'user_id' => $uploadedBy,
                        'uploaded_document_id' => $document->getKey(),
                        'branch' => $parsedRider['branch'],
                        'movement_type' => 'purchase',
                        'reference' => $document->original_name,
                        'description' => 'Importacion automatica desde Excel.',
                        'points' => $points,
                        'occurred_at' => $document->uploaded_at,
                        'metadata' => [
                            'source' => 'excel_auto_import',
                            'actor_type' => 'excel',
                            'branch' => $parsedRider['branch'],
                            'rider_code' => $rider->rider_id,
                            'rider_name' => $parsedRider['rider_name'],
                            'article_codes' => $parsedRider['article_codes'],
                            'article_descriptions' => $parsedRider['article_descriptions'],
                            'points_total' => $parsedRider['points_total'],
                            'row_numbers' => $parsedRider['row_numbers'],
                        ],
                    ]);

                    $processedPoints += $movement->points;
                    $processedItems[] = [
                        'rider_id' => $rider->rider_id,
                        'branch' => $parsedRider['branch'],
                        'movement_id' => $movement->getKey(),
                        'points_total' => $parsedRider['points_total'],
                        'points' => $movement->points,
                        'row_numbers' => $parsedRider['row_numbers'],
                        'article_codes' => $parsedRider['article_codes'],
                        'article_descriptions' => $parsedRider['article_descriptions'],
                    ];

                    if ($documentRiderId === null && count($parsed['parsed_riders']) === 1) {
                        $documentRiderId = $rider->getKey();
                    }
                }

                $status = count($processedItems) > 0
                    ? ($skippedItems === [] ? 'processed' : 'processed_with_errors')
                    : 'processed_without_points';

                $document->update([
                    'rider_id' => $documentRiderId,
                    'status' => $status,
                    'metadata' => array_merge($metadata, [
                        'parsed_riders' => $parsed['parsed_riders'],
                        'processed_items' => $processedItems,
                        'skipped_items' => $skippedItems,
                        'parsed_points' => $processedPoints,
                        'notes' => $this->buildProcessingNotes(count($processedItems), count($skippedItems)),
                    ]),
                ]);

                return $document->fresh(['rider', 'movements']);
            });
        } catch (\Throwable $exception) {
            Storage::disk('public')->delete($path);

            throw $exception;
        }
    }

    public function extractImportData(string $path): array
    {
        $reader = ReaderFactory::createFromFile($path);
        $reader->open($path);

        try {
            $fallbackRows = null;

            foreach ($reader->getSheetIterator() as $sheet) {
                if (mb_strtoupper(trim($sheet->getName())) === self::TARGET_SHEET_NAME) {
                    return $this->parseRows($sheet->getRowIterator());
                }

                $fallbackRows ??= iterator_to_array($sheet->getRowIterator());
            }

            if ($fallbackRows !== null) {
                return $this->parseRows($fallbackRows);
            }
        } finally {
            $reader->close();
        }

        throw ValidationException::withMessages([
            'excel' => ['El archivo Excel no contiene hojas para procesar.'],
        ]);
    }

    protected function parseRows(iterable $rows): array
    {
        $parsedRiders = [];
        $skippedItems = [];
        $rowNumber = 0;

        foreach ($rows as $row) {
            $rowNumber++;
            $values = [];

            foreach ($row->getCells() as $cell) {
                $values[] = $cell->getValue();
            }

            if ($rowNumber === 1) {
                continue;
            }

            if ($this->isRowEmpty($values)) {
                continue;
            }

            if ($this->isHeaderRow($values)) {
                continue;
            }

            $branch = $this->normalizeBranch($values[0] ?? null);
            $riderId = $this->normalizeRiderId($values[1] ?? null);
            $riderName = $this->stringValue($values[2] ?? null);
            $articleCode = $this->stringValue($values[4] ?? null);
            $articleDescription = $this->stringValue($values[5] ?? null);

            if ($riderId === null) {
                continue;
            }

            $parsedKey = $riderId.'|'.($branch ?? 'SIN SUCURSAL');

            if (! isset($parsedRiders[$parsedKey])) {
                $parsedRiders[$parsedKey] = [
                    'rider_id' => $riderId,
                    'rider_name' => $riderName,
                    'branch' => $branch,
                    'points_total' => 0.0,
                    'row_numbers' => [],
                    'article_codes' => [],
                    'article_descriptions' => [],
                ];
            }

            $points = $this->parsePositiveNumber($values[9] ?? null);

            if ($points === null) {
                $skippedItems[] = [
                    'row_number' => $rowNumber,
                    'rider_id' => $riderId,
                    'branch' => $branch,
                    'article_code' => $articleCode,
                    'article_description' => $articleDescription,
                    'points_raw' => $this->stringValue($values[9] ?? null),
                    'reason' => 'La columna J no contiene un valor numerico positivo de puntos.',
                ];

                continue;
            }

            $parsedRiders[$parsedKey]['points_total'] += $points;
            $parsedRiders[$parsedKey]['row_numbers'][] = $rowNumber;

            if ($articleCode !== null) {
                $parsedRiders[$parsedKey]['article_codes'][] = $articleCode;
            }

            if ($articleDescription !== null) {
                $parsedRiders[$parsedKey]['article_descriptions'][] = $articleDescription;
            }
        }

        if ($parsedRiders === []) {
            throw ValidationException::withMessages([
                'excel' => ['No se encontraron filas válidas con código de rider para procesar desde el Excel.'],
            ]);
        }

        return [
            'parsed_riders' => array_values(array_map(function (array $rider): array {
                $rider['points_total'] = round($rider['points_total'], 2);
                $rider['article_codes'] = array_values(array_unique($rider['article_codes']));
                $rider['article_descriptions'] = array_values(array_unique($rider['article_descriptions']));

                return $rider;
            }, $parsedRiders)),
            'skipped_items' => $skippedItems,
        ];
    }

    protected function resolveRider(array $parsedRider, ?Rider $targetRider): Rider
    {
        if ($targetRider) {
            if ($parsedRider['rider_id'] !== $targetRider->rider_id) {
                throw ValidationException::withMessages([
                    'excel' => ['El Excel contiene filas de otro rider y no coincide con el rider actual.'],
                ]);
            }

            if ($parsedRider['branch'] !== null && $targetRider->branch === null) {
                $targetRider->update(['branch' => $parsedRider['branch']]);
            }

            return $targetRider;
        }

        $rider = Rider::query()->firstOrNew(['rider_id' => $parsedRider['rider_id']]);
        $isNewRider = ! $rider->exists;
        $rider->name = $parsedRider['rider_name'] ?: $parsedRider['rider_id'];

        if ($parsedRider['branch'] !== null && $rider->branch === null) {
            $rider->branch = $parsedRider['branch'];
        }

        if ($isNewRider) {
            $rider->creation_source = 'excel';
            $rider->created_by = null;
        }

        $rider->save();

        return $rider;
    }

    protected function isHeaderRow(array $values): bool
    {
        return $this->normalizeHeaderValue($values[0] ?? null) === 'SUCURSAL'
            && $this->normalizeHeaderValue($values[1] ?? null) === 'CODIGO';
    }

    protected function isRowEmpty(array $values): bool
    {
        foreach ($values as $value) {
            if ($this->stringValue($value) !== null) {
                return false;
            }
        }

        return true;
    }

    protected function normalizeHeaderValue(mixed $value): ?string
    {
        $value = $this->stringValue($value);

        if ($value === null) {
            return null;
        }

        $value = strtoupper($value);
        $value = str_replace('.', '', $value);
        $value = strtr($value, [
            'Á' => 'A',
            'É' => 'E',
            'Í' => 'I',
            'Ó' => 'O',
            'Ú' => 'U',
            'Ñ' => 'N',
        ]);
        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;

        return trim($value);
    }

    protected function normalizeBranch(mixed $value): ?string
    {
        $value = $this->stringValue($value);

        if ($value === null) {
            return null;
        }

        $value = strtoupper($value);
        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;

        return trim($value);
    }

    protected function normalizeRiderId(mixed $value): ?string
    {
        $value = $this->stringValue($value);

        return $value !== null ? strtoupper($value) : null;
    }

    protected function parsePositiveNumber(mixed $value): ?float
    {
        if (is_int($value) || is_float($value)) {
            return $value > 0 ? (float) $value : null;
        }

        $stringValue = $this->stringValue($value);

        if ($stringValue === null) {
            return null;
        }

        $normalized = str_replace([' ', ','], ['', '.'], $stringValue);

        if (! is_numeric($normalized)) {
            return null;
        }

        $number = (float) $normalized;

        return $number > 0 ? $number : null;
    }

    protected function stringValue(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }

    protected function buildProcessingNotes(int $processedCount, int $skippedCount): string
    {
        if ($processedCount > 0 && $skippedCount === 0) {
            return 'Documento Excel procesado automaticamente por rider y sucursal.';
        }

        if ($processedCount > 0) {
            return 'Documento Excel procesado parcialmente. Revisa las filas omitidas en el detalle.';
        }

        return 'No se encontraron filas validas para sumar puntos desde el Excel.';
    }
}
