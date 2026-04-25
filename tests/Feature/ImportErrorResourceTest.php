<?php

namespace Tests\Feature;

use App\Filament\Resources\ImportErrors\ImportErrorResource;
use App\Models\Rider;
use App\Models\UploadedDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImportErrorResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_error_resource_query_only_returns_documents_with_errors(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin',
            'email' => 'admin-errors@example.com',
            'password' => 'password',
            'role' => User::ROLE_ADMIN,
        ]);

        $rider = Rider::query()->create([
            'rider_id' => 'ERR001',
            'name' => 'Rider Error',
            'branch' => 'SANTA CRUZ',
        ]);

        $failed = UploadedDocument::query()->create([
            'rider_id' => $rider->getKey(),
            'original_name' => 'fallido.xlsx',
            'path' => 'documents/global/fallido.xlsx',
            'disk' => 'local',
            'status' => 'failed',
            'uploaded_at' => now(),
            'metadata' => [
                'fatal_errors' => [
                    ['reason' => 'Archivo invalido'],
                ],
            ],
        ]);

        UploadedDocument::query()->create([
            'rider_id' => $rider->getKey(),
            'original_name' => 'ok.xlsx',
            'path' => 'documents/global/ok.xlsx',
            'disk' => 'local',
            'status' => 'processed',
            'uploaded_at' => now(),
            'metadata' => [],
        ]);

        $this->actingAs($admin);

        $records = ImportErrorResource::getEloquentQuery()->get();

        $this->assertCount(1, $records);
        $this->assertTrue($records->first()->is($failed));
    }

    public function test_branch_scoped_user_sees_failed_document_from_metadata_branch_scope(): void
    {
        $user = User::query()->create([
            'name' => 'Marketing SCZ',
            'email' => 'marketing-errors-scz@example.com',
            'password' => 'password',
            'role' => User::ROLE_MARKETING,
            'branch' => 'SANTA CRUZ',
        ]);

        UploadedDocument::query()->create([
            'original_name' => 'scz.xlsx',
            'path' => 'documents/global/scz.xlsx',
            'disk' => 'local',
            'status' => 'failed',
            'uploaded_at' => now(),
            'metadata' => [
                'branch_scope' => 'SANTA CRUZ',
                'fatal_errors' => [
                    ['reason' => 'Error SCZ'],
                ],
            ],
        ]);

        UploadedDocument::query()->create([
            'original_name' => 'lpz.xlsx',
            'path' => 'documents/global/lpz.xlsx',
            'disk' => 'local',
            'status' => 'failed',
            'uploaded_at' => now(),
            'metadata' => [
                'branch_scope' => 'LA PAZ',
                'fatal_errors' => [
                    ['reason' => 'Error LPZ'],
                ],
            ],
        ]);

        $this->actingAs($user);

        $records = ImportErrorResource::getEloquentQuery()->pluck('original_name')->all();

        $this->assertSame(['scz.xlsx'], $records);
    }
}
